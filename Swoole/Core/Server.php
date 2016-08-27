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
     * The Swoole Server Config.
     *
     * @var array
     */
    protected $swoole_config = null;

    /**
     * The Process Name.
     *
     * @var string
     */
    protected $name = null;

    public function __construct()
    {
        $config = Di::get('config');
        $this->swoole_config = Di::get('swoole_config');
        $this->serv = new \swoole_server($config['host'], $config['port']);
    }

    public function configure($key, $value)
    {
        $this->swoole_config[$key] = $value;
        return $this;
    }

    public function getServ()
    {
        return $this->serv;
    }

    public function onStart()
    {
        $prefix = $this->name ? $this->name.': ' : '';
        swoole_set_process_name($prefix.'rpc master');
    }

    public function onManagerStart()
    {
        $prefix = $this->name ? $this->name.': ' : '';
        swoole_set_process_name($prefix.'rpc manager');
    }

    public function onWorkerStart()
    {
        $prefix = $this->name ? $this->name.': ' : '';
        swoole_set_process_name($prefix.'rpc worker');
    }

    public function onReceive($serv, $fd, $from_id, $data)
    {

    }

    public function serve()
    {
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
