<?php

namespace Swoole\Core;

class Server
{
    /**
     * The Swoole Server Object.
     *
     * @var \swoole_server
     */
    protected $serv = null;

    /**
     * The Process Name.
     *
     * @var string
     */
    protected $name = null;

    protected $config = ['host' => '127.0.0.1', 'port' => 8091];
    protected $swoole_config = [];

    /**
     * Swoole server configure.
     *
     * @param array $config Collection of swoole server config
     *
     * @return int The number of routes handled
     */
    public function __construct()
    {
        $config = Di::get('config');
        if (isset($config['name'])) {
            $this->name = $config['name'];
            unset($config['name']);
        }
        foreach ($config as $key => $value) {
            if (isset($this->config[$key]) && $value) {
                $this->config[$key] = $value;
            } elseif ($value) {
                $this->swoole_config[$key] = $value;
            }
        }

        return $this;
    }

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

    public function onReceive($serv, $fd, $from_id, $data)
    {

    }

    public function serve()
    {
        $this->serv = new \swoole_server($this->config['host'], $this->config['port']);
        $support_callback = [
            'start' => [$this, 'onStart'],
            'managerStart' => [$this, 'onManagerStart'],
            'workerStart' => [$this, 'onWorkerStart'],
            'receive' => [$this, 'onReceive'],
            'task' => null,
        ];
        foreach ($support_callback as $name => $callback) {
            
            // If has the dependency injection
            
            if (is_callable(Di::get($name))) {
                $callback = Di::get($name);
            }

            if ($callback !== null) {
                $this->serv->on($name, $callback);
            }
        }
        $this->serv->set($this->swoole_config);
        $this->serv->start();
    }
}
