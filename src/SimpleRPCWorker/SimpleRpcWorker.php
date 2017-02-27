<?php

namespace Tg\SimpleRPC\SimpleRPCWorker;

use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
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

    /** @var PromiseInterface */
    private $serverConnection;

    /** @var RpcWorkHandlerInterface[] */
    private $workHandlers = [];

    public function __construct($serverIp, LoopInterface $loop = null)
    {
        $this->serverIp = $serverIp;
        $this->loop = $loop ? $loop : \React\EventLoop\Factory::create();
    }

    /** @return PromiseInterface */
    private function getServerConnection()
    {
        if (!$this->serverConnection) {
            $this->serverConnection = (new TcpConnector($this->loop))->connect($this->serverIp);
        }

        return $this->serverConnection;
    }

    /**
     * @param RpcWorkHandlerInterface $workHandler
     * @return SimpleRpcWorker
     */
    public function register(RpcWorkHandlerInterface $workHandler)
    {
        $this->workHandlers[] = $workHandler;
        return $this;
    }

    public function run()
    {
        $this->getServerConnection()->then(function (Stream $stream) {

            $client = new \Tg\SimpleRPC\SimpleRPCServer\RpcClient(0, $stream);

            $client->send(new RpcMessage())

            // todo, interne nachricht um services zu registrieren.

            $stream->on('error', function() {
                die('Error');
            });

            $stream->on('data', function ($data) use ($stream, $client) {
                $client->pushBytes($data);

                $msgs = ReceivedRpcMessage::fromData($client);

                if ($msgs == ReceivedRpcMessage::STATE_NEEDS_MORE_BYTES) {
                    return;
                }

                if (!is_array($msgs)) {
                    $stream->end();
                    return;
                }

                foreach ((array)$msgs as $msg) {
                    
                    foreach ($this->workHandlers as $workHandler) {
                        
                        if (!$workHandler->supports($msg)) {
                            continue 2;
                        }
                        
                        $response = $workHandler->onWork($msg);
                        $stream->write((new RpcMessage($response->toBytes(), new RpcClientHeaderResponse(), 1337, 1, $msg->getId()))->encode());
                    }

                    throw new \RuntimeException("Could not handel Message.");
                }
            });

        });

        $this->loop->run();
    }

}