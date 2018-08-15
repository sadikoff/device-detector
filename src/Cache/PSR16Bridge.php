<?php

namespace Koff\DeviceDetector\Cache;

use Psr\SimpleCache\CacheInterface;

class PSR16Bridge implements DDCacheInterface
{
    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * PSR16Bridge constructor.
     *
     * @param CacheInterface $cache
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param $id
     *
     * @return mixed
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function fetch($id)
    {
        return $this->cache->get($id, false);
    }

    /**
     * @param $id
     *
     * @return bool
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function contains($id)
    {
        return $this->cache->has($id);
    }

    /**
     * @param     $id
     * @param     $data
     * @param int $lifeTime
     *
     * @return bool
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function save($id, $data, $lifeTime = 0)
    {
        return $this->cache->set($id, $data, func_num_args() < 3 ? null : $lifeTime);
    }

    /**
     * @param $id
     *
     * @return bool
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function delete($id)
    {
        return $this->cache->delete($id);
    }

    /**
     * @return bool
     */
    public function flushAll()
    {
        return $this->cache->clear();
    }
}
