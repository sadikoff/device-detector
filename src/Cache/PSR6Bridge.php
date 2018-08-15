<?php

namespace Koff\DeviceDetector\Cache;

use Psr\Cache\CacheItemPoolInterface;

class PSR6Bridge implements DDCacheInterface
{
    /**
     * @var CacheItemPoolInterface
     */
    private $pool;

    /**
     * PSR6Bridge constructor.
     *
     * @param CacheItemPoolInterface $pool
     */
    public function __construct(CacheItemPoolInterface $pool)
    {
        $this->pool = $pool;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($id)
    {
        $item = $this->pool->getItem($id);

        return $item->isHit() ? $item->get() : false;
    }

    /**
     * {@inheritdoc}
     */
    public function contains($id)
    {
        return $this->pool->hasItem($id);
    }

    /**
     * {@inheritdoc}
     */
    public function save($id, $data, $lifeTime = 0)
    {
        $item = $this->pool->getItem($id);
        $item->set($data);
        if (func_num_args() > 2) {
            $item->expiresAfter($lifeTime);
        }

        return $this->pool->save($item);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        return $this->pool->deleteItem($id);
    }

    /**
     * {@inheritdoc}
     */
    public function flushAll()
    {
        return $this->pool->clear();
    }
}
