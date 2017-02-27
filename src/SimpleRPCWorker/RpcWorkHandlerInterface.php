<?php

namespace Tg\SimpleRPC\SimpleRPCWorker;


use Tg\SimpleRPC\ReceivedRpcMessage;

interface RpcWorkHandlerInterface
{
    public function onWork(ReceivedRpcMessage $message): WorkerReplyInterface;

    public function supports(ReceivedRpcMessage $message): bool;
}