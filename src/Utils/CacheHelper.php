<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Utils;

use Psr\Cache\CacheItemPoolInterface;

class CacheHelper implements CacheHelperInterface
{
    public function __construct(private CacheItemPoolInterface $cache, private int $cacheExpireAt)
    {
    }

    public function save(string $cacheId, mixed $entry): void
    {
        $cacheItem = $this->cache->getItem($cacheId);
        $cacheItem->set($entry);
        $cacheItem->expiresAfter($this->cacheExpireAt);
        $this->cache->save($cacheItem);
    }

    public function delete(string $cacheId): void
    {
        $this->cache->deleteItem($cacheId);
    }

    public function get(string $cacheId)
    {
        $cacheItem = $this->cache->getItem($cacheId);
        if (!$cacheItem->isHit()) {
            return null;
        } else {
            return $cacheItem->get();
        }
    }
}
