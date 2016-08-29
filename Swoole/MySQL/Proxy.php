<?php

namespace Swoole\MySQL;

use Swoole\Core\Di;
use Swoole\Core\Pool;

class Proxy
{

    public function __construct($db_config)
    {
        $_config_map = $_pool_map = $_task_map = [];
        $task_worker_num = 0;

        foreach ($db_config as $name => $item) {
            $maxconn = $item['maxconn'];
            $_pool_map[$name] = range($task_worker_num, $task_worker_num + $maxconn - 1);
            $_task_map += array_fill($task_worker_num, $maxconn, $name);
            $task_worker_num += $maxconn;
            $_config_map[$name] = [
                'dsn' => $item['dsn'],
                'username' => $item['username'],
                'password' => $item['password'],
                'options' => $item['options'],
            ];
        }

        foreach ($_pool_map as $name => $task_ids) {
            $_pool_map[$name] = new Pool($task_ids);
        }

        Di::set('pool_map', $_pool_map);
        Di::set('config_map', $_config_map);
        Di::set('task_map', $_task_map);

        Di::get('server')->configure('task_worker_num', $task_worker_num);
    }

    public function onTask($serv, $task_id, $from_id, $data)
    {
        static $conn = null;
        if ($conn == null) {
            $name = Di::get('task_map')[$task_id];
            $config = Di::get('config_map')[$name];
            $conn = new \PDO($config['dsn'], $config['username'], $config['password'], $config['options']);
        }
        // 将data、conn传入解析模块
        return (new Resolve($conn, $data))->run();
    }

}