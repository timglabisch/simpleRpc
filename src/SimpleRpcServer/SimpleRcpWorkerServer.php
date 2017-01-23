<?php

namespace Tg\SimpleRPC\SimpleRPCServer;

use Exception;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;

class SimpleRcpWorkerServer
{
    /** @var RpcClient */
    private $workerClients = [];

    private $clientIncrement;

    function log(RpcClient $client, $message) {
        echo "[worker][".$client."] ". $message."\n";
    }

    public function run($port, LoopInterface $loop) {

        $socket = new \React\Socket\Server($loop);
        $socket->on('connection', function (ConnectionInterface $worker) {

            $client =  new RpcClient(++$this->clientIncrement, $worker);

            $this->workerClients[] = $client;

            $this->log($client, "hello");

            $worker->on('data', function ($data) use ($client) {
                //$this->log($client, $data);

                $client->pushBytes($data);

                $messages = $client->resolveMessages();

                if (is_string($messages)) {
                    $this->log($client, $messages);
                } elseif (is_array($messages)) {
                    foreach ($messages as $message) {
                        $this->log($client, "got message: ".$message->getBuffer());
                    }
                } else {
                    $this->log($client, "invalid message");
                }
            });

            $worker->on('close', function() use ($client) {
                $this->log($client, "bye");
                unset($this->workerClients[$client->getId()]);
            });

            $worker->on('error', function (Exception $e) {
                echo 'error: ' . $e->getMessage() . PHP_EOL;
            });

        });

        $socket->listen($port);
    }

}