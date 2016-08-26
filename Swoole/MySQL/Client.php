<?php

namespace Swoole\MySQL;

/**
 * PDO连接类 维护prepare statement.
 * Task单进程内连接
 *
 * @author higher
 */
class Client
{
    protected static $_conn;            // 数据库连接
    protected static $_stmt = [];       // prepare statement

    public static function getConnection($config)
    {
        if (static::$_conn === null) {
            static::$_conn = new \PDO($config['dsn'], $config['username'], $config['password'], $config['options']);
        }

        return static::$_conn;
    }

    public function __call($method, $parameters)
    {
        
    }
}