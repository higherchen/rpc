<?php

namespace Swoole\Core;

class Container
{
    protected $_services = [];
    protected $_instances = [];

    public function get($alias)
    {
        return isset($this->_services[$alias]) ? $this->_services[$alias] : null;
    }

    public function set($alias, $instance)
    {
        if (!isset($this->_services[$alias])) {
            $this->_services[$alias] = $instance;
            return true;
        }
        return false;
    }

    public function singleton($alias, $parameters = [])
    {
        if (isset($this->instances[$alias])) {
            return $this->instances[$alias];
        }

        if (isset($_services[$alias]) && is_callable($_services[$alias])) {
            $object = call_user_func_array($_services[$alias], $parameters);
        } else {
            $class = new \ReflectionClass($alias);
            $object = $class->newInstanceArgs($parameters);
        }

        if ($object !== null) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    public function make($alias, $parameters = [], $shared = false)
    {
        if ($shared && isset($this->instances[$alias])) {
            return $this->instances[$alias];
        }

        if (isset($_services[$alias]) && is_callable($_services[$alias])) {
            $object = call_user_func_array($_services[$alias], $parameters);
        } else {
            $class = new \ReflectionClass($alias);
            $object = $class->newInstanceArgs($parameters);
        }

        if ($shared && $object !== null) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }
}
