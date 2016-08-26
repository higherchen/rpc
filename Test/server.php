<?php

require_once __DIR__.'/../Thrift/ClassLoader/ThriftClassLoader.php';
use Thrift\ClassLoader\ThriftClassLoader;
use Thrift\Server\TServerSocket;
use Swoole\Core\App;

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

$config = [
    // rpc config
    'host' => '0.0.0.0',
    'port' => 8091,
    'processor' => '\\Services\\HelloSwoole\\HelloSwooleProcessor',
    'handler' => '\\Services\\HelloSwoole\\Handler',

    // swoole config
    'worker_num' => 1,
    'dispatch_mode' => 1,               // 1: 轮循, 3: 争抢
    'open_length_check' => true,        // 打开包长检测
    'package_max_length' => 8192000,    // 最大的请求包长度,8M
    'package_length_type' => 'N',       // 长度的类型，参见PHP的pack函数
    'package_length_offset' => 0,       // 第N个字节是包长度的值
    'package_body_offset' => 4,         // 从第几个字节计算长度
];

$app = new App($config);
$app->initRPC($processor);
$app->run();
