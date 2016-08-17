<?php

namespace Ekyna\Bundle\ResourceBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ResourceRegistryPass
 * @package Ekyna\Bundle\ResourceBundle\DependencyInjection\Compiler
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceRegistryPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ekyna_resource.configuration_registry')) {
            return;
        }

        $definition = $container->getDefinition('ekyna_resource.configuration_registry');

        $configurations = [];
        foreach ($container->findTaggedServiceIds('ekyna_resource.configuration') as $serviceId => $tag) {
            $alias = isset($tag[0]['alias']) ? $tag[0]['alias'] : $serviceId;
            $configurations[$alias] = new Reference($serviceId);
        }
        $definition->replaceArgument(0, $configurations);
    }
}
