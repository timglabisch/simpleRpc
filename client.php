<?php


use Tg\SimpleRPC\ReceivedRpcMessage;
use Tg\SimpleRPC\RpcMessage;
use Tg\SimpleRPC\SimpleRPCClient\ServiceDiscovery\ConsulServiceDiscovery;
use Tg\SimpleRPC\SimpleRPCClient\SimpleRpcClient;
use Tg\SimpleRPC\SimpleRPCMessage\Generated\RpcClientHeaderRequest;

require __DIR__ . '/vendor/autoload.php';


$loop = React\EventLoop\Factory::create();

$rpcClient = new SimpleRpcClient($loop, new ConsulServiceDiscovery(['http://172.20.20.10:8500'], 2));

while(true) {

    foreach (range(0, 600) as $x) {
        $rand = mt_rand(0, PHP_INT_MAX);

        $methods = ['methodA', 'methodB', 'methodC'];
        $header = new RpcClientHeaderRequest();
        $header->setMethod($methods[array_rand($methods)]);

        $rpcClient->send(new RpcMessage('Hello World '.$rand, $header))->then(function (ReceivedRpcMessage $message) use ($rand) {
            if ($message->getBuffer() !== 'Hello World '.$rand.' reply') {
                die('bad id ...');
            }

            echo "got {$message->getBuffer()} \n";
        });
    }


    $loop->run();
}

