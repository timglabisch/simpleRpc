<?php

namespace Tg\SimpleRPC\SimpleRPCServer\ServerHandler;

use Tg\SimpleRPC\ReceivedRpcMessage;
use Tg\SimpleRPC\SimpleRPCServer\RpcClient;
use Tg\SimpleRPC\SimpleRPCServer\RpcServerHandlerInterface;

class LogableServerHandler implements RpcServerHandlerInterface
{
    /** @var string */
    private $clientType;

    /** @var RpcServerHandlerInterface */
    private $decorated;

    public function __construct(
        string $clientType,
        RpcServerHandlerInterface $decorated
    ) {
        $this->clientType = $clientType;
        $this->decorated = $decorated;
    }

    private function log(RpcClient $client, $message) {
        echo "[{$this->clientType}][".$client."] ". $message."\n";
    }

    public function onConnection(RpcClient $client)
    {
        $this->log($client, "Connected");
        $this->decorated->onConnection($client);
    }

    public function onMessage(RpcClient $client, ReceivedRpcMessage $message)
    {
        $this->log($client, "Got Message: ");//. $message->getBuffer());
        $this->decorated->onMessage($client, $message);
    }

    public function onError(RpcClient $client, \Exception $exception)
    {
        $this->log($client, "Got Exception: ". $exception->getMessage());
        $this->decorated->onError($client, $exception);
    }

    public function onClose(RpcClient $client)
    {
        $this->log($client, "Closed Connection.");
        $this->decorated->onClose($client);
    }

    public function prepareMetrics()
    {
        return $this->decorated->prepareMetrics();
    }

}