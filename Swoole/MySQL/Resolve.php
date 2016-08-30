<?php

namespace Swoole\MySQL;

class Resolve
{
    
    protected $trans;
    protected $error;

    protected static $conn = null;
    protected static $stmts = [];

    public function __construct($task_id, $data) 
    {
        // $this->conn = $conn;
        $this->trans = $data['trans'];
        $this->query = $data['query'];
        $this->database = $data['database'];

        if (static::$conn == null) {
            
        }
    }

    public function getStatement($sql)
    {
        $mark = md5($sql);
        if (!isset(static::$stmts[$mark])) {
            static::$stmts[$mark] = $this->conn->prepare($sql);
        }

        return static::$stmts[$mark];
    }

    public function getError()
    {
        return $this->error;
    }

    public function run()
    {
        if (!$this->trans) {
            // 非事务型
            list($method, $sql, $options) = $this->query;
            $stmt = $this->getStatement($sql);

            switch ($method) {
            case 'query':
                $stmt->execute($options);
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                break;
            
            case 'queryRow':
                $stmt->execute($options);
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                break;

            case 'execute':
                $stmt->execute($options);
                $result = $stmt->rowCount();
                break;
            
            default:
                $result = false;
            }

            $info = $stmt->errorInfo();
            $this->error = ['code' => $info[0], 'info' => $info[2]];
            
            return $result;

        } else {
            // 事务型
            $this->conn->beginTransaction();
            try {
                foreach ($this->query as $query) {
                    list($method, $sql, $options) = $query;
                    $this->conn->exec($sql);
                    $info = $this->conn->errorInfo();
                    $this->error = ['code' => $info[0], 'info' => $info[2]];
                }
            } catch (\Exception $e) {
                $this->conn->rollBack();
                return false;
            }
            return true;
        }
    }
}