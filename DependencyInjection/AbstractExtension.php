<?php

namespace Ekyna\Bundle\ResourceBundle\DependencyInjection;

use Ekyna\Bundle\CoreBundle\DependencyInjection\Extension;
use Ekyna\Bundle\ResourceBundle\Configuration\ConfigurationBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * Class AbstractExtension
 * @package Ekyna\Bundle\ResourceBundle\DependencyInjection
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractExtension extends Extension
{
    /**
     * @var XmlFileLoader
     */
    protected $loader;

    /**
     * @var string[]
     */
    protected $configFiles = [
        'services',
    ];


    /**
     * @inheritDoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        throw new \Exception('AbstractExtension:load() has to be overridden.');
    }

    /**
     * Configures the pool builder and returns the bundle processed configuration.
     *
     * @param array                  $configs
     * @param string                 $prefix
     * @param ConfigurationInterface $configuration
     * @param ContainerBuilder       $container
     *
     * @return array
     */
    protected function configure(
        array $configs,
        $prefix,
        ConfigurationInterface $configuration,
        ContainerBuilder $container
    ) {
        $config = $this->processConfiguration($configuration, $configs);

        $this->loader = new XmlFileLoader($container, new FileLocator($this->getConfigurationDirectory()));
        $this->loadConfigurationFile($this->configFiles);

        if (array_key_exists('pools', $config)) { // TODO rename 'pools' to 'resources'
            $builder = new ConfigurationBuilder($container);
            foreach ($config['pools'] as $resourceName => $params) {
                $builder
                    ->configure($prefix, $resourceName, $params)
                    ->build();
            }
        }

        return $config;
    }

    /**
     * Loads bundle configuration files.
     *
     * @param array $config
     */
    protected function loadConfigurationFile(array $config)
    {
        foreach ($config as $filename) {
            if (file_exists($file = sprintf('%s/%s.xml', $this->getConfigurationDirectory(), $filename))) {
                $this->loader->load($file);
            }
        }
    }
}
