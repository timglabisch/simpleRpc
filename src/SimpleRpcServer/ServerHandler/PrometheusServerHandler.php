<?php

namespace Tg\SimpleRPC\SimpleRPCServer\ServerHandler;


use Prometheus\Client;
use Prometheus\Counter;
use Prometheus\Gauge;
use Prometheus\Metric;
use Tg\SimpleRPC\ReceivedRpcMessage;
use Tg\SimpleRPC\SimpleRPCServer\RpcClient;
use Tg\SimpleRPC\SimpleRPCServer\RpcServerHandlerInterface;
use Tg\SimpleRPC\SimpleRPCServer\WorkQueue;

class PrometheusServerHandler implements RpcServerHandlerInterface
{
    /** @var RpcServerHandlerInterface */
    private $decorated;

    /** @var Client */
    private $client;

    /** @var Counter */
    private $counterConnections;

    /** @var int */
    private $connections = 0;

    /** @var Counter */
    private $counterOnMessages;

    /** @var int */
    private $onMessages = 0;

    /** @var Counter */
    private $counterOnErrors;

    /** @var int */
    private $onErrors = 0;

    /** @var Counter */
    private $counterOnCloses;

    /** @var Counter */
    private $onCloses = 0;

    /** @var Gauge */
    private $gaugeActiveConnections;

    /** @var int */
    private $activeConnections = 0;

    /** @var Gauge */
    private $gaugePendingWork;

    /** @var WorkQueue */
    private $workQueue;

    /**
     * PrometheusServerHandler constructor.
     * @param RpcServerHandlerInterface $decorated
     * @param Client $client
     */
    public function __construct(RpcServerHandlerInterface $decorated, Client $client, $namespace, WorkQueue $workQueue)
    {
        $this->decorated = $decorated;
        $this->client = $client;
        $this->workQueue = $workQueue;
        $this->counterConnections = $client->newCounter(['namespace' => $namespace, 'name' => 'connection', 'help' => 'connection...']);
        $this->counterOnMessages = $client->newCounter(['namespace' => $namespace, 'name' => 'message', 'help' => 'message...']);
        $this->counterOnErrors = $client->newCounter(['namespace' => $namespace, 'name' => 'error', 'help' => 'error...']);
        $this->counterOnCloses = $client->newCounter(['namespace' => $namespace, 'name' => 'close', 'help' => 'close...']);
        $this->gaugeActiveConnections = $client->newGauge(['namespace' => $namespace, 'name' => 'active_connections', 'help' => 'active connections...']);
        $this->gaugePendingWork = $client->newGauge(['namespace' => $namespace, 'name' => 'pending_work', 'help' => 'pending work...']);
    }

    public function onConnection(RpcClient $client)
    {
        $this->connections++;
        $this->activeConnections++;
        return $this->decorated->onConnection($client);
    }

    public function onMessage(RpcClient $client, ReceivedRpcMessage $message)
    {
        $this->onMessages++;
        return $this->decorated->onMessage($client, $message);
    }

    public function onError(RpcClient $client, \Exception $exception)
    {
        $this->onErrors++;
        $this->activeConnections--;
        return $this->decorated->onError($client, $exception);
    }

    public function onClose(RpcClient $client)
    {
        $this->onCloses++;
        $this->activeConnections--;
        return $this->decorated->onClose($client);
    }

    public function prepareMetrics()
    {
        $this->counterConnections->increment([], $this->connections);
        $this->connections = 0;

        $this->counterOnMessages->increment([], $this->onMessages);
        $this->onMessages = 0;

        $this->counterOnErrors->increment([], $this->onErrors);
        $this->onErrors = 0;

        $this->counterOnCloses->increment([], $this->onCloses);
        $this->onCloses = 0;

        $this->gaugePendingWork->set([], $this->workQueue->count());
        $this->gaugeActiveConnections->set([], $this->activeConnections);
        
        return $this->decorated->prepareMetrics();
    }
}