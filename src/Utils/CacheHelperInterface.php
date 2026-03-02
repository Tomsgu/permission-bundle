<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Utils;

interface CacheHelperInterface
{
    /**
     * Saves the given entry to the cache.
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function save(string $cacheId, mixed $entry): void;

    /**
     * Deletes a cache entry.
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function delete(string $cacheId): void;

    /**
     * Get an entry from the cache.
     *
     * @return mixed|null The unserialized entry.
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function get(string $cacheId);
}
