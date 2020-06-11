<?php

namespace Ekyna\Bundle\ResourceBundle\DependencyInjection\Compiler;

use Ekyna\Bundle\ResourceBundle\Doctrine\ContainerRepositoryFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ExtendDoctrine
 * @package Ekyna\Bundle\ResourceBundle\DependencyInjection\Compiler
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ExtendDoctrinePass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        // Replace doctrine container service factory
        $container
            ->getDefinition('doctrine.orm.container_repository_factory')
            ->setClass(ContainerRepositoryFactory::class)
            ->addArgument(new Reference('ekyna_resource.configuration_registry'));
    }
}
