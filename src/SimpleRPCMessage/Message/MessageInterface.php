<?php

namespace Tg\SimpleRPC\SimpleRPCMessage\Message;


interface MessageInterface
{
    const TYPE_RPC_REQUEST = 1;

    const TYPE_RPC_RESPONSE = 2;

    const TYPE_REJECT_RPC_REQUEST = 3;

    const TYPE_WORKER_CONFIGURATION_REQUEST = 5;

    const TYPE_WORKER_CONFIGURATION_RESPONSE = 6;

    const TYPE_PING = 7;

    const TYPE_PONG = 8;

}