<?php

use React\Socket\ConnectionInterface;
use Tg\SimpleRPC\SimpleRPCServer\ServerHandler\LogableServerHandler;
use Tg\SimpleRPC\SimpleRPCServer\ServerHandler\WorkerServerHandler;
use Tg\SimpleRPC\SimpleRPCServer\SimpleRpcServerHandler;
use Tutorial\Person;

require __DIR__ . '/vendor/autoload.php';
@require __DIR__ . '/foo/example.pb.php';



$loop = React\EventLoop\Factory::create();


$queue = new SplQueue();

(new SimpleRpcServerHandler(
    new LogableServerHandler(
        'worker',
        new WorkerServerHandler($queue)
    )
))
    ->run(1337, $loop)
;

$loop->run();
