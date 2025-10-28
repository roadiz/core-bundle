<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class JoinNodeTypeFieldConfiguration implements ConfigurationInterface
{
    #[\Override]
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('join');
        $root = $builder->getRootNode();
        $root->children()
            ->scalarNode('classname')
                ->isRequired()
                ->cannotBeEmpty()
                ->info('Full qualified class name for Doctrine entity.')
            ->end()
            ->scalarNode('displayable')
                ->isRequired()
                ->cannotBeEmpty()
                ->info('Method name to display entity name/title as a string.')
            ->end()
            ->scalarNode('alt_displayable')
                ->info('Method name to display entity secondary information as a string.')
            ->end()
            ->scalarNode('thumbnail')
                ->info('Method name to display a thumbnail document.')
            ->end()
            ->scalarNode('inversedBy')
                ->info('Optional: you can specify an inversedBy relation if you want your App\Entity\City object to be able to access its nodes-sources')
            ->end()
            ->scalarNode('inversed_by')
                ->info('Alias for inversedBy.')
            ->end()
            ->arrayNode('searchable')
                ->requiresAtLeastOneElement()
                ->prototype('scalar')
                ->cannotBeEmpty()
                ->end()
                ->info('Searchable entity fields for entity explorer.')
            ->end()
            ->arrayNode('where')
                ->prototype('array')
                    ->children()
                        ->scalarNode('field')->end()
                        ->scalarNode('value')->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('orderBy')
                ->prototype('array')
                    ->children()
                        ->scalarNode('field')->end()
                        ->scalarNode('direction')->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('proxy')
                ->children()
                    ->scalarNode('classname')
                        ->info('Full qualified class name for Doctrine proxy entity.')
                    ->end()
                    ->scalarNode('relation')
                        ->info('Field name to link external entity.')
                    ->end()
                    ->scalarNode('self')
                        ->info('Field name to link self entity.')
                    ->end()
                    ->arrayNode('orderBy')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('field')->end()
                                ->scalarNode('direction')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $builder;
    }
}
