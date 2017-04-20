<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\Service\Routing\Loader;

use Symfony\Component\Routing\Loader\YamlFileLoader as BaseLoader;
use Symfony\Component\Routing\RouteCollection;

use function array_fill_keys;
use function array_key_exists;
use function array_keys;
use function is_array;

/**
 * Class YamlFileLoader
 * @package Ekyna\Bundle\ResourceBundle\Service\Routing\Loader
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class YamlFileLoader extends BaseLoader
{
    use HostsTrait;

    private array $locales;
    private string $defaultLocale;

    public function setLocales(array $locales, string $defaultLocale): void
    {
        $this->locales = $locales;
        $this->defaultLocale = $defaultLocale;
    }

    protected function parseRoute(RouteCollection $collection, string $name, array $config, string $path): void
    {
        $this->addHosts($config);

        if (isset($config['options']) && array_key_exists('ekyna_i18n', $config['options'])) {
            if (!is_array($config['path'])) {
                $config['path'] = array_fill_keys($this->locales, $config['path']);
            }
        }

        /** TODO if hosts parameter is empty or not configured:
         *    prefix:   /{_locale}
         *    defaults: { _locale: "%kernel.default_locale%" }
         *    requirements : { _locale: "%kernel.locales%" }
         * @see \Ekyna\Bundle\ResourceBundle\DependencyInjection\Compiler\RouterHostsPass::process
         */

        parent::parseRoute($collection, $name, $config, $path);
    }

    protected function parseImport(RouteCollection $collection, array $config, string $path, string $file): void
    {
        $this->addHosts($config);

        parent::parseImport($collection, $config, $path, $file);
    }
}
