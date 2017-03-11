<?php

namespace Tg\SimpleRPC\SimpleRPCMessage;


class MessageIdGenerator
{
    private $id = 0;

    public function getNewMessageId() {
        return ++$this->id;
    }
}