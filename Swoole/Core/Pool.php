<?php

namespace Swoole\Core;

/**
 * 连接池类，1个task进程持久化1个连接
 *
 * @author higher
 */
class Pool
{
    /**
     * Idle resources in pool.
     *
     * @var unique array
     */
    public $idle_resources;

    /**
     * All resources in pool.
     *
     * @var unique array
     */
    protected $resources;

    /**
     * Init resources pool
     *
     * @param  array unique resources
     * @return void
     */
    public function __construct($resources)
    {
        if (!is_array($resources) || !$resources) {
            throw new \Exception('You must set at least one resources!');
        }
        $this->resources = $this->idle_resources = $resources;
    }

    /**
     * Get a free resource from idle pool
     *
     * @param  mixed (int|string) optional $mark
     * @return mixed
     */
    public function getFreeResource($mark = false)
    {
        $resource = null;
        if (!$mark || !isset($this->idle_resources[$mark])) {
            $resource = array_shift($this->idle_resources);
        } else if (isset($this->idle_resources[$mark])) {
            $resource = $this->idle_resources[$mark];
            unset($this->idle_resources[$mark]);
        }

        return $resource;
    }

    /**
     * Free a resource to idle pool
     *
     * @param  mixed (int|string) $mark
     * @param  mixed optional     $resource
     * @return mixed
     */
    public function freeResource($mark, $resource = null)
    {
        if ($resource) {
            $this->idle_resources[$mark] = $resource;
        } else {
            array_push($this->idle_resources, $mark);
        }

        return true;
    }
}