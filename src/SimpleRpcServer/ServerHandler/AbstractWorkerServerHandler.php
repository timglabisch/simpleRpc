<?php

namespace Tg\SimpleRPC\SimpleRPCServer\ServerHandler;

use Tg\SimpleRPC\ReceivedRpcMessage;
use Tg\SimpleRPC\SimpleRPCServer\RpcClient;

abstract class AbstractWorkerServerHandler
{

    function onMessage(RpcClient $client, ReceivedRpcMessage $message) {}

    function onConnection(RpcClient $client) { }

    function onError(RpcClient $client, \Exception $exception) { }

    function onClose(RpcClient $client) { }

}