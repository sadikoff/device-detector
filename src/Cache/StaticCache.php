<?php

namespace Koff\DeviceDetector\Cache;

/**
 * Class StaticCache.
 *
 * Simple Cache that caches in a static property
 * (Speeds up multiple detections in one request)
 */
class StaticCache implements DDCacheInterface
{
    /**
     * Holds the static cache data.
     *
     * @var array
     */
    protected static $staticCache = array();

    public function fetch($id)
    {
        return $this->contains($id) ? self::$staticCache[$id] : false;
    }

    public function contains($id)
    {
        return isset(self::$staticCache[$id]) || array_key_exists($id, self::$staticCache);
    }

    public function save($id, $data, $lifeTime = 0)
    {
        self::$staticCache[$id] = $data;

        return true;
    }

    public function delete($id)
    {
        unset(self::$staticCache[$id]);

        return true;
    }

    public function flushAll()
    {
        self::$staticCache = array();

        return true;
    }
}
