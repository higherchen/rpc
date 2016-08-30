<?php

namespace Swoole\MySQL;

use Swoole\Core\Di;

class Client
{
    // db name
    protected $database;

    // transaction
    protected $trans = false;

    protected $message;

    public function __construct($database = 'default')
    {
        $this->database = $database;
    }

    public function query($statement, $options = [])
    {
        $this->message = ['query', $statement, $options];
        return $this->call();
    }

    public function queryRow($statement, $options = [])
    {
        $this->message = ['queryRow', $statement, $options];
        return $this->call();
    }

    public function execute($statement, $options = [])
    {
        if ($this->trans) {
            $this->message[] = ['execute', $statement, $options];
        } else {
            $this->message = ['execute', $statement, $options];
            return $this->call();
        }
    }

    public function beginTransaction()
    {
        $this->trans = true;
    }

    public function commit()
    {
        return $this->call();
    }

    protected function call()
    {
        $data = ['trans' => $this->trans, 'query' => $this->message, 'database' => $this->database];
        $task_id = Di::get('pool_map')[$this->database]->getFreeResource();
        $response = Di::get('server')->getServ()->taskwait($data, 0.5, $task_id);
        Di::get('pool_map')[$this->database]->freeResource($task_id);

        $this->trans = false;
        $this->message = null;
        return $response;
    }
}