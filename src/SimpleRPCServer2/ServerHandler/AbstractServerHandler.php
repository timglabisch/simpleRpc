<?php

namespace Tg\SimpleRPC\SimpleRPCServer\ServerHandler;

use Tg\SimpleRPC\ReceivedRpcMessage;
use Tg\SimpleRPC\SimpleRPCServer\RpcClient;
use Tg\SimpleRPC\SimpleRPCServer\RpcServerHandlerInterface;

abstract class AbstractServerHandler implements RpcServerHandlerInterface
{

    function onMessage(RpcClient $client, ReceivedRpcMessage $message) {}

    function onConnection(RpcClient $client) { }

    function onError(RpcClient $client, \Exception $exception) { }

    function onClose(RpcClient $client) { }

    public function prepareMetrics() {}
    
}