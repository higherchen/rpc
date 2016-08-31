<?php

namespace Services\HelloSwoole;

use Swoole\MySQL\Client;

class Handler implements HelloSwooleIf
{
    public function sendMessage(\Services\HelloSwoole\Message $msg)
    {
        return ($msg->send_uid > 100) ? RetCode::SUCCESS : RetCode::ACCESS_DENY;
    }
}
