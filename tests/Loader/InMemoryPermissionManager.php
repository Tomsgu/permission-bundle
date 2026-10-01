<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Tests\Loader;

use Tomsgu\PermissionBundle\Entity\Permission;
use Tomsgu\PermissionBundle\Manager\PermissionManager;
use Tomsgu\PermissionBundle\Model\PermissionInterface;

final class InMemoryPermissionManager extends PermissionManager
{
    /**
     * @var array<string, PermissionInterface>
     */
    public array $stored = [];

    public int $flushes = 0;

    /**
     * @var list<PermissionInterface>
     */
    private array $pending = [];

    public function store(string $name, string $description): void
    {
        $this->stored[$name] = $this->createPermission($name, $description);
    }

    public function findPermissionBy(array $criteria): ?PermissionInterface
    {
        return $this->findPermissionsBy($criteria)[0] ?? null;
    }

    public function findPermissionsBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return array_values(array_filter(
            $this->stored,
            static fn (PermissionInterface $permission): bool => !isset($criteria['name']) || $permission->getName() === $criteria['name']
        ));
    }

    public function updatePermission(PermissionInterface $permission, ?bool $andFlush = true): void
    {
        $this->pending[] = $permission;
        if ($andFlush === true) {
            foreach ($this->pending as $pending) {
                $this->stored[$pending->getName()] = $pending;
            }
            $this->pending = [];
            $this->flushes++;
        }
    }

    public function deletePermission(PermissionInterface $permission): void
    {
        unset($this->stored[$permission->getName()]);
    }

    public function getClass(): string
    {
        return TestPermission::class;
    }
}

final class TestPermission extends Permission
{
    public function getId(): mixed
    {
        return null;
    }
}
