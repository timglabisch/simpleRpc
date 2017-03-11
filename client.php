<?php


use Tg\SimpleRPC\ReceivedRpcMessage;
use Tg\SimpleRPC\RpcMessage;
use Tg\SimpleRPC\SimpleRPCClient\ServiceDiscovery\ConsulServiceDiscovery;
use Tg\SimpleRPC\SimpleRPCClient\SimpleRpcClient;
use Tg\SimpleRPC\SimpleRPCMessage\Generated\RpcClientHeaderRequest;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCRequest;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCResponse;

require __DIR__ . '/vendor/autoload.php';


$loop = React\EventLoop\Factory::create();

$rpcClient = new SimpleRpcClient($loop, new ConsulServiceDiscovery(['http://172.20.20.10:8500'], 2));

while(true) {

    foreach (range(0, 600) as $x) {
        $rand = random_int(0, PHP_INT_MAX);

        $methods = ['methodA', 'methodB', 'methodC'];

        $rpcClient->send($methods[array_rand($methods)], 'some_content')->then(function (MessageRPCResponse $response) use ($rand) {
            if ($response->getBody() !== 'Hello World '.$rand.' reply') {
                die('bad id ...');
            }

            echo "got {$response->getBody()} \n";
        });
    }


    $loop->run();
}

