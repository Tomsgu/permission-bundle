<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Loader;

use Tomsgu\PermissionBundle\Catalogue\PermissionCatalogue;
use Tomsgu\PermissionBundle\Model\PermissionInterface;
use Tomsgu\PermissionBundle\Model\PermissionManagerInterface;

/**
 * Makes the stored permissions match the declared ones.
 */
final class PermissionSynchronizer
{
    public function __construct(
        private PermissionManagerInterface $permissionManager,
        private PermissionCatalogue $catalogue,
    ) {
    }

    /**
     * Creates missing permissions and refreshes descriptions. Stored permissions that are no longer
     * declared are deleted only when $prune is true, otherwise they are reported.
     */
    public function synchronize(bool $prune = false): SynchronizationResult
    {
        $stored = [];
        foreach ($this->permissionManager->findPermissions() as $permission) {
            $stored[$permission->getName()] = $permission;
        }

        $created = [];
        $updated = [];
        $lastChanged = null;
        foreach ($this->catalogue->all() as $name => $definition) {
            $permission = $stored[$name] ?? null;
            if ($permission === null) {
                $permission = $this->permissionManager->createPermission($name, $definition->description);
                $created[] = $name;
            } elseif ($permission->getDescription() !== $definition->description) {
                $permission->setDescription($definition->description);
                $updated[] = $name;
            } else {
                continue;
            }
            $this->permissionManager->updatePermission($permission, false);
            $lastChanged = $permission;
        }
        if ($lastChanged instanceof PermissionInterface) {
            $this->permissionManager->updatePermission($lastChanged);
        }

        $removed = [];
        $undeclared = [];
        foreach ($stored as $name => $permission) {
            if ($this->catalogue->has($name)) {
                continue;
            }
            if ($prune) {
                $this->permissionManager->deletePermission($permission);
                $removed[] = $name;
            } else {
                $undeclared[] = $name;
            }
        }

        return new SynchronizationResult($created, $updated, $removed, $undeclared);
    }
}
