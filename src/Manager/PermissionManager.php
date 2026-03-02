<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Manager;

use Tomsgu\PermissionBundle\Model\PermissionInterface;
use Tomsgu\PermissionBundle\Model\PermissionManagerInterface;

abstract class PermissionManager implements PermissionManagerInterface
{
    public function createPermission(string $name, string $description): PermissionInterface
    {
        $class = $this->getClass();
        /** @var PermissionInterface $permission */
        $permission = new $class($name, $description);

        return $permission;
    }

    public function findPermissionById(int $id): ?PermissionInterface
    {
        return $this->findPermissionBy(['id' => $id]);
    }

    public function findPermissions(): array
    {
        return $this->findPermissionsBy([]);
    }
}
