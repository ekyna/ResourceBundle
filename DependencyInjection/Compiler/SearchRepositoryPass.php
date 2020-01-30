<?php

namespace Ekyna\Bundle\ResourceBundle\DependencyInjection\Compiler;

use Ekyna\Component\Resource\Search\Search;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class SearchRepositoryPass
 * @package Ekyna\Bundle\ResourceBundle\DependencyInjection\Compiler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SearchRepositoryPass implements CompilerPassInterface
{
    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(Search::class)) {
            return;
        }

        $registry = $container->getDefinition(Search::class);
        $repositories = $container->findTaggedServiceIds('ekyna_resource.search');
        $resources = [];

        foreach ($repositories as $id => $tags) {
            foreach ($tags as $attributes) {
                if (!isset($attributes['resource'])) {
                    throw new \RuntimeException(
                        "The attribute 'resource' is required on tag 'ekyna_resource.search' on service $id."
                    );
                }

                $registry->addMethodCall('addRepository', [$attributes['resource'], new Reference($id)]);
                $resources[] = $attributes['resource'];
            }
        }

        $container->setParameter('ekyna_resource.search_resources', $resources);
    }
}
