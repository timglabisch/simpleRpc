<?php

namespace Tg\SimpleRPC\SimpleRPCWorker;


use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCRequest;

interface RpcWorkHandlerInterface
{
    public function onWork(MessageRPCRequest $message): WorkerReplyInterface;

    public function supports(MessageRPCRequest $message): bool;

    public function getSupportedServices(): array;
}