<?php

namespace Tg\SimpleRPC\SimpleRPCServer\ServerHandler;


use Tg\SimpleRPC\ReceivedRpcMessage;
use Tg\SimpleRPC\SimpleRPCServer\RpcClient;
use Tg\SimpleRPC\SimpleRPCServer\RpcServerHandlerInterface;
use Tg\SimpleRPC\SimpleRPCServer\WorkQueue;

class ClientServerHandler extends AbstractServerHandler implements RpcServerHandlerInterface
{
    /** @var WorkQueue */
    private $work;

    public function __construct(
        WorkQueue $work
    ) {
        $this->work = $work;
    }

    public function onMessage(RpcClient $client, ReceivedRpcMessage $message)
    {
        $this->work->push($message);
    }

}