<?php

namespace Tg\SimpleRPC\SimpleRPCWorker;


use Tg\SimpleRPC\ReceivedRpcMessage;

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


    public function onWork(ReceivedRpcMessage $message): WorkerReplyInterface
    {
        $method = strtolower($message->getHeader()->getMethod());

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
        $this->methodMap[$this->serviceName.$method] = $callable;
        return $this;
    }

    public function supports(ReceivedRpcMessage $message): bool
    {
        $method = strtolower($message->getHeader()->getMethod());

        return isset($this->methodMap[$method]);
    }

    /** @return string[] */
    public function getSupportedMethosNames()
    {
        return array_keys($this->methodMap);
    }
}