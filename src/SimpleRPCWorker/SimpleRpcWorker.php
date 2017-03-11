<?php

namespace Tg\SimpleRPC\SimpleRPCWorker;

use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use React\SocketClient\TcpConnector;
use React\Stream\Stream;
use Tg\SimpleRPC\SimpleRPCMessage\Codec\CodecInterface;
use Tg\SimpleRPC\SimpleRPCMessage\Codec\V1\RPCCodecV1;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCRequest;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCWorkerConfiguration;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCWorkerConfigurationRequest;
use Tg\SimpleRPC\SimpleRPCMessage\Message\V1\MessageCreatorV1;
use Tg\SimpleRPC\SimpleRPCMessage\Message\V1\MessageExtractorV1;
use Tg\SimpleRPC\SimpleRPCMessage\MessageHandler\MessageHandler;
use Tg\SimpleRPC\SimpleRPCMessage\MessageIdGenerator;

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

    /** @var MessageHandler */
    private $messageHandler;

    /** @var MessageIdGenerator */
    private $idGenerator;

    public function __construct($serverIp, LoopInterface $loop = null)
    {
        $this->serverIp = $serverIp;
        $this->loop = $loop ? $loop : \React\EventLoop\Factory::create();
        $this->messageHandler = new MessageHandler(
            [new RPCCodecV1()],
            [new MessageExtractorV1()],
            [new MessageCreatorV1()]
        );
        $this->idGenerator = new MessageIdGenerator();
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

            $client = new \Tg\SimpleRPC\SimpleRPCServer\RpcClient(0, $stream, $this->messageHandler, $this->messageHandler->getDefaultCodec());

            $client->send(
                new MessageRPCWorkerConfigurationRequest(
                    $this->idGenerator->getNewMessageId(),
                    new MessageRPCWorkerConfiguration("foo", 100, ["lala"], '')
                )
            );


            $stream->on('error', function() {
                die('Error');
            });

            $stream->on('data', function ($data) use ($stream, $client) {
                $client->getBuffer()->pushBytes($data);

                foreach ($client->resolveMessages() as $msg) {
                    
                    foreach ($this->workHandlers as $workHandler) {
                        
                        if (!$workHandler->supports($msg->getMsg())) {
                            continue 2;
                        }

                        if (!$msg instanceof MessageRPCRequest) {
                            echo "interne Nachricht vom typ ". get_class($msg)."\n";
                            continue;
                        }

                        $response = $workHandler->onWork($msg);

                        $client->send($response);
                    }

                    throw new \RuntimeException("Could not handel Message.");
                }
            });

        });

        $this->loop->run();
    }

}