Tomsgu Permission Bundle
========================

A Symfony bundle that provides a simple permission layer for access control. Use it when you need something more flexible than roles but less complex than ACL.

Installation
------------

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

Configuration
-------------

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

Usage
-----

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

### Loading Permissions

Load permissions defined in your configuration into the database:

```bash
php bin/console tomsgu:permission:load
```

License
-------

MIT License. See [LICENSE](LICENSE) for details.
