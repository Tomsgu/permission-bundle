1.1.0
==
* Add `categories`, `levels`, and `label`, `category` and `level` on permissions, to the configuration.
* Add `PermissionCatalogue` to read the declared permissions, categories and levels in display order.
* A permission declared again under the same name overrides the earlier declaration.
* The `tomsgu_permission.permissions` parameter is now keyed by permission name.
* `tomsgu:permission:load` now refreshes descriptions, reports permissions that are no longer declared and deletes them with `--prune`.
* Add `PermissionInterface::setDescription()`, implemented by the base `Permission` entity.
* Deprecate `PermissionLoader` in favour of `PermissionSynchronizer`.
