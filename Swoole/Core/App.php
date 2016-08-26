<?php

namespace Swoole\Core;

class App
{
    protected $config;
    protected $swoole_config;

    public function __construct($config, $swoole_config)
    {
        Di::set('config', $config);
        Di::set('swoole_config', $swoole_config);
        Di::set(
            'server', function () {
                return new Server();
            }
        );
    }

    public function initRPC($processor)
    {
        $socket_tranport = new \Thrift\Server\TServerSocket(Di::get('config')['host'], Di::get('config')['port']);
        $out_factory = $in_factory = new \Thrift\Factory\TFramedTransportFactory();
        $out_protocol = $in_protocol = new \Thrift\Factory\TBinaryProtocolFactory();

        $server = new \Swoole\Thrift\Server($processor, $socket_tranport, $in_factory, $out_factory, $in_protocol, $out_protocol);
        Di::set('receive', [$server, 'onReceive']);
    }

    public function initMySQL($db_config)
    {
        $proxy = new \Swoole\MySQL\Proxy($db_config);
        Di::set('task', [$proxy, 'onTask']);
    }

    public function run()
    {
        Di::singleton('server')->serve();
    }
}