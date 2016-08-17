<?php

namespace Ekyna\Bundle\ResourceBundle\DependencyInjection;

use Ekyna\Bundle\CoreBundle\DependencyInjection\Extension;
use Ekyna\Bundle\ResourceBundle\Configuration\ConfigurationBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;

/**
 * Class EkynaResourceExtension
 * @package Ekyna\Bundle\ResourceBundle\DependencyInjection
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class EkynaResourceExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $this->configureResources($config['resources'], $container);

        if (!$container->hasParameter('ekyna_resource.translation_mapping')) {
            $container->setParameter('ekyna_resource.translation_mapping', []);
        }
    }

    /**
     * Configures the resources.
     *
     * @param array $resources
     * @param ContainerBuilder $container
     */
    private function configureResources(array $resources, ContainerBuilder $container)
    {
        $builder = new ConfigurationBuilder($container);
        foreach ($resources as $prefix => $config) {
            foreach ($config as $resourceName => $parameters) {
                $builder
                    ->configure($prefix, $resourceName, $parameters)
                    ->build();
            }
        }
    }
}
