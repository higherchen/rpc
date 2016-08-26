<?php

namespace Swoole\Core;

class App
{
    public function __construct($config)
    {
        Di::set('config', $config);
    }

    public function initRPC($processor)
    {
        $socket_tranport = new \Thrift\Server\TServerSocket(Di::get('host'), Di::get('port'));
        $out_factory = $in_factory = new \Thrift\Factory\TFramedTransportFactory();
        $out_protocol = $in_protocol = new \Thrift\Factory\TBinaryProtocolFactory();

        $server = new \Swoole\Thrift\Server($processor, $socket_tranport, $in_factory, $out_factory, $in_protocol, $out_protocol);
        Di::set('receive', [$server, 'onReceive']);
    }

    public function initMySQL()
    {
    }

    public function run()
    {
        $server = new Server();
        $server->serve();
    }
}