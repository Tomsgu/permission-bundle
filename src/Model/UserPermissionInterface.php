<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Model;

interface UserPermissionInterface
{
    /**
     * Get all permissions.
     *
     * @return PermissionInterface[]
     */
    public function getPermissions(): array;

    public function getId(): mixed;
}
