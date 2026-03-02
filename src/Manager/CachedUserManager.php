<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Manager;

use Tomsgu\PermissionBundle\Model\UserManagerAbstract;
use Tomsgu\PermissionBundle\Model\UserManagerInterface;
use Tomsgu\PermissionBundle\Model\UserPermissionInterface;
use Tomsgu\PermissionBundle\Utils\CacheHelperInterface;

class CachedUserManager extends UserManagerAbstract implements UserManagerInterface
{
    public function __construct(private CacheHelperInterface $cacheHelper, private string $cachePrefix)
    {
    }

    public function getPermissions(UserPermissionInterface $user): array
    {
        $cacheID = $this->cachePrefix . $this->resolveUserId($user);
        /** @var array<int,string>|null $permissions */
        $permissions = $this->cacheHelper->get($cacheID);
        if ($permissions === null) {
            $permissions = $this->getUserPermissions($user);
            if ($permissions !== []) {
                $this->cacheHelper->save($cacheID, $permissions);
            }
        }

        return $permissions;
    }

    public function invalidatePermissions(UserPermissionInterface $user): void
    {
        $cacheID = $this->cachePrefix . $this->resolveUserId($user);
        $this->cacheHelper->delete($cacheID);
    }

    private function resolveUserId(UserPermissionInterface $user): string
    {
        $id = $user->getId();

        if (is_int($id)) {
            return (string) $id;
        }

        if (is_string($id)) {
            return $id;
        }

        if ($id instanceof \Stringable) {
            return (string) $id;
        }

        throw new \InvalidArgumentException(sprintf(
            'User ID must be an int, string, or Stringable, got "%s".',
            get_debug_type($id),
        ));
    }
}
