<?php

namespace Tg\SimpleRPC\SimpleRPCWorker;


use Tg\SimpleRPC\SimpleRPCMessage\Generated\RPCWorkerResponse;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCRequest;

class MethodRpcWorkHandler implements RpcWorkHandlerInterface
{
    /** @var string[] */
    private $methodMap = [];

    /** @var string */
    private $serviceName;

    public function __construct($serviceName = '')
    {
        $this->serviceName = $serviceName ? $serviceName.'.' : '';
    }

    public function onWork(MessageRPCRequest $message): WorkerReplyInterface
    {
        $method = strtolower($message->getMethod());

        if (!isset($this->methodMap[$method])) {
            throw new \RuntimeException(sprintf("Method %s not found", $method));
        }

        $cb = $this->methodMap[$method];

        return $cb($message);
    }

    /**
     * @param string $method
     * @param callable $callable
     * @return MethodRpcWorkHandler
     */
    public function on(string $method, callable $callable)
    {
        $this->methodMap[strtolower($this->serviceName.$method)] = $callable;
        return $this;
    }

    public function supports(MessageRPCRequest $message): bool
    {
        $method = strtolower($message->getMethod());

        return isset($this->methodMap[$method]);
    }

    /** @return string[] */
    public function getSupportedServices(): array
    {
        return array_keys($this->methodMap);
    }
}