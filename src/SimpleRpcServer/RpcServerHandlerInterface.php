<?php

namespace Tg\SimpleRPC\SimpleRPCServer;

use Tg\SimpleRPC\ReceivedRpcMessage;

interface RpcServerHandlerInterface
{
    public function onConnection(RpcClient $client);

    public function onMessage(RpcClient $client, ReceivedRpcMessage $message);

    public function onError(RpcClient $client, \Exception $exception);

    public function onClose(RpcClient $client);

}