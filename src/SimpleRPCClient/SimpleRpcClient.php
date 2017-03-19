<?php

namespace Tg\SimpleRPC\SimpleRPCClient;

use React\EventLoop\LoopInterface;
use Tg\SimpleRPC\SimpleRPCClient\ServiceDiscovery\ServiceDiscoveryInterface;
use Tg\SimpleRPC\SimpleRPCMessage\Codec\V1\RPCCodecV1;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCRequest;
use Tg\SimpleRPC\SimpleRPCMessage\Codec\V1\MessageCreatorV1;
use Tg\SimpleRPC\SimpleRPCMessage\Codec\V1\MessageExtractorV1;
use Tg\SimpleRPC\SimpleRPCMessage\MessageHandler\MessageHandler;
use Tg\SimpleRPC\SimpleRPCMessage\MessageIdGenerator;

class SimpleRpcClient
{

    /** @var LoopInterface */
    private $loop;

    /** @var ServiceDiscoveryInterface */
    private $serviceDiscovery;

    /** @var ClientConnection[] */
    private $connections = [];

    private $messageHandler;

    /** @var MessageIdGenerator */
    private $idGenerator;

    /** @param ClientConnection[] $connections */
    public function __construct(
        LoopInterface $loop,
        ServiceDiscoveryInterface $serviceDiscovery
    )
    {
        $this->loop = $loop;
        $this->serviceDiscovery = $serviceDiscovery;
        $this->messageHandler = new MessageHandler(
            [new RPCCodecV1()]
        );
        $this->idGenerator = new MessageIdGenerator();
    }

    private function getClientConnection(MessageHandler $messageHandler): ClientConnection
    {
        $connectionString = $this->serviceDiscovery->getConnectionString();

        if (!isset($this->connections[$connectionString])) {
            $this->connections[$connectionString] = new ClientConnection(
                $this->loop,
                $connectionString,
                $messageHandler
            );
        }

        return $this->connections[$connectionString];
    }

    /** @return \React\Promise\PromiseInterface */
    public function send(string $method, $body)
    {
        return $this->getClientConnection($this->messageHandler)->send(new MessageRPCRequest(
            $this->idGenerator->getNewMessageId(),
            $method,
            $body
        ));
    }

}