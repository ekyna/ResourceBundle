<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

use function call_user_func;

/**
 * Class UploaderPass
 * @package Ekyna\Bundle\ResourceBundle\DependencyInjection\Compiler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class UploaderPass implements CompilerPassInterface
{
    public const UPLOADER_TAG = 'ekyna_resource.uploader';

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container): void
    {
        $types = [];
        foreach ($container->findTaggedServiceIds(self::UPLOADER_TAG, true) as $serviceId => $tag) {
            $types[$serviceId] = new Reference($serviceId);
        }

        $container
            ->getDefinition('ekyna_resource.uploader_resolver')
            ->replaceArgument(0, ServiceLocatorTagPass::register($container, $types, 'resource_uploaders'));
    }
}
