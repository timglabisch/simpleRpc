<?php

use Tg\SimpleRPC\ReceivedRpcMessage;
use Tg\SimpleRPC\SimpleRPCWorker\MethodRpcWorkHandler;
use Tg\SimpleRPC\SimpleRPCWorker\RpcWorkHandlerInterface;
use Tg\SimpleRPC\SimpleRPCWorker\SimpleRpcWorker;
use Tg\SimpleRPC\SimpleRPCWorker\WorkerReply;
use Tg\SimpleRPC\SimpleRPCWorker\WorkerReplyInterface;

require __DIR__ . '/vendor/autoload.php';


$loop = React\EventLoop\Factory::create();


(new SimpleRpcWorker($loop, $_SERVER['RPC_SERVER']))->run((new MethodRpcWorkHandler)

    ->on('methodA', function(ReceivedRpcMessage $message) {
        echo "do some methodA\n";
        return new WorkerReply($message->getBuffer().' reply');
    })

    ->on('methodB', function(ReceivedRpcMessage $message) {
        echo "do some methodB\n";
        return new WorkerReply($message->getBuffer().' reply');
    })

    ->on('methodC', function(ReceivedRpcMessage $message) {
        echo "do some methodC\n";
        return new WorkerReply($message->getBuffer().' reply');
    })
);

$loop->run();