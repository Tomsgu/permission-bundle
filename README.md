[![Tests](https://github.com/Tomsgu/permission-bundle/actions/workflows/tests.yaml/badge.svg)](https://github.com/Tomsgu/permission-bundle/actions/workflows/tests.yaml)
[![Static Analysis](https://github.com/Tomsgu/permission-bundle/actions/workflows/static-analysis.yaml/badge.svg)](https://github.com/Tomsgu/permission-bundle/actions/workflows/static-analysis.yaml)

# Tomsgu Permission Bundle

A Symfony bundle that provides a simple permission layer for access control. Use it when you need something more flexible than roles but less complex than ACL.

## Installation

```bash
composer require tomsgu/permission-bundle
```

Register the bundle in `config/bundles.php`:

```php
return [
    // ...
    Tomsgu\PermissionBundle\TomsguPermissionBundle::class => ['all' => true],
];
```

## Configuration

```yaml
# config/packages/tomsgu_permission.yaml
tomsgu_permission:
    permissions:
        - { name: "EDIT_POST", description: "Can edit posts" }
        - { name: "DELETE_POST", description: "Can delete posts" }
    database:
        db_driver: orm
        permission_class: App\Entity\Permission
    cache: ~
```

### Create a Permission Entity

```php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Tomsgu\PermissionBundle\Entity\Permission as BasePermission;

#[ORM\Entity]
#[ORM\Table(name: 'permission')]
class Permission extends BasePermission
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    protected int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
```

### Implement UserPermissionInterface

Your `User` class must implement `UserPermissionInterface` to work with the `UserManager`:

```php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Tomsgu\PermissionBundle\Model\UserPermissionInterface;

#[ORM\Entity]
class User implements UserPermissionInterface
{
    #[ORM\ManyToMany(targetEntity: Permission::class)]
    #[ORM\JoinTable(name: 'users_permissions')]
    protected array $permissions;

    public function getId(): int
    {
        return $this->id;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }
}
```

## Usage

Inject `UserManagerInterface` to check permissions:

```php
use Tomsgu\PermissionBundle\Model\UserManagerInterface;

class PostController
{
    public function __construct(private UserManagerInterface $userManager) {}

    public function edit(User $user): void
    {
        if ($this->userManager->hasPermission($user, 'EDIT_POST')) {
            // ...
        }
    }
}
```

### Describing permissions

```yaml
tomsgu_permission:
    categories:
        content: Content
        users:
            label: Users
            description: Accounts and what they may do
    levels:
        edit: Changes data
        delete: Deletes data
    permissions:
        - { name: "POST_EDIT", label: "Edit posts", description: "Can change any post.", category: content, level: edit }
        - { name: "POST_DELETE", label: "Delete posts", description: "Can delete any post.", category: content, level: delete }
        - { name: "USER_BAN", label: "Ban users", description: "Can lock an account.", category: users }
```

Only `name` is required. `label`, `description`, `category` and `level` are for presenting permissions
to people. A `level` says how much a permission lets someone do, so an application can mark the ones
that change or delete data; it must be one of the keys declared under `levels`.

Categories are listed in the order they are declared. Permissions are listed in the order they are
declared, and a permission declared again under the same name overrides the earlier declaration and
takes the later position. That lets an application give a label and a category to permissions that
a library declares.

### Showing permissions

`Tomsgu\PermissionBundle\Catalogue\PermissionCatalogue` gives the declared categories and permissions
in display order:

```php
foreach ($catalogue->categories() as $category) {
    foreach ($catalogue->permissionsIn($category->key) as $permission) {
        echo $permission->displayLabel(), ': ', $permission->description;
    }
}
$catalogue->uncategorized();
$catalogue->get('POST_EDIT');
$catalogue->levelLabel('edit'); // "Changes data"
```

### Synchronizing the stored permissions

```bash
php bin/console tomsgu:permission:load
```

Creates the permissions that are missing and refreshes descriptions. Stored permissions that are no
longer declared are listed, and deleted only with `--prune`. Deleting fails if rows in other tables
still refer to the permission, so a join table to groups or users needs `ON DELETE CASCADE`.

## License

MIT License. See [LICENSE](LICENSE) for details.
