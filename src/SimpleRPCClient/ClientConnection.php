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

    private $maxPendingRpcCalls;

    /** @return PromiseInterface */
    private function getClientPromise()
    {
        if (!$this->clientPromisor) {
            $this->clientPromisor = new \React\Promise\Deferred();
            $connection = (new TcpConnector($this->loop))->connect($this->connectionsString);
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

    public function __construct(LoopInterface $loop, string $connectionsString, int $maxPendingRpcCalls = 5000)
    {
        $this->loop = $loop;
        $this->connectionsString = $connectionsString;
        $this->maxPendingRpcCalls = $maxPendingRpcCalls;
    }

    public function send(RpcMessage $message): PromiseInterface
    {
        if (count($this->messageQueue) > $this->maxPendingRpcCalls) {
            throw new \LogicException("
                you have more than {$this->maxPendingRpcCalls} pending rpc calls.
                this could become a memory issue.
                may you forgot to call the run method on the loop or the server isn't ready?
            ");
        }

        if (!isset($this->messageQueue[$message->getId()])) {
            $this->messageQueue[$message->getId()] = new \React\Promise\Deferred();
            $this->getClientPromise()->then(function(RpcClient $client) use ($message) {
                $client->send($message);
            });
        }

        return $this->messageQueue[$message->getId()]->promise();
    }


}