<?php

namespace Tg\SimpleRPC\SimpleRPCServer;

use Exception;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;

class SimpleRpcServerHandler
{
    /** @var RpcClient */
    private $workerClients = [];

    private $clientIncrement;

    /** @var RpcServerHandlerInterface */
    private $serverHandler;

    public function __construct(
        RpcServerHandlerInterface $serverHandler
    ) {
        $this->serverHandler = $serverHandler;
    }
    
    public function run($port, LoopInterface $loop) {

        $socket = new \React\Socket\Server($loop);
        $socket->on('connection', function (ConnectionInterface $worker) {

            $client =  new RpcClient(++$this->clientIncrement, $worker);

            $this->workerClients[] = $client;

            $worker->on('data', function ($data) use ($client) {

                $client->pushBytes($data);

                $messages = $client->resolveMessages();

                if (is_array($messages)) {
                    foreach ($messages as $message) {
                        $this->serverHandler->onMessage($client, $message);
                    }
                } else {
                    $a = 0;
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

        $socket->listen($port);
    }

}