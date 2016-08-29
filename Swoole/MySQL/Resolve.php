<?php

namespace Swoole\MySQL;

class Resolve
{
    protected $conn;
    protected $data;
    protected $prepared = false;
    protected $stmt = null;

    protected static $stmts = [];

    public function __construct($conn, $data) 
    {
        $this->conn = $conn;
        $this->data = $data;
    }

    public function getStatement($sql, $options = null)
    {
        $mark = md5($sql);
        if (!isset(static::$stmts[$mark])) {
            static::$stmts[$mark] = $this->conn->prepare($sql, $options);
        }

        return static::$stmts[$mark];
    }

    public function run()
    {
        $result = '';
        foreach ($this->data as $command) {
            foreach ($command as $method => $params) {
                if ($method == 'prepare') {
                    $this->stmt = $this->getStatement($params[0], $params[1]);
                    $this->prepared = true;
                    continue;
                }
                if ($this->prepared && method_exists($this->stmt, $method)) {
                    $result = call_user_func_array([$this->stmt, $method], $params);
                    continue;
                }
                if (!$this->prepared && method_exists($this->conn, $method)) {
                    $result = call_user_func_array([$this->conn, $method], $params);
                    continue;
                }
            }
        }

        return $result;
    }
}