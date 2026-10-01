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
                ->arrayNode('categories')
                    ->info('Groups to present permissions in, in display order.')
                    ->useAttributeAsKey('key')
                    ->arrayPrototype()
                        ->beforeNormalization()
                            ->ifString()
                            ->then(static fn (string $label): array => ['label' => $label])
                        ->end()
                        ->children()
                            ->scalarNode('label')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('description')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('levels')
                    ->info('How much a permission lets someone do, as "key: label".')
                    ->useAttributeAsKey('key')
                    ->scalarPrototype()->cannotBeEmpty()->end()
                ->end()
                ->arrayNode('permissions')
                    ->info('A permission declared again under the same name overrides the earlier declaration.')
                    ->useAttributeAsKey('name', false)
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('label')->defaultNull()->info('Name shown to people instead of the technical one.')->end()
                            ->scalarNode('description')->defaultValue('')->end()
                            ->scalarNode('category')->defaultNull()->info('Key of one of the declared categories.')->end()
                            ->scalarNode('level')->defaultNull()->info('Key of one of the declared levels.')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->validate()
                ->ifTrue(static fn (array $config): bool => self::unknownReferences($config, 'categories', 'category') !== [])
                ->then(static function (array $config): never {
                    throw new \InvalidArgumentException(sprintf(
                        'Permissions refer to categories that are not declared under "tomsgu_permission.categories": %s.',
                        implode(', ', self::unknownReferences($config, 'categories', 'category'))
                    ));
                })
            ->end()
            ->validate()
                ->ifTrue(static fn (array $config): bool => self::unknownReferences($config, 'levels', 'level') !== [])
                ->then(static function (array $config): never {
                    throw new \InvalidArgumentException(sprintf(
                        'Permissions refer to levels that are not declared under "tomsgu_permission.levels": %s.',
                        implode(', ', self::unknownReferences($config, 'levels', 'level'))
                    ));
                })
            ->end();

        $this->addDoctrineSection($treeBuilder);
        $this->addCachedUserManagerSection($treeBuilder);

        return $treeBuilder;
    }

    /**
     * @param array<array-key, mixed> $config
     *
     * @return list<string> "PERMISSION_NAME (key)" for every permission pointing at a key missing from $declaredUnder
     */
    private static function unknownReferences(array $config, string $declaredUnder, string $attribute): array
    {
        $declared = is_array($config[$declaredUnder] ?? null) ? $config[$declaredUnder] : [];
        $permissions = is_array($config['permissions'] ?? null) ? $config['permissions'] : [];

        $unknown = [];
        foreach ($permissions as $name => $permission) {
            $key = is_array($permission) ? ($permission[$attribute] ?? null) : null;
            if (is_string($key) && !isset($declared[$key])) {
                $unknown[] = sprintf('%s (%s)', $name, $key);
            }
        }

        return $unknown;
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
