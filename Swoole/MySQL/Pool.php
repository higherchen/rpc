<?php

namespace Swoole\MySQL;

/**
 * 连接池类，1个task进程持久化1个连接
 * 维护单个数据库实例的连接池.
 *
 * @author higher
 */
class Pool
{
    protected $tasks;
    protected $idle_tasks;

    public function __construct($task_ids)
    {
        if (!is_array($task_ids) || !$task_ids) {
            throw new \Exception('You must set at least one task worker!');
        }
        $this->tasks = $this->idle_tasks = $task_ids;
    }

    public function getFreeWorker()
    {
        $task_id = array_shift($this->idle_tasks);

        return $task_id;
    }

    public function freeWorker($task_id)
    {
        if (!in_array($task_id, $this->tasks) || in_array($task_id, $this->idle_tasks)) {
            return false;
        }
        array_push($this->idle_tasks, $task_id);

        return true;
    }
}