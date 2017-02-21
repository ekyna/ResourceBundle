<?php

namespace Ekyna\Bundle\ResourceBundle\DependencyInjection\Compiler;

use Ekyna\Bundle\ResourceBundle\Elastica\RepositoryManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class FosElasticaPass
 * @package Ekyna\Bundle\ResourceBundle\DependencyInjection\Compiler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class FosElasticaPass implements CompilerPassInterface
{
    // TODO support other drivers than ORM
    const FOS_ELASTICA_MANAGER_ORM = 'fos_elastica.manager.orm';
    const RESOURCE_LOCALE_PROVIDER = 'ekyna_resource.locale.request_provider';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition(self::FOS_ELASTICA_MANAGER_ORM)) {
            $managerDefinition = $container->getDefinition(self::FOS_ELASTICA_MANAGER_ORM);
            $managerDefinition->setClass(RepositoryManager::class);
            $managerDefinition->addMethodCall(
                'setLocaleProvider',
                [new Reference(self::RESOURCE_LOCALE_PROVIDER)]
            );
        }
    }
}
