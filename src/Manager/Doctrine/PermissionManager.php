<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Manager\Doctrine;

use Doctrine\Persistence\ObjectManager;
use Tomsgu\PermissionBundle\Manager\PermissionManager as BasePermissionManager;
use Tomsgu\PermissionBundle\Model\PermissionInterface;

class PermissionManager extends BasePermissionManager
{
    /**
     * @param class-string $class
     */
    public function __construct(private ObjectManager $objectManager, private string $class)
    {
    }

    public function findPermissionBy(array $criteria): ?PermissionInterface
    {
        /** @var null|PermissionInterface */
        return $this->objectManager->getRepository($this->getClass())->findOneBy($criteria);
    }

    public function findPermissionsBy(
        array $criteria,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null
    ): array {
        /** @var array<int, PermissionInterface> */
        return $this->objectManager->getRepository($this->getClass())->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function updatePermission(PermissionInterface $permission, ?bool $andFlush = true): void
    {
        $this->objectManager->persist($permission);
        if ($andFlush === true) {
            $this->objectManager->flush();
        }
    }

    public function deletePermission(PermissionInterface $permission): void
    {
        $this->objectManager->remove($permission);
        $this->objectManager->flush();
    }

    /**
     * Returns the permission's fully qualified class name.
     *
     * @return class-string
     */
    public function getClass(): string
    {
        if (str_contains($this->class, ':') === true) {
            $metadata = $this->objectManager->getClassMetadata($this->class);
            $this->class = $metadata->getName();
        }

        return $this->class;
    }
}
