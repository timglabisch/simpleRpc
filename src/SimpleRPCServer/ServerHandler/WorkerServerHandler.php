<?php

namespace Tg\SimpleRPC\SimpleRPCServer\ServerHandler;


use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tg\SimpleRPC\ReceivedRpcMessage;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCWorkerConfiguration;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCWorkerConfigurationRequest;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCWorkerConfigurationResponse;
use Tg\SimpleRPC\SimpleRPCServer\Event\SupportedServicesChangedEvent;
use Tg\SimpleRPC\SimpleRPCServer\RpcClient;
use Tg\SimpleRPC\SimpleRPCServer\RpcServerHandlerInterface;
use Tg\SimpleRPC\SimpleRPCServer\ServerHandler\Worker\WorkerClientConfiguration;
use Tg\SimpleRPC\SimpleRPCServer\WorkQueue;

class WorkerServerHandler extends AbstractServerHandler
{
    /** @var WorkQueue */
    private $workQueue;

    /** @var ReceivedRpcMessage[] */
    private $clientWork = [];

    private $idlClients = [];

    /** @var WorkerClientConfiguration[] */
    private $workerClientConfigurations = [];

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        WorkQueue $workQueue,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->workQueue = $workQueue;
        $this->eventDispatcher = $eventDispatcher;

        $this->workQueue->on(function () {
            foreach ($this->idlClients as $idlClient) {
                if ($this->workQueue->isEmpty()) {
                    break;
                }

                $this->tryToEnqueWork($idlClient);
            }
        });
    }

    public function onError(RpcClient $client, \Exception $exception)
    {
        $this->onClose($client);
    }

    public function onClose(RpcClient $client)
    {
        if (isset($this->idlClients[$client->getId()])) {
            unset($this->idlClients[$client->getId()]);
        }

        unset($this->workerClientConfigurations[$client->getId()]);

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

    public function tryToEnqueWork(RpcClient $client)
    {
        if (!$this->workerClientConfigurations[$client->getId()]->getActive()) {
            return;
        }

        if ($this->workQueue->isEmpty()) {
            $this->idlClients[$client->getId()] = $client;
            return;
        }

        /** @var $senderAndMessage ReceivedRpcMessage */
        $senderAndMessage = $this->workQueue->dequeue();
        unset($this->idlClients[$client->getId()]);

        $this->clientWork[$client->getId()] = $senderAndMessage;

        $client->send($senderAndMessage->getMsg());
    }

    public function onConnection(RpcClient $client)
    {
        $this->workerClientConfigurations[$client->getId()] = new WorkerClientConfiguration();
        $this->tryToEnqueWork($client);
    }

    private function handleClientConfigurationUpdate(
        RpcClient $client,
        MessageRPCWorkerConfigurationRequest $configurationRequest
    ) {
        $workerClientConfiguration = $this->workerClientConfigurations[$client->getId()];

        $configuration = $configurationRequest->getConfiguration();

        $workerClientConfiguration->setActive($configuration->getActive());
        $workerClientConfiguration->setServices($configuration->getServices());
        $workerClientConfiguration->setMaxTasks($configuration->getMaxTasks());

        $client->send(
            new MessageRPCWorkerConfigurationResponse(
                $configurationRequest->getId(),
                new MessageRPCWorkerConfiguration(
                    $workerClientConfiguration->getActive(),
                    $workerClientConfiguration->getMaxTasks(),
                    $workerClientConfiguration->getServices(),
                    ''
                )
            )
        );

        $services = [];
        foreach ($this->workerClientConfigurations as $configuration) {
            if (!$configuration->getActive()) {
                continue;
            }

            foreach ($configuration->getServices() as $configurationService) {
                $services[] = $configurationService;
            }
        }

        $this->eventDispatcher->dispatch(
            SupportedServicesChangedEvent::class,
            new SupportedServicesChangedEvent(array_values(array_unique($services)))
        );
    }


    public function onMessage(RpcClient $client, ReceivedRpcMessage $message)
    {
        if (isset($this->clientWork[$client->getId()])) {
            $this->clientWork[$client->getId()]->getSender()->send($message->getMsg());
            unset($this->clientWork[$client->getId()]);
        } elseif ($message->getMsg() instanceof MessageRPCWorkerConfigurationRequest) {
            $this->handleClientConfigurationUpdate($client, $message->getMsg());
        }

        $this->tryToEnqueWork($client);
    }

}