<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Manager;

use Tomsgu\PermissionBundle\Model\UserManagerAbstract;
use Tomsgu\PermissionBundle\Model\UserManagerInterface;
use Tomsgu\PermissionBundle\Model\UserPermissionInterface;

class UserManager extends UserManagerAbstract implements UserManagerInterface
{
    public function getPermissions(UserPermissionInterface $user): array
    {
        return $this->getUserPermissions($user);
    }

    public function invalidatePermissions(UserPermissionInterface $user): void
    {
    }
}
