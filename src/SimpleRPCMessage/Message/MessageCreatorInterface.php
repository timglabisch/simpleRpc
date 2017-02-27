<?php

namespace Tg\SimpleRPC\SimpleRPCMessage\Message;



interface MessageCreatorInterface
{
    public function create($msg);
}