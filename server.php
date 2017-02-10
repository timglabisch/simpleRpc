<?php

use React\Socket\ConnectionInterface;
use Tg\SimpleRPC\SimpleRPCServer\ServerHandler\ClientServerHandler;
use Tg\SimpleRPC\SimpleRPCServer\ServerHandler\LogableServerHandler;
use Tg\SimpleRPC\SimpleRPCServer\ServerHandler\PrometheusServerHandler;
use Tg\SimpleRPC\SimpleRPCServer\ServerHandler\WorkerServerHandler;
use Tg\SimpleRPC\SimpleRPCServer\SimpleRpcServerHandler;
use Tg\SimpleRPC\SimpleRPCServer\WorkQueue;
use Tutorial\Person;

require __DIR__ . '/vendor/autoload.php';

$client = new \Prometheus\Client(['base_uri' => '-']);

$loop = React\EventLoop\Factory::create();

$queue = new WorkQueue();

(new SimpleRpcServerHandler(
    $workerMetricServerHandler = new PrometheusServerHandler(
        new LogableServerHandler('worker', new WorkerServerHandler($queue)),
        $client,
        'worker',
        $queue
    )
))
    ->run(1337, $loop)
;

(new SimpleRpcServerHandler(
    $clientMetricServerHandler = new PrometheusServerHandler(
        new LogableServerHandler('client', new ClientServerHandler($queue)),
        $client,
        'client',
        $queue
    )
))
    ->run(1338, $loop)
;


$socket = new React\Socket\Server($loop);
$http = new React\Http\Server($socket);
/** @var $memoryGauge \Prometheus\Gauge */
$memoryGauge = $client->newGauge(['namespace' => 'php', 'name' => 'Memory', 'help' => 'Memory...']);
$cpuGauge = $client->newGauge(['namespace' => 'php', 'name' => 'CPU', 'help' => 'CPU...']);
$http->on('request', function ($request, $response) use (
    $client,
    $workerMetricServerHandler,
    $clientMetricServerHandler,
    $memoryGauge,
    $cpuGauge
) {
    $memoryGauge->set([], memory_get_usage(true));
    $memoryGauge->set([], sys_getloadavg()[0]);
    $workerMetricServerHandler->prepareMetrics();
    $clientMetricServerHandler->prepareMetrics();

    $response->writeHead(200, array('Content-Type' => 'text/plain'));
    $response->end($client->serialize());
});

$socket->listen(3333);

$loop->run();
