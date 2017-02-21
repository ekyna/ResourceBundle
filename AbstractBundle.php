<?php

namespace Ekyna\Bundle\ResourceBundle;

use Ekyna\Bundle\ResourceBundle\DependencyInjection\Compiler\ResolveDoctrineTargetEntitiesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class AbstractBundle
 * @package Ekyna\Bundle\ResourceBundle
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $interfaces = $this->getModelInterfaces();
        if (!empty($interfaces)) {
            $container->addCompilerPass(
                new ResolveDoctrineTargetEntitiesPass($interfaces)
            );
        }
    }

    /**
     * Target entities resolver configuration (Interface - Model).
     *
     * @return array
     */
    protected function getModelInterfaces()
    {
        return [];
    }
}
