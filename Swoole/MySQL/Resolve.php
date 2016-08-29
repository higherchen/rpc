<?php

namespace Swoole\MySQL;

class Resolve
{
    protected $conn;
    protected $trans;

    protected static $stmts = [];

    public function __construct($conn, $data) 
    {
        $this->conn = $conn;
        $this->trans = $data['trans'];
        $this->query = $data['query'];
    }

    public function getStatement($sql)
    {
        $mark = md5($sql);
        if (!isset(static::$stmts[$mark])) {
            static::$stmts[$mark] = $this->conn->prepare($sql);
        }

        return static::$stmts[$mark];
    }

    public function run()
    {
        if (!$this->trans) {
            // 非事务型
            list($method, $sql, $options) = reset($this->query);
            $stmt = $this->getStatement($sql);

            switch ($method) {
            case 'query':
                $stmt->execute($options);
                return $stmt->fetchAll();
            
            case 'queryRow':
                $stmt->execute($options);
                return $stmt->fetch(\PDO::FETCH_ASSOC);

            case 'execute':
                $stmt->execute($options);
                return $stmt->rowCount();
            
            default:
                return false;
            }
        } else {
            // 事务型
            $this->conn->beginTransaction();
            try {
                foreach ($this->query as $query) {
                    list($method, $sql, $options) = $query;
                    $this->conn->exec($sql);
                }
            } catch (\Exception $e) {
                $this->conn->rollBack();
                return false;
            }
            return true;
        }
    }
}