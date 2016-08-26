<?php

namespace Swoole\MySQL;

class Proxy
{
    protected static $_config_map;
    protected static $_pool_map;

    public function __construct($db_config)
    {
        $task_worker_num = 0;

        foreach ($db_config as $name => $item) {
            $maxconn = $item['maxconn'];
            static::$_pool_map[$name] = range($task_worker_num, $task_worker_num + $maxconn - 1);
            $task_worker_num += $maxconn;
            static::$_config_map[$name] = [
                'dsn' => $item['dsn'],
                'username' => $item['username'],
                'password' => $item['password'],
                'timeout' => $item['timeout'],
            ];
        }

        foreach (static::$_pool_map as $name => $task_ids) {
            static::$_pool_map[$name] = new Pool($task_ids);
        }

        Di::singleton('server')->configure('task_worker_num', $task_worker_num);
    }

    public static function getFreeTask($name)
    {
        return static::$_pool_map[$name]->getFreeWorker();
    }

    public static function freeTask($name, $task_id)
    {
        return static::$_pool_map[$name]->freeWorker($task_id);
    }

    public function onTask($serv, $task_id, $from_id, $data)
    {

    }
}