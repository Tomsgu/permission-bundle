<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Model;

interface PermissionManagerInterface
{
    public function createPermission(string $name, string $description): PermissionInterface;
    /**
     * Finds one permission by the given criteria.
     *
     * @param array<string, mixed> $criteria
     */
    public function findPermissionBy(array $criteria): ?PermissionInterface;
    /**
     * Gets a permission by the given id.
     */
    public function findPermissionById(int $id): ?PermissionInterface;
    /**
     * @return array<int,PermissionInterface>
     */
    public function findPermissions(): array;
    /**
     * @param array<string,mixed> $criteria
     * @param array<string, 'ASC'|'asc'|'DESC'|'desc'>|null $orderBy
     *
     * @return array<int,PermissionInterface>
     */
    public function findPermissionsBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;
    /**
     * Updates a permission.
     */
    public function updatePermission(PermissionInterface $permission, ?bool $andFlush = true): void;
    /**
     * Deletes a permission.
     */
    public function deletePermission(PermissionInterface $permission): void;
    /**
     * Returns the permission's fully qualified class name.
     */
    public function getClass(): string;
}
