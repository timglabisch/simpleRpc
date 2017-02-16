<?php

namespace Tg\SimpleRPC\SimpleRPCClient;

use React\EventLoop\LoopInterface;
use SensioLabs\Consul\Services\Catalog;
use Tg\SimpleRPC\RpcMessage;
use Tg\SimpleRPC\SimpleRPCClient\ServiceDiscovery\ServiceDiscoveryInterface;

class SimpleRpcClient
{

    /** @var LoopInterface */
    private $loop;

    /** @var ServiceDiscoveryInterface */
    private $serviceDiscovery;

    /** @var ClientConnection[] */
    private $connections = [];

    /** @param ClientConnection[] $connections */
    public function __construct(
        LoopInterface $loop,
        ServiceDiscoveryInterface $serviceDiscovery
    )
    {
        $this->loop = $loop;
        $this->serviceDiscovery = $serviceDiscovery;
    }

    private function getClientConnection(): ClientConnection
    {
        $connectionString = $this->serviceDiscovery->getConnectionString();

        if (!isset($this->connections[$connectionString])) {
            $this->connections[$connectionString] = new ClientConnection(
                $this->loop,
                $connectionString
            );
        }

        return $this->connections[$connectionString];
    }

    /** @return \React\Promise\PromiseInterface */
    public function send(RpcMessage $message)
    {
        return $this->getClientConnection()->send($message);
    }

}