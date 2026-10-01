<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Loader;

use Tomsgu\PermissionBundle\Model\PermissionManagerInterface;

/**
 * Load permissions to the database.
 *
 * @deprecated since 1.1, use PermissionSynchronizer, which also refreshes descriptions and can delete undeclared permissions.
 */
class PermissionLoader implements PermissionLoaderInterface
{
    /**
     * @param array<array-key, array{name: string, description: string}> $permissions
     */
    public function __construct(private PermissionManagerInterface $permissionManager, private array $permissions)
    {
    }

    public function loadPermissions(): void
    {
        $persistedPermission = null;
        foreach ($this->permissions as $permission) {
            if ($this->permissionManager->findPermissionBy(['name' => $permission['name']]) === null) {
                $newPermission = $this->permissionManager->createPermission($permission['name'], $permission['description']);
                $this->permissionManager->updatePermission($newPermission, false);
                $persistedPermission = $newPermission;
            }
        }
        if ($persistedPermission !== null) {
            $this->permissionManager->updatePermission($persistedPermission);
        }
    }
}
