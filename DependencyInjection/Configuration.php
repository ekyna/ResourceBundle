<?php

namespace Ekyna\Bundle\ResourceBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Ekyna\Bundle\ResourceBundle\DependencyInjection
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ekyna_resource');

        $rootNode
            ->children()
                ->append($this->getResourcesSection())
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * Returns the resources configuration definition.
     *
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function getResourcesSection()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('resources');

        $node
            ->useAttributeAsKey('prefix') // TODO rename as 'namespace'
            ->prototype('array')
                ->useAttributeAsKey('name')
                ->prototype('array')
                    ->children()
                        ->variableNode('templates')->end() // TODO normalization ?
                        ->scalarNode('entity')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('controller')->end()
                        ->scalarNode('repository')->end()
                        ->scalarNode('operator')->end()
                        ->scalarNode('event')->end()
                        ->scalarNode('form')->end()
                        ->scalarNode('table')->end()
                        ->scalarNode('parent')->end()
                        ->arrayNode('translation')
                            ->children()
                                ->scalarNode('entity')->end()
                                ->scalarNode('repository')->end()
                                ->arrayNode('fields')
                                    ->prototype('scalar')->end()
                                    ->defaultValue([])
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
