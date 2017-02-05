<?php

use Tg\SimpleRPC\ReceivedRpcMessage;
use Tg\SimpleRPC\SimpleRPCWorker\RpcWorkHandlerInterface;
use Tg\SimpleRPC\SimpleRPCWorker\SimpleRpcWorker;
use Tg\SimpleRPC\SimpleRPCWorker\WorkerReply;
use Tg\SimpleRPC\SimpleRPCWorker\WorkerReplyInterface;

require __DIR__ . '/vendor/autoload.php';


$loop = React\EventLoop\Factory::create();

(new SimpleRpcWorker($loop, '127.0.0.1:1337'))->run(
    new class implements RpcWorkHandlerInterface {

        public function onWork(ReceivedRpcMessage $message): WorkerReplyInterface
        {
            echo "do some work\n";
            return new WorkerReply($message->getBuffer().' reply');
        }

    }
);

$loop->run();