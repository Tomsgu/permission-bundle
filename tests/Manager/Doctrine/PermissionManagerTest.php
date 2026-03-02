<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Tests\Manager\Doctrine;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tomsgu\PermissionBundle\Entity\Permission;
use Tomsgu\PermissionBundle\Manager\Doctrine\PermissionManager;
use Tomsgu\PermissionBundle\Model\PermissionInterface;

class TestPermission extends Permission
{
    public function getId(): mixed
    {
        return null;
    }
}

class PermissionManagerTest extends TestCase
{
    /**
     * @return array{PermissionManager, ObjectRepository}
     */
    private function createManagerWithStubs(): array
    {
        $objectManager = $this->createStub(ObjectManager::class);
        $repository = $this->createStub(ObjectRepository::class);
        $objectManager->method('getRepository')->willReturn($repository);

        return [new PermissionManager($objectManager, TestPermission::class), $repository];
    }

    /**
     * @return array{PermissionManager, ObjectManager&MockObject}
     */
    private function createManagerWithMock(): array
    {
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->method('getRepository')->willReturn($this->createStub(ObjectRepository::class));

        return [new PermissionManager($objectManager, TestPermission::class), $objectManager];
    }

    public function testCreatePermission(): void
    {
        [$manager] = $this->createManagerWithStubs();

        $permission = $manager->createPermission('ROLE_ADMIN', 'Admin access');

        $this->assertInstanceOf(TestPermission::class, $permission);
        $this->assertSame('ROLE_ADMIN', $permission->getName());
        $this->assertSame('Admin access', $permission->getDescription());
    }

    public function testFindPermissionBy(): void
    {
        [$manager, $repository] = $this->createManagerWithStubs();
        $expected = $this->createStub(PermissionInterface::class);
        $repository->method('findOneBy')->willReturn($expected);

        $this->assertSame($expected, $manager->findPermissionBy(['name' => 'ROLE_ADMIN']));
    }

    public function testFindPermissionById(): void
    {
        [$manager, $repository] = $this->createManagerWithStubs();
        $expected = $this->createStub(PermissionInterface::class);
        $repository->method('findOneBy')->willReturn($expected);

        $this->assertSame($expected, $manager->findPermissionById(5));
    }

    public function testFindPermissionsBy(): void
    {
        [$manager, $repository] = $this->createManagerWithStubs();
        $expected = [$this->createStub(PermissionInterface::class)];
        $repository->method('findBy')->willReturn($expected);

        $this->assertSame($expected, $manager->findPermissionsBy(['name' => 'ROLE_ADMIN']));
    }

    public function testFindPermissions(): void
    {
        [$manager, $repository] = $this->createManagerWithStubs();
        $expected = [$this->createStub(PermissionInterface::class)];
        $repository->method('findBy')->willReturn($expected);

        $this->assertSame($expected, $manager->findPermissions());
    }

    public function testUpdatePermission(): void
    {
        [$manager, $objectManager] = $this->createManagerWithMock();
        $permission = $this->createStub(PermissionInterface::class);

        $objectManager->expects($this->once())->method('persist')->with($permission);
        $objectManager->expects($this->once())->method('flush');

        $manager->updatePermission($permission);
    }

    public function testUpdatePermissionWithoutFlush(): void
    {
        [$manager, $objectManager] = $this->createManagerWithMock();
        $permission = $this->createStub(PermissionInterface::class);

        $objectManager->expects($this->once())->method('persist')->with($permission);
        $objectManager->expects($this->never())->method('flush');

        $manager->updatePermission($permission, false);
    }

    public function testDeletePermission(): void
    {
        [$manager, $objectManager] = $this->createManagerWithMock();
        $permission = $this->createStub(PermissionInterface::class);

        $objectManager->expects($this->once())->method('remove')->with($permission);
        $objectManager->expects($this->once())->method('flush');

        $manager->deletePermission($permission);
    }

    public function testGetClass(): void
    {
        [$manager] = $this->createManagerWithStubs();

        $this->assertSame(TestPermission::class, $manager->getClass());
    }

    public function testGetClassResolvesDoctrineAlias(): void
    {
        $objectManager = $this->createMock(ObjectManager::class);
        $manager = new PermissionManager($objectManager, 'Bundle:Entity');

        $metadata = $this->createStub(ClassMetadata::class);
        $metadata->method('getName')->willReturn(TestPermission::class);

        $objectManager->expects($this->once())
            ->method('getClassMetadata')
            ->with('Bundle:Entity')
            ->willReturn($metadata);

        $this->assertSame(TestPermission::class, $manager->getClass());
    }
}
