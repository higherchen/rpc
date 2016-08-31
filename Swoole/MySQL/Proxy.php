<?php

namespace Swoole\MySQL;

use Swoole\Core\Di;
use Swoole\Core\Pool;

class Proxy
{

    public function __construct($db_config)
    {
        $_pool_map = [];
        $task_worker_num = 0;

        foreach ($db_config as $name => $item) {
            $maxconn = $item['maxconn'];
            $_pool_map[$name] = range($task_worker_num, $task_worker_num + $maxconn - 1);
            $task_worker_num += $maxconn;
        }

        foreach ($_pool_map as $name => $task_ids) {
            $_pool_map[$name] = new Pool($task_ids);
        }

        Di::set('pool_map', $_pool_map);

        Di::get('server')->configure('task_worker_num', array_sum(array_column($db_config, 'maxconn')));
    }

    public function onTask($serv, $task_id, $from_id, $data)
    {
        return (new Resolve($task_id, $data))->run();
    }

    public function onFinish($serv, $task_id, $data)
    {
        // 可以记录日志 或者 处理异常
        echo "Finish OK\n";
    }

}