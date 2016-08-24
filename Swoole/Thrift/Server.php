<?php

namespace Swoole\Thrift;

use Thrift;
use Thrift\Server\TSimpleServer;

class Server extends TSimpleServer
{
    protected $processor = null;
    protected $serv = null;
    protected $name = null;

    protected $service = ['processor' => '', 'handler' => '', 'host' => '127.0.0.1', 'port' => 8091];
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
        $prefix = $this->name ? $this->name.': ' : '';
        swoole_set_process_name($prefix.'master process');
    }

    public function onManagerStart()
    {
        $prefix = $this->name ? $this->name.': ' : '';
        swoole_set_process_name($prefix.'manager process');
    }

    public function onWorkerStart()
    {
        $prefix = $this->name ? $this->name.': ' : '';
        swoole_set_process_name($prefix.'worker process');
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
        if (isset($config['name'])) {
            $this->name = $config['name'];
            unset($config['name']);
        }
        foreach ($config as $key => $value) {
            if (isset($this->service[$key]) && $value) {
                $this->service[$key] = $value;
            } elseif ($value) {
                $this->swoole_config[$key] = $value;
            }
        }

        $this->serv = new \swoole_server($this->service['host'], $this->service['port']);

        return $this;
    }

    public function onReceive($serv, $fd, $from_id, $data)
    {
        $processor_class = $this->service['processor'];
        $handler_class = $this->service['handler'];

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
        $this->serv->on('Start', [$this, 'onStart']);
        $this->serv->on('ManagerStart', [$this, 'onManagerStart']);
        $this->serv->on('WorkerStart', [$this, 'onWorkerStart']);
        $this->serv->on('receive', [$this, 'onReceive']);
        $this->serv->set($this->swoole_config);
        $this->serv->start();
    }
}
