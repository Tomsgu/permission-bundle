<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Tests\Catalogue;

use PHPUnit\Framework\TestCase;
use Tomsgu\PermissionBundle\Catalogue\PermissionCatalogue;

class PermissionCatalogueTest extends TestCase
{
    public function testPermissionsAreGroupedByCategoryInDeclarationOrder(): void
    {
        $catalogue = new PermissionCatalogue(
            [
                ['name' => 'POST_EDIT', 'label' => 'Edit posts', 'description' => 'Can edit posts.', 'category' => 'content'],
                ['name' => 'USER_BAN', 'label' => 'Ban users', 'description' => '', 'category' => 'users'],
                ['name' => 'POST_DELETE', 'label' => null, 'description' => '', 'category' => 'content'],
                ['name' => 'LEGACY', 'description' => 'No category.'],
            ],
            ['users' => ['label' => 'Users', 'description' => 'Accounts'], 'content' => ['label' => 'Content']],
        );

        self::assertSame(['users', 'content'], array_map(static fn ($category) => $category->key, $catalogue->categories()));
        self::assertSame('Accounts', $catalogue->category('users')?->description);
        self::assertSame(['POST_EDIT', 'POST_DELETE'], array_map(static fn ($permission) => $permission->name, $catalogue->permissionsIn('content')));
        self::assertSame(['LEGACY'], array_map(static fn ($permission) => $permission->name, $catalogue->uncategorized()));
        self::assertSame(['POST_EDIT', 'USER_BAN', 'POST_DELETE', 'LEGACY'], array_keys($catalogue->all()));
    }

    public function testAPermissionCarriesItsLevel(): void
    {
        $catalogue = new PermissionCatalogue(
            [['name' => 'POST_DELETE', 'level' => 'delete'], ['name' => 'POST_SHOW']],
            [],
            ['edit' => 'Changes data', 'delete' => 'Deletes data'],
        );

        self::assertSame('delete', $catalogue->get('POST_DELETE')?->level);
        self::assertNull($catalogue->get('POST_SHOW')?->level);
        self::assertSame(['edit' => 'Changes data', 'delete' => 'Deletes data'], $catalogue->levels());
        self::assertSame('Deletes data', $catalogue->levelLabel('delete'));
        self::assertNull($catalogue->levelLabel('unknown'));
    }

    public function testALabelFallsBackToTheName(): void
    {
        $catalogue = new PermissionCatalogue([
            ['name' => 'POST_EDIT', 'label' => 'Edit posts'],
            ['name' => 'POST_DELETE'],
        ]);

        self::assertSame('Edit posts', $catalogue->get('POST_EDIT')?->displayLabel());
        self::assertSame('POST_DELETE', $catalogue->get('POST_DELETE')?->displayLabel());
        self::assertTrue($catalogue->has('POST_DELETE'));
        self::assertFalse($catalogue->has('UNKNOWN'));
        self::assertNull($catalogue->get('UNKNOWN'));
    }
}
