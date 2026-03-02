<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('tomsgu_permission');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('permissions')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->end()
                            ->scalarNode('description')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        $this->addDoctrineSection($treeBuilder);
        $this->addCachedUserManagerSection($treeBuilder);

        return $treeBuilder;
    }

    private function addCachedUserManagerSection(TreeBuilder $treeBuilder): void
    {
        $treeBuilder
            ->getRootNode()
            ->children()
                ->arrayNode('cache')
                ->canBeUnset()
                    ->children()
                        ->scalarNode('cache_prefix')
                            ->defaultValue('tomsgu_permission.user.permissions.')
                            ->cannotBeEmpty()
                        ->end()
                        ->integerNode('expires_at')
                            ->defaultValue(86400)
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addDoctrineSection(TreeBuilder $treeBuilder): void
    {
        $supportedDrivers = ['orm'];

        $treeBuilder
            ->getRootNode()
            ->children()
                ->arrayNode('database')
                    ->canBeUnset()
                    ->children()
                        ->scalarNode('db_driver')
                            ->validate()
                                ->ifNotInArray($supportedDrivers)
                                ->thenInvalid('The driver %s is not supported. Please choose one of ' . json_encode($supportedDrivers))
                            ->end()
                            ->cannotBeOverwritten()
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('object_manager_name')->defaultNull()->end()
                        ->scalarNode('permission_class')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('permission_manager_class')->defaultValue('tomsgu_permission.permission_manager.default')->end()
                    ->end()
                ->end()
            ->end();
    }
}
