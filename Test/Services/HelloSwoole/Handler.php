<?php

namespace Services\HelloSwoole;

use Swoole\MySQL\Client;

class Handler implements HelloSwooleIf
{
    public function sendMessage(\Services\HelloSwoole\Message $msg)
    {
        $config = include __DIR__.'/../../Conf/database.php';
        $client = new Client($config['default'], 'default');
        $response = $client->query("SELECT * FROM user WHERE id = ?", [1]);
        var_dump($response);

        return ($msg->send_uid > 100) ? RetCode::SUCCESS : RetCode::ACCESS_DENY;
    }
}
