<?php

use React\Socket\ConnectionInterface;
use Tg\SimpleRPC\SimpleRPCServer\SimpleRcpWorkerServer;
use Tutorial\Person;

require __DIR__ . '/vendor/autoload.php';
@require __DIR__ . '/foo/example.pb.php';



$loop = React\EventLoop\Factory::create();


$simpleRpcServer = new SimpleRcpWorkerServer();
$simpleRpcServer->run(1337, $loop);

$loop->run();
