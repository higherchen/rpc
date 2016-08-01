<?php

namespace Services\HelloSwoole;

class Handler implements HelloSwooleIf
{
    public function sendMessage(\Services\HelloSwoole\Message $msg)
    {
        var_dump($msg->send_uid);

        return ($msg->send_uid > 100) ? RetCode::SUCCESS : RetCode::ACCESS_DENY;
    }
}
