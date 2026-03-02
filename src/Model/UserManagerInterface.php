<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Model;

interface UserManagerInterface
{
    /**
     * Gets all permissions from user. If it isn't cached it will add a new entry.
     *
     * @return array<int,string>
     */
    public function getPermissions(UserPermissionInterface $user): array;

    /**
     * Has the given user the given permissions.
     *
     * @param array<int,string> $permissions
     */
    public function hasPermissions(UserPermissionInterface $user, array $permissions): bool;

    /**
     * Has the given user the given permission.
     */
    public function hasPermission(UserPermissionInterface $user, string $permission): bool;

    /**
     * Invalidate permissions of the given user.
     */
    public function invalidatePermissions(UserPermissionInterface $user): void;
}
