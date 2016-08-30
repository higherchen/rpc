<?php

namespace Services\HelloSwoole;

use Swoole\MySQL\Client;

class Handler implements HelloSwooleIf
{
    public function sendMessage(\Services\HelloSwoole\Message $msg)
    {
        $client = new Client();
        $response = $client->query("SELECT * FROM user WHERE id = ?", [1]);
        var_dump($response);

        return ($msg->send_uid > 100) ? RetCode::SUCCESS : RetCode::ACCESS_DENY;
    }
}
