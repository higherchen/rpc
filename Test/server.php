<?php

require_once __DIR__.'/../Thrift/ClassLoader/ThriftClassLoader.php';
use Thrift\ClassLoader\ThriftClassLoader;
use Thrift\Server\TServerSocket;

$thrift_dirname = dirname(__DIR__);

/* no composer version */
$loader = new ThriftClassLoader();
$loader->registerNamespace('Thrift', $thrift_dirname);
$loader->registerNamespace('Swoole', $thrift_dirname);
$loader->registerNamespace('Services', __DIR__);
$loader->registerDefinition('Services',  __DIR__);
$loader->register();

$service = new Services\HelloSwoole\Handler();
$processor = new Services\HelloSwoole\HelloSwooleProcessor($service);
$socket_tranport = new TServerSocket('0.0.0.0', 8091);
$out_factory = $in_factory = new Thrift\Factory\TFramedTransportFactory();
$out_protocol = $in_protocol = new Thrift\Factory\TBinaryProtocolFactory();

$server = new Swoole\Thrift\Server($processor, $socket_tranport, $in_factory, $out_factory, $in_protocol, $out_protocol);
$server->configure(['processor' => '\\Services\\HelloSwoole\\HelloSwooleProcessor', 'handler' => '\\Services\\HelloSwoole\\Handler'])->serve();
