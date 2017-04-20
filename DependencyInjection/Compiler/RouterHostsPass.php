<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\DependencyInjection\Compiler;

use Ekyna\Bundle\ResourceBundle\Service\Routing\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Parameter;

/**
 * Class RouterHostsPass
 * @package Ekyna\Bundle\ResourceBundle\DependencyInjection\Compiler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RouterHostsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('ekyna_resource.hosts')) {
            return;
        }

        $container
            ->getDefinition('routing.loader.yml')
            ->setClass(YamlFileLoader::class)
            ->addMethodCall('setLocales', [new Parameter('ekyna_resource.locales'), new Parameter('kernel.default_locale')])
            ->addMethodCall('setHosts', [new Parameter('ekyna_resource.hosts')]);
    }
}
