<?php

namespace Swoole\Thrift;

use Thrift;
use Thrift\Server\TSimpleServer;

class Server extends TSimpleServer
{
    protected $processor = null;

    protected $service = ['name' => 'Index', 'host' => '127.0.0.1', 'port' => 8091];
    protected $swoole_config = [
        'worker_num' => 1,
        'dispatch_mode' => 1,               // 1: 轮循, 3: 争抢
        'open_length_check' => true,        // 打开包长检测
        'package_max_length' => 8192000,    // 最大的请求包长度,8M
        'package_length_type' => 'N',       // 长度的类型，参见PHP的pack函数
        'package_length_offset' => 0,       // 第N个字节是包长度的值
        'package_body_offset' => 4,         // 从第几个字节计算长度
    ];

    public function onStart()
    {
        echo "Thrift Server Start\n";
    }

    /**
     * Swoole thrift server configure.
     *
     * @param array $config Collection of swoole server config & thrift config
     *
     * @return int The number of routes handled
     */
    public function configure($config)
    {
        foreach ($config as $key => $value) {
            if (isset($this->service[$key]) && $value) {
                $this->service[$key] = $value;
            } elseif (isset($this->swoole_config[$key]) && $value) {
                $this->swoole_config[$key] = $value;
            }
        }

        return $this;
    }

    public function onReceive($serv, $fd, $from_id, $data)
    {
        $name = $this->service['name'];
        $processor_class = '\\Services\\'.$name.'\\'.$name.'Processor';
        $handler_class = '\\Services\\'.$name.'\\Handler';

        $handler = new $handler_class();
        $this->processor = new $processor_class($handler);

        $socket = new Socket();
        $socket->setHandle($fd);
        $socket->buffer = $data;
        $socket->server = $serv;
        $protocol = new Thrift\Protocol\TBinaryProtocol($socket, false, false);

        try {
            $protocol->fname = $name;
            $this->processor->process($protocol, $protocol);
        } catch (\Exception $e) {
        }
    }

    public function serve()
    {
        $serv = new \swoole_server($this->service['host'], $this->service['port']);
        $serv->on('workerStart', [$this, 'onStart']);
        $serv->on('receive', [$this, 'onReceive']);
        $serv->set($this->swoole_config);
        $serv->start();
    }
}
