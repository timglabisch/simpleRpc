<?php

namespace Tg\SimpleRPC\SimpleRPCServer;

use Exception;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use Tg\SimpleRPC\ReceivedRpcMessage;
use Tg\SimpleRPC\SimpleRPCMessage\Codec\V1\RPCCodecV1;
use Tg\SimpleRPC\SimpleRPCMessage\Codec\V1\MessageCreatorV1;
use Tg\SimpleRPC\SimpleRPCMessage\Codec\V1\MessageExtractorV1;
use Tg\SimpleRPC\SimpleRPCMessage\MessageHandler\MessageHandler;

class SimpleRpcServerHandler
{
    /** @var RpcClient */
    private $workerClients = [];

    private $clientIncrement;

    /** @var RpcServerHandlerInterface */
    private $serverHandler;

    /** @var MessageHandler */
    private $messageHandler;

    public function __construct(
        RpcServerHandlerInterface $serverHandler
    ) {
        $this->serverHandler = $serverHandler;
        $this->messageHandler = new MessageHandler(
            [new RPCCodecV1()]
        );
    }
    
    public function run($port, LoopInterface $loop) {

        $socket = new \React\Socket\Server($loop);
        $socket->on('connection', function (ConnectionInterface $worker) {

            $client =  new RpcClient(++$this->clientIncrement, $worker, $this->messageHandler);

            $this->serverHandler->onConnection($client);

            $this->workerClients[] = $client;

            $worker->on('data', function ($data) use ($client) {

                $client->getBuffer()->pushBytes($data);

                foreach ($client->resolveMessages() as $message) {
                    $this->serverHandler->onMessage($client, $message);
                }

            });

            $worker->on('close', function() use ($client) {
                $this->serverHandler->onClose($client);
                unset($this->workerClients[$client->getId()]);
            });

            $worker->on('error', function (Exception $e) use ($client) {
                $this->serverHandler->onError($client, $e);
            });

        });

        $socket->listen($port, '0.0.0.0');
    }

}