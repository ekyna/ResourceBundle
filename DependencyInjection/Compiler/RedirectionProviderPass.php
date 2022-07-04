<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class RedirectionProviderPass
 * @package Ekyna\Bundle\ResourceBundle\DependencyInjection\Compiler
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class RedirectionProviderPass implements CompilerPassInterface
{
    private const PROVIDER_TAG = 'ekyna_resource.redirection_provider';

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('ekyna_resource.redirection.provider_registry');

        foreach ($container->findTaggedServiceIds(self::PROVIDER_TAG, true) as $serviceId => $tags) {
            $definition->addMethodCall('addProvider', [new Reference($serviceId)]);
        }
    }
}
