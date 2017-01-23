<?php

use React\Socket\ConnectionInterface;
use Tg\SimpleRPC\RpcMessage;
use Tutorial\Person;

require __DIR__ . '/vendor/autoload.php';
@require __DIR__ . '/foo/example.pb.php';



$loop = React\EventLoop\Factory::create();

$client = stream_socket_client('tcp://127.0.0.1:1337');

$conn = new React\Stream\Stream($client, $loop);
$conn->pipe(new React\Stream\Stream(STDOUT, $loop));
$conn->write((new RpcMessage("Hello World"))->encode());

$conn->on('data', function($data) {
    echo $data. "\n";
});

$loop->run();