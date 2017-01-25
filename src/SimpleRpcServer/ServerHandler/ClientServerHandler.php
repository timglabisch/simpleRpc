<?php

namespace Tg\SimpleRPC\SimpleRPCServer\ServerHandler;


use Tg\SimpleRPC\ReceivedRpcMessage;
use Tg\SimpleRPC\SimpleRPCServer\RpcClient;
use Tg\SimpleRPC\SimpleRPCServer\RpcServerHandlerInterface;

class ClientServerHandler extends AbstractWorkerServerHandler implements RpcServerHandlerInterface
{
    /** @var \SplQueue */
    private $work;

    public function __construct(
        \SplQueue $work
    ) {
        $this->work = $work;
    }

    public function onMessage(RpcClient $client, ReceivedRpcMessage $message)
    {
        $this->work->push($message);
    }

}