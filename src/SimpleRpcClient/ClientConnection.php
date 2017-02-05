<?php

namespace Tg\SimpleRPC\SimpleRPCClient;


use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use React\Promise\PromisorInterface;
use React\SocketClient\TcpConnector;
use React\Stream\Stream;
use Tg\SimpleRPC\ReceivedRpcMessage;
use Tg\SimpleRPC\RpcMessage;
use Tg\SimpleRPC\SimpleRPCServer\RpcClient;

class ClientConnection
{
    /** @var string */
    private $connectionsString;

    /** @var LoopInterface */
    private $loop;

    /** @var Deferred */
    private $clientPromisor;

    /** @var PromisorInterface[] */
    private $messageQueue = [];

    /** @return PromiseInterface */
    private function getClientPromise()
    {
        if (!$this->clientPromisor) {
            $this->clientPromisor = new \React\Promise\Deferred();
            $connection = (new TcpConnector($this->loop))->connect('127.0.0.1:1338');
            $connection->then(function (Stream $stream) {

                $client = new RpcClient(mt_rand(), $stream);
                $this->clientPromisor->resolve($client);
                
                $stream->on(
                    'data',
                    function ($data) use ($stream, $client) {
                        //   echo "on data\n";
                        $client->pushBytes($data);

                        $msgs = ReceivedRpcMessage::fromData($client);

                        if ($msgs == ReceivedRpcMessage::STATE_NEEDS_MORE_BYTES) {
                            // echo "needs more byte\n";

                            return;
                        }

                        if (!is_array($msgs)) {
                            die("got bad message\n");
                          //  $this->client->close();
                        }

                        foreach ($msgs as $msg) {

                            if (!isset($this->messageQueue[$msg->getId()])) {
                                die("wrong message id");
                            //    $this->client->close();
                            }


                            $this->messageQueue[$msg->getId()]->resolve($msg);
                            unset($this->messageQueue[$msg->getId()]);
                        }

                        if (empty($this->messageQueue)) {
                            $this->clientPromisor = null;
                            $client->close();
                        }
                    }
                );

            });
        }

        return $this->clientPromisor->promise();
    }

    public function __construct(LoopInterface $loop, string $connectionsString)
    {
        $this->loop = $loop;
        $this->connectionsString = $connectionsString;
    }

    public function send(RpcMessage $message): PromiseInterface
    {
        if (!isset($this->messageQueue[$message->getId()])) {
            $this->messageQueue[$message->getId()] = new \React\Promise\Deferred();
            $this->getClientPromise()->then(function(RpcClient $client) use ($message) {
                $client->send($message);
            });
        }

        return $this->messageQueue[$message->getId()]->promise();
    }


}