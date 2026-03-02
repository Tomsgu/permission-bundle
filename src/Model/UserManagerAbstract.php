<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Model;

abstract class UserManagerAbstract implements UserManagerInterface
{
    public function hasPermissions(UserPermissionInterface $user, array $permissions): bool
    {
        $userPermissions = $this->getPermissions($user);

        foreach ($permissions as $permission) {
            if (!in_array($permission, $userPermissions)) {
                return false;
            }
        }

        return true;
    }

    public function hasPermission(UserPermissionInterface $user, string $permission): bool
    {
        return $this->hasPermissions($user, [$permission]);
    }

    /**
     * @param UserPermissionInterface $user
     *
     * @return array<int,string>
     */
    protected function getUserPermissions(UserPermissionInterface $user): array
    {
        $permissions = [];
        foreach ($user->getPermissions() as $permission) {
            $permissions[] = $permission->getName();
        }

        return $permissions;
    }
}
