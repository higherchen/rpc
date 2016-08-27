<?php

namespace Swoole\MySQL;

class Client
{
    // db name
    protected $database;
    
    // prepared
    protected $prepared = false;

    // trans
    protected $trans = false;

    // message
    protected $message;

    // ready
    protected $ready = true;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function prepare($statement, $options = [])
    {
        $this->prepared = true;
        $this->ready = false;
        $this->message[] = ['prepare' => [$statement, $options]];
        return $this;
    }

    public function beginTransaction()
    {
        $this->chains = true;
        $this->ready = false;
        $this->message[] = ['beginTransaction' => null];
        return $this;
    }

    public function __call($method, $parameters)
    {
        // If transaction
        if ($this->chains) {
            if ($method != 'commit') {
                $this->message[] = [$method, $parameters];
            } else {
                $this->ready = true;
            }
        }

        // If prepared
        if ($this->prepared && $method == 'excute') {
            $this->message[] = ['execute', $parameters];
            $this->ready = true;
        }

        if ($this->ready) {
            $task_id = Di::get('pool_map')[$this->database]->getFreeResource();
            $response = Di::get('server')->getServ()->taskwait($this->message, 0.5, $task_id);
            Di::get('pool_map')[$this->database]->freeResource($task_id);
            return $response;
        }

        return $this;
    }
}