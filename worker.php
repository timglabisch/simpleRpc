<?php

use Tg\SimpleRPC\ReceivedRpcMessage;
use Tg\SimpleRPC\SimpleRPCWorker\MethodRpcWorkHandler;
use Tg\SimpleRPC\SimpleRPCWorker\SimpleRpcWorker;
use Tg\SimpleRPC\SimpleRPCWorker\WorkerReply;

require __DIR__ . '/vendor/autoload.php';


$worker = (new MethodRpcWorkHandler('card'))

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
;


(new SimpleRpcWorker($_SERVER['RPC_SERVER']))
    ->register($worker)
    ->run()
;
