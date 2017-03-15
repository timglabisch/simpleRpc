<?php

use Tg\SimpleRPC\ReceivedRpcMessage;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCRequest;
use Tg\SimpleRPC\SimpleRPCWorker\MethodRpcWorkHandler;
use Tg\SimpleRPC\SimpleRPCWorker\SimpleRpcWorker;
use Tg\SimpleRPC\SimpleRPCWorker\WorkerReply;

require __DIR__ . '/vendor/autoload.php';


$worker = (new MethodRpcWorkHandler('card'))

    ->on('methodA', function(MessageRPCRequest $message) {
        echo "do some methodA\n";
        return new WorkerReply($message->getBody().' reply');
    })

    ->on('methodB', function(MessageRPCRequest $message) {
        echo "do some methodB\n";
        return new WorkerReply($message->getBody().' reply');
    })

    ->on('methodC', function(MessageRPCRequest $message) {
        echo "do some methodC\n";
        return new WorkerReply($message->getBody().' reply');
    })
;


(new SimpleRpcWorker($_SERVER['RPC_SERVER']))
    ->register($worker)
    ->run()
;
