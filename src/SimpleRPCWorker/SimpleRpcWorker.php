<?php

namespace Tg\SimpleRPC\SimpleRPCWorker;

use React\EventLoop\LoopInterface;
use React\SocketClient\TcpConnector;
use React\Stream\Stream;
use Tg\SimpleRPC\ReceivedRpcMessage;
use Tg\SimpleRPC\RpcMessage;
use Tg\SimpleRPC\SimpleRPCMessage\Generated\RpcClientHeaderResponse;

class SimpleRpcWorker
{
    /** @var LoopInterface */
    private $loop;

    /** @var string */
    private $serverIp;

    private $workHandler;

    public function __construct(LoopInterface $loop, $serverIp)
    {
        $this->loop = $loop;
        $this->serverIp = $serverIp;
    }

    public function run(RpcWorkHandlerInterface $workHandler)
    {
        (new TcpConnector($this->loop))->connect($this->serverIp)->then(function (Stream $stream) use ($workHandler) {

            $client = new \Tg\SimpleRPC\SimpleRPCServer\RpcClient(0, $stream);

            $stream->on('error', function() {
                die('Error');
            });

            $stream->on('data', function ($data) use ($stream, $client, &$i, $workHandler) {
                $client->pushBytes($data);

                $msgs = ReceivedRpcMessage::fromData($client);

                if ($msgs == ReceivedRpcMessage::STATE_NEEDS_MORE_BYTES) {
                    return;
                }

                if (!is_array($msgs)) {
                    $stream->end();
                }

                foreach ($msgs as $msg) {
                    $response = $workHandler->onWork($msg);

                    $stream->write((new RpcMessage($response->toBytes(), new RpcClientHeaderResponse(), 1337, 1, $msg->getId()))->encode());
                }
            });

        });
    }

}