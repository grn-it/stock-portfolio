<?php

namespace AppBundle\Cache\Redis;

use AppBundle\Cache\CacheInterface;

class Cache implements CacheInterface
{
    private $redis;

    /**
     * @param string $host
     * @param string $port
     */
    public function __construct($host, $port)
    {
        $this->redis = new \Redis();

        $this->redis->connect($host, $port);
    }
    
    /**
     * @param string $key
     * @return string
     */
    public function get($key)
    {
        return unserialize($this->redis->get($key));
    }
    
    /**
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->redis->set($key, serialize($value));
    }
}