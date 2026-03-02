<?php

declare(strict_types=1);

namespace Tomsgu\PermissionBundle\Tests\Utils;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Tomsgu\PermissionBundle\Utils\CacheHelper;

class CacheHelperTest extends TestCase
{
    private CacheItemPoolInterface&MockObject $pool;
    private CacheHelper $cacheHelper;

    protected function setUp(): void
    {
        $this->pool = $this->createMock(CacheItemPoolInterface::class);
        $this->cacheHelper = new CacheHelper($this->pool, 3600);
    }

    public function testSave(): void
    {
        $item = $this->createMock(CacheItemInterface::class);

        $this->pool->expects($this->once())
            ->method('getItem')
            ->with('cache_key')
            ->willReturn($item);

        $item->expects($this->once())->method('set')->with(['data']);
        $item->expects($this->once())->method('expiresAfter')->with(3600);

        $this->pool->expects($this->once())->method('save')->with($item);

        $this->cacheHelper->save('cache_key', ['data']);
    }

    public function testGet(): void
    {
        $item = $this->createStub(CacheItemInterface::class);
        $item->method('isHit')->willReturn(true);
        $item->method('get')->willReturn(['cached_data']);

        $this->pool->expects($this->once())
            ->method('getItem')
            ->with('cache_key')
            ->willReturn($item);

        $this->assertSame(['cached_data'], $this->cacheHelper->get('cache_key'));
    }

    public function testGetReturnsNullOnMiss(): void
    {
        $item = $this->createStub(CacheItemInterface::class);
        $item->method('isHit')->willReturn(false);

        $this->pool->expects($this->once())
            ->method('getItem')
            ->with('cache_key')
            ->willReturn($item);

        $this->assertNull($this->cacheHelper->get('cache_key'));
    }

    public function testDelete(): void
    {
        $this->pool->expects($this->once())
            ->method('deleteItem')
            ->with('cache_key');

        $this->cacheHelper->delete('cache_key');
    }

}
