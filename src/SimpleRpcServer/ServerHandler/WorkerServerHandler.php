<?php

namespace Tg\SimpleRPC\SimpleRPCServer\ServerHandler;


use Tg\SimpleRPC\ReceivedRpcMessage;
use Tg\SimpleRPC\RpcMessage;
use Tg\SimpleRPC\SimpleRPCServer\RpcClient;
use Tg\SimpleRPC\SimpleRPCServer\RpcServerHandlerInterface;
use Tg\SimpleRPC\SimpleRPCServer\WorkQueue;

class WorkerServerHandler extends AbstractWorkerServerHandler implements RpcServerHandlerInterface
{
    /** @var WorkQueue */
    private $workQueue;

    /** @var ReceivedRpcMessage[] */
    private $clientWork = [];

    private $idlClients = [];

    public function __construct(
        WorkQueue $workQueue
    ) {
        $this->workQueue = $workQueue;

        $this->workQueue->on(function () {
            foreach ($this->idlClients as $idlClient) {
                if ($this->workQueue->isEmpty()) {
                    break;
                }

                $this->tryToEnqueWork($idlClient);
            }
        });
    }

    function onError(RpcClient $client, \Exception $exception)
    {
        $this->onClose($client);
    }

    function onClose(RpcClient $client)
    {
        if (isset($this->idlClients[$client->getId()])) {
            unset($this->idlClients[$client->getId()]);
        }

        $this->retryWork($client);
        parent::onClose($client);
    }

    public function retryWork(RpcClient $client)
    {
        if (!isset($this->clientWork[$client->getId()])) {
            return;
        }

        $this->workQueue->push($this->clientWork[$client->getId()]);
        unset($this->clientWork[$client->getId()]);
    }

    function tryToEnqueWork(RpcClient $client)
    {
        if ($this->workQueue->isEmpty()) {
            $this->idlClients[$client->getId()] = $client;
            return;
        }

        /** @var $message ReceivedRpcMessage */
        $message = $this->workQueue->dequeue();
        unset($this->idlClients[$client->getId()]);

        $this->clientWork[$client->getId()] = $message;

        $client->send($message);
    }

    function onConnection(RpcClient $client)
    {
        $this->tryToEnqueWork($client);
    }

    public function onMessage(RpcClient $client, ReceivedRpcMessage $message)
    {
        if (isset($this->clientWork[$client->getId()])) {
            $this->clientWork[$client->getId()]->getSender()->send($message);
            unset($this->clientWork[$client->getId()]);
        }

        $this->tryToEnqueWork($client);
    }

}