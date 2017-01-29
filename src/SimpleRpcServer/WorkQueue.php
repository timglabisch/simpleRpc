<?php

namespace Tg\SimpleRPC\SimpleRPCServer;


use Tg\SimpleRPC\ReceivedRpcMessage;

class WorkQueue
{
    /** @var \SplQueue */
    private $buffer;

    /** @var callable */
    private $cb;

    public function __construct()
    {
        $this->buffer = new \SplQueue();
        $this->cb = function() {};
    }

    public function on(callable $cb)
    {
        $this->cb = $cb;
    }

    public function push(ReceivedRpcMessage $message)
    {
        $this->buffer->push($message);
        $cb = $this->cb;
        $cb();
    }

    public function isEmpty()
    {
        return $this->buffer->isEmpty();
    }

    public function dequeue()
    {
        return $this->buffer->dequeue();
    }

}