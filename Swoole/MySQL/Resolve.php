<?php

namespace Swoole\MySQL;

class Resolve
{
    
    protected $trans;
    protected $max_try = 1;

    protected static $conn = null;
    protected static $stmts = [];

    public function __construct($task_id, $data) 
    {
        $this->trans = $data['trans'];
        $this->query = $data['query'];
        $this->database = $data['database'];
        $this->config = $data['config'];
    }

    public function getConnection() 
    {
        $config = $this->config;
        if (static::$conn == null) {
            static::$conn = new \PDO($config['dsn'], $config['username'], $config['password'], $config['options']);
        }
        return static::$conn;
    } 

    public function getStatement($sql)
    {
        $mark = md5($sql);
        if (!isset(static::$stmts[$mark])) {
            static::$stmts[$mark] = $this->getConnection()->prepare($sql);
        }

        return static::$stmts[$mark];
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
                return false;
            }

            $error = $stmt->errorInfo();
            if ($error[0] == 'HY000' && $this->max_try > 0) {
                static::$conn = null;
                static::$stmts = [];
                $this->max_try--;
                return $this->run();
            }
            
            return ['code' => $error[0], 'msg' => $error[2], 'data' => $result];

        } else {
            // 事务型
            $this->getConnection()->beginTransaction();
            try {
                foreach ($this->query as $query) {
                    list($method, $sql, $options) = $query;
                    $this->getConnection()->exec($sql);
                }
                $this->getConnection()->commit();
            } catch (\Exception $e) {
                $this->getConnection()->rollBack();
                return ['code' => $e->getCode(), 'msg' => $e->getMessage()];
            }
            return true;
        }
    }
}