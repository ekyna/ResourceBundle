<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ResourceBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Ekyna\Bundle\ResourceBundle\DependencyInjection
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('ekyna_resource');

        $root = $builder->getRootNode();

        $this->addI18nNode($root);
        $this->addPdfSection($root);
        $this->addReportNode($root);

        return $builder;
    }

    private function addI18nNode(ArrayNodeDefinition $builder): void
    {
        $builder
            ->children()
                ->arrayNode('i18n')
                    ->canBeDisabled()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('locale')
                            ->cannotBeEmpty()
                            ->defaultValue('en')
                        ->end()
                        ->arrayNode('locales')
                            ->cannotBeEmpty()
                            ->defaultValue(['en'])
                            ->scalarPrototype()->end()
                        ->end()
                        ->arrayNode('hosts')
                            ->defaultValue([])
                            ->useAttributeAsKey('name')
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addPdfSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('pdf')
                    ->isRequired()
                    ->children()
                        ->scalarNode('entry_point')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('token')->isRequired()->cannotBeEmpty()->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addReportNode(ArrayNodeDefinition $builder): void
    {
        $builder
            ->children()
                ->arrayNode('report')
                    ->canBeDisabled()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('email')
                            ->cannotBeEmpty()
                            ->defaultValue('support@ekyan.com')
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
