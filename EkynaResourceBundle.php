<?php

namespace Ekyna\Bundle\ResourceBundle;

use Ekyna\Bundle\ResourceBundle\DependencyInjection\Compiler\FosElasticaPass;
use Ekyna\Bundle\ResourceBundle\DependencyInjection\Compiler\ResourceRegistryPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class EkynaResourceBundle
 * @package Ekyna\Bundle\ResourceBundle
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class EkynaResourceBundle extends Bundle
{
    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterListenersPass(
            'ekyna_resource.event_dispatcher',
            'resource.event_listener',
            'resource.event_subscriber'
        ));

        $container->addCompilerPass(new ResourceRegistryPass());
        $container->addCompilerPass(new FosElasticaPass());
    }
}
