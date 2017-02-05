<?php

namespace Tg\SimpleRPC\SimpleRPCClient;

use React\EventLoop\LoopInterface;
use Tg\SimpleRPC\RpcMessage;

class SimpleRpcClient
{

    /** @var ClientConnection[] */
    private $connections = [];

    /**
     * SimpleRpcClient constructor.
     * @param ClientConnection[] $connections
     */
    public function __construct(LoopInterface $loop, array $connections)
    {
        $this->connections = array_map(function(string $connection) use ($loop) {
            return new ClientConnection($loop, $connection);
        }, $connections);
    }
    
    /** @return \React\Promise\PromiseInterface */
    public function send(RpcMessage $message) {
        return $this->connections[array_rand($this->connections)]->send($message);
    }


}