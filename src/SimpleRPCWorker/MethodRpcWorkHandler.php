<?php

namespace Tg\SimpleRPC\SimpleRPCWorker;


use Tg\SimpleRPC\ReceivedRpcMessage;

class MethodRpcWorkHandler implements RpcWorkHandlerInterface
{
    /** @var string[] */
    private $methodMap = [];

    public function onWork(ReceivedRpcMessage $message): WorkerReplyInterface
    {
        if (!isset($this->methodMap[$message->getHeader()->getMethod()])) {
            throw new \RuntimeException(sprintf("Method %s not found", $message->getHeader()->getMethod()));
        }

        $cb = $this->methodMap[$message->getHeader()->getMethod()];
        return $cb($message);
    }

    /**
     * @param string $method
     * @param callable $callable
     * @return MethodRpcWorkHandler
     */
    public function on(string $method, callable $callable)
    {
        $this->methodMap[$method] = $callable;
        return $this;
    }

    /** @return string[] */
    public function getSupportedMethosNames()
    {
        return array_keys($this->methodMap);
    }
}