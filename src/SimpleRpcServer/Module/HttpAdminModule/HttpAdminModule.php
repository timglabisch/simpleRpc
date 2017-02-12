<?php

namespace Tg\SimpleRPC\SimpleRPCServer\Module\HttpAdminModule;

use Prometheus\Client;
use React\EventLoop\LoopInterface;
use Tg\SimpleRPC\SimpleRPCServer\Module\ModuleInterface;
use Tg\SimpleRPC\SimpleRPCServer\RpcServerHandlerInterface;
use Tg\SimpleRPC\SimpleRPCServer\ServerHandler\ClientServerHandler;
use Tg\SimpleRPC\SimpleRPCServer\ServerHandler\WorkerServerHandler;

class HttpAdminModule implements ModuleInterface
{
    /** @var LoopInterface */
    private $loop;

    /** @var WorkerServerHandler */
    private $workerServerHandler;

    /** @var ClientServerHandler */
    private $clientServerHandler;

    /** @var Client */
    private $prometheusClient;

    public function __construct(
        LoopInterface $loop,
        RpcServerHandlerInterface $workerServerHandler,
        RpcServerHandlerInterface $clientServerHandler,
        Client $prometheusClient
    ) {
        $this->loop = $loop;
        $this->workerServerHandler = $workerServerHandler;
        $this->clientServerHandler = $clientServerHandler;
        $this->prometheusClient = $prometheusClient;
    }


    public function run(array $arguments)
    {
        $socket = new \React\Socket\Server($this->loop);
        $http = new \React\Http\Server($socket);

        /** @var $memoryGauge \Prometheus\Gauge */
        $memoryGauge = $this->prometheusClient->newGauge(['namespace' => 'php', 'name' => 'Memory', 'help' => 'Memory...']);

        /** @var $cpuGauge \Prometheus\Gauge */
        $cpuGauge = $this->prometheusClient->newGauge(['namespace' => 'php', 'name' => 'CPU', 'help' => 'CPU...']);

        $http->on('request', function ($request, $response) use ($memoryGauge, $cpuGauge) {
            $memoryGauge->set([], memory_get_usage(true));
            $memoryGauge->set([], sys_getloadavg()[0]);
            $this->workerServerHandler->prepareMetrics();
            $this->clientServerHandler->prepareMetrics();

            $response->writeHead(200, array('Content-Type' => 'text/plain'));
            $response->end($this->prometheusClient->serialize());
        });

        $socket->listen($arguments['port-admin']);
    }

}