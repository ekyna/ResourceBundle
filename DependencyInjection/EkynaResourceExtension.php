<?php

namespace Ekyna\Bundle\ResourceBundle\DependencyInjection;

use Ekyna\Bundle\ResourceBundle\Configuration\ConfigurationBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class EkynaResourceExtension
 * @package Ekyna\Bundle\ResourceBundle\DependencyInjection
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class EkynaResourceExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $this->configureResources($config['resources'], $container);

        /* Inheritance mapping = [
         *     resource_id => [
         *         'class' => Class,
         *         'repository' => Repository class,
         *     ]
         * ] */
        if (!$container->hasParameter('ekyna_resource.entities')) {
            $container->setParameter('ekyna_resource.entities', []);
        }

        /* Target entities resolution
         * [ Interface => Class or class parameter ]
         */
        if (!$container->hasParameter('ekyna_resource.interfaces')) {
            $container->setParameter('ekyna_resource.interfaces', []);
        }

        /* Translation mapping = [
         *     Translatable class => Translation class,
         *     Translation class  => Translatable class,
         * ]
         */
        if (!$container->hasParameter('ekyna_resource.translation_mapping')) {
            $container->setParameter('ekyna_resource.translation_mapping', []);
        }
    }

    /**
     * Configures the resources.
     *
     * @param array            $resources
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
