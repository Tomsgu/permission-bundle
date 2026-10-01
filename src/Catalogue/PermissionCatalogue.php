<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Catalogue;

/**
 * Every permission, category and level declared in the configuration, in display order.
 */
final class PermissionCatalogue
{
    /**
     * @var array<string, PermissionDefinition>
     */
    private array $permissions = [];

    /**
     * @var array<string, PermissionCategory>
     */
    private array $categories = [];

    /**
     * @param array<array-key, array{name: string, label?: string|null, description?: string|null, category?: string|null, level?: string|null}> $permissions
     * @param array<string, array{label: string, description?: string|null}>                                                                     $categories
     * @param array<string, string>                                                                                                              $levels      label by key
     */
    public function __construct(array $permissions, array $categories = [], private readonly array $levels = [])
    {
        foreach ($categories as $key => $category) {
            $this->categories[$key] = new PermissionCategory($key, $category['label'], $category['description'] ?? null);
        }
        foreach ($permissions as $permission) {
            $this->permissions[$permission['name']] = new PermissionDefinition(
                $permission['name'],
                $permission['label'] ?? null,
                $permission['description'] ?? '',
                $permission['category'] ?? null,
                $permission['level'] ?? null,
            );
        }
    }

    /**
     * @return array<string, PermissionDefinition>
     */
    public function all(): array
    {
        return $this->permissions;
    }

    public function has(string $name): bool
    {
        return isset($this->permissions[$name]);
    }

    public function get(string $name): ?PermissionDefinition
    {
        return $this->permissions[$name] ?? null;
    }

    /**
     * @return list<PermissionCategory>
     */
    public function categories(): array
    {
        return array_values($this->categories);
    }

    public function category(string $key): ?PermissionCategory
    {
        return $this->categories[$key] ?? null;
    }

    /**
     * @return array<string, string> label by key
     */
    public function levels(): array
    {
        return $this->levels;
    }

    public function levelLabel(string $key): ?string
    {
        return $this->levels[$key] ?? null;
    }

    /**
     * @return list<PermissionDefinition>
     */
    public function permissionsIn(string $categoryKey): array
    {
        return array_values(array_filter(
            $this->permissions,
            static fn (PermissionDefinition $permission): bool => $permission->category === $categoryKey
        ));
    }

    /**
     * @return list<PermissionDefinition>
     */
    public function uncategorized(): array
    {
        return array_values(array_filter(
            $this->permissions,
            static fn (PermissionDefinition $permission): bool => $permission->category === null
        ));
    }
}
