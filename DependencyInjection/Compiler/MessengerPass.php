<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class MessengerPass
 * @package Ekyna\Bundle\ResourceBundle\DependencyInjection\Compiler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class MessengerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->has('messenger.default_bus')) {
            return;
        }

        // Disable message queue
        $container
            ->getDefinition('ekyna_resource.queue.message')
            ->clearTag('kernel.event_listener');
    }
}
