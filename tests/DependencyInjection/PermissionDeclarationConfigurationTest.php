<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\DuplicateKeyException;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tomsgu\PermissionBundle\Catalogue\PermissionCatalogue;
use Tomsgu\PermissionBundle\DependencyInjection\TomsguPermissionExtension;

class PermissionDeclarationConfigurationTest extends TestCase
{
    public function testAPlainNameAndDescriptionListStillWorks(): void
    {
        $permissions = $this->load([[
            'permissions' => [
                ['name' => 'POST_EDIT', 'description' => 'Can edit posts.'],
                ['name' => 'POST_DELETE', 'description' => 'Can delete posts.'],
            ],
        ]])->getParameter('tomsgu_permission.permissions');

        self::assertSame(['POST_EDIT', 'POST_DELETE'], array_keys($permissions));
        self::assertSame(
            ['name' => 'POST_EDIT', 'description' => 'Can edit posts.', 'label' => null, 'category' => null, 'level' => null],
            $permissions['POST_EDIT']
        );
    }

    public function testALaterConfigurationOverridesAndReordersAPermission(): void
    {
        $container = $this->load([
            ['permissions' => [
                ['name' => 'LIBRARY_EXPORT', 'description' => 'Exports.'],
                ['name' => 'LIBRARY_EDIT', 'description' => 'Edits.'],
            ]],
            [
                'categories' => ['reports' => 'Reports', 'content' => ['label' => 'Content', 'description' => 'Posts and pages']],
                'permissions' => [
                    ['name' => 'POST_EDIT', 'label' => 'Edit posts', 'category' => 'content'],
                    ['name' => 'LIBRARY_EXPORT', 'label' => 'Export reports', 'category' => 'reports'],
                ],
            ],
        ]);
        $permissions = $container->getParameter('tomsgu_permission.permissions');

        self::assertSame(['LIBRARY_EDIT', 'POST_EDIT', 'LIBRARY_EXPORT'], array_keys($permissions));
        self::assertSame('Exports.', $permissions['LIBRARY_EXPORT']['description']);
        self::assertSame('Export reports', $permissions['LIBRARY_EXPORT']['label']);
        self::assertSame('reports', $permissions['LIBRARY_EXPORT']['category']);
        self::assertSame(
            ['reports' => ['label' => 'Reports', 'description' => null], 'content' => ['label' => 'Content', 'description' => 'Posts and pages']],
            $container->getParameter('tomsgu_permission.categories')
        );
        self::assertTrue($container->hasDefinition(PermissionCatalogue::class));
    }

    public function testAPermissionCannotPointAtAnUnknownCategory(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('POST_EDIT (content)');

        $this->load([['permissions' => [['name' => 'POST_EDIT', 'category' => 'content']]]]);
    }

    public function testAPermissionCanNameADeclaredLevel(): void
    {
        $container = $this->load([[
            'levels' => ['edit' => 'Changes data', 'delete' => 'Deletes data'],
            'permissions' => [['name' => 'POST_EDIT', 'level' => 'edit'], ['name' => 'POST_SHOW']],
        ]]);
        $permissions = $container->getParameter('tomsgu_permission.permissions');

        self::assertSame(['edit' => 'Changes data', 'delete' => 'Deletes data'], $container->getParameter('tomsgu_permission.levels'));
        self::assertSame('edit', $permissions['POST_EDIT']['level']);
        self::assertNull($permissions['POST_SHOW']['level']);
    }

    public function testAPermissionCannotPointAtAnUnknownLevel(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('POST_EDIT (edit)');

        $this->load([['permissions' => [['name' => 'POST_EDIT', 'level' => 'edit']]]]);
    }

    public function testTheSameNameTwiceInOneConfigurationIsRejected(): void
    {
        $this->expectException(DuplicateKeyException::class);

        $this->load([['permissions' => [['name' => 'POST_EDIT'], ['name' => 'POST_EDIT']]]]);
    }

    /**
     * @param array<int, array<string, mixed>> $configs
     */
    private function load(array $configs): ContainerBuilder
    {
        $container = new ContainerBuilder();
        (new TomsguPermissionExtension())->load($configs, $container);

        return $container;
    }
}
