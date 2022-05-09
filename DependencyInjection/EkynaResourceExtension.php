<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\DependencyInjection;

use DoctrineExtensions\Query\Mysql;
use Ekyna\Component\Resource\Doctrine\DBAL\Type;
use Ekyna\Component\Resource\Resource;
use Misd\PhoneNumberBundle\Doctrine\DBAL\Types\PhoneNumberType;
use ReflectionClass;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

use function array_diff;
use function array_keys;
use function array_unique;
use function array_unshift;
use function array_values;
use function dirname;
use function in_array;

/**
 * Class EkynaResourceExtension
 * @package Ekyna\Bundle\ResourceBundle\DependencyInjection
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class EkynaResourceExtension extends Extension implements PrependExtensionInterface
{
    use PrependBundleConfigTrait;

    public function prepend(ContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new Configuration(), $configs);

        $this->configureI18n($config['i18n'], $container);
        $this->configureReport($config['report'], $container);
        $this->configureDoctrine($container);

        $this->prependBundleConfigFiles($container);
    }

    private function configureDoctrine(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('doctrine', [
            'dbal' => [
                'types' => [
                    PhoneNumberType::NAME        => [
                        'class' => PhoneNumberType::class,
                    ],
                    Type\EncryptedJsonType::NAME => [
                        'class' => Type\EncryptedJsonType::class,
                    ],
                    Type\PhpDecimalType::NAME    => [
                        'class' => Type\PhpDecimalType::class,
                    ],
                ],
            ],
            'orm'  => [
                'entity_managers' => [
                    'default' => [
                        'dql' => [
                            'datetime_functions' => [
                                'month'          => Mysql\Month::class,
                                'year'           => Mysql\Year::class,
                                'date'           => Mysql\Date::class,
                                'day'            => Mysql\Day::class,
                                'dayofweek'      => Mysql\DayOfWeek::class,
                                'dayofyear'      => Mysql\DayOfYear::class,
                                'unix_timestamp' => Mysql\UnixTimestamp::class,
                            ],
                            'numeric_functions'  => [
                                'rand' => Mysql\Rand::class,
                            ],
                            'string_functions'   => [
                                'ifnull' => Mysql\IfNull::class,
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        // Component services
        $loader = new PhpFileLoader($container, new FileLocator($this->getComponentConfigDirectory()));
        $loader->load('config.php');
        $loader->load('doctrine.php');
        $loader->load('search.php');
        $loader->load('services.php');

        // Bundle services
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services/acl.php');
        $loader->load('services/form.php');
        $loader->load('services.php');

        if (in_array($container->getParameter('kernel.environment'), ['dev', 'test'], true)) {
            $loader->load('services/dev.php');
        }

        $config = $this->processConfiguration(new Configuration(), $configs);

        $this->configurePdf($config['pdf'], $container);
    }

    /**
     * Configures localization (router, translator).
     */
    private function configureI18n(array $config, ContainerBuilder $container): void
    {
        if (!$config['enabled']) {
            return;
        }

        $default = $config['locale'];
        $locales = array_unique($config['locales']);
        $hosts = $config['hosts'];

        if (!in_array($default, $locales, true)) {
            array_unshift($locales, $default);
        }

        if (!empty($hosts) && !empty(array_diff($locales, array_keys($hosts)))) {
            $exception = new InvalidConfigurationException('Inconsistency between enabled locales and configured hosts.');
            $exception->setPath('ekyna_resource.hosts');
            throw $exception;
        }

        $container->setParameter('ekyna_resource.locales', $locales);
        $container->setParameter('ekyna_resource.hosts', $hosts);

        $framework = [
            'trusted_hosts'   => array_values($hosts),
            'default_locale'  => $default,
            'enabled_locales' => $locales,
            'translator'      => [
                'fallbacks' => [$default],
            ],
        ];

        /*if (isset($hosts[$default])) {
            $framework['router']['default_uri'] = $hosts[$default];
        }*/

        $container->prependExtensionConfig('framework', $framework);
    }

    private function configurePdf(array $config, ContainerBuilder $container): void
    {
        $container
            ->getDefinition('ekyna_resource.generator.pdf')
            ->setArguments([$config['entry_point'], $config['token']]);
    }

    private function configureReport(array $config, ContainerBuilder $container): void
    {
        $container->setParameter('ekyna_resource.report_email', $config['email']);
    }

    private function getComponentConfigDirectory(): string
    {
        $rc = new ReflectionClass(Resource::class);
        $directory = dirname($rc->getFileName());

        return $directory . '/Bridge/Symfony/Resources/config';
    }
}
