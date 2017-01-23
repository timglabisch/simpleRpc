<?php

namespace Tg\SimpleRPC\SimpleRPCServer;


use React\Socket\ConnectionInterface;
use Tg\SimpleRPC\ReceivedRpcMessage;
use Tg\SimpleRPC\RpcMessage;

class RpcClient
{
    private $id;

    /** @var ConnectionInterface */
    private $connection;

    /** @var string */
    private $buffer = '';

    /**
     * RpcClient constructor.
     * @param ConnectionInterface $connection
     */
    public function __construct($id, ConnectionInterface $connection)
    {
        $this->id = $id;
        $this->connection = $connection;
    }

    public function send(RpcMessage $message)
    {
        $this->connection->write($message->encode());
    }

    public function pushBytes(string $data)
    {
        $this->buffer = $this->buffer . $data;
    }

    public function consumeBuffer(int $size): string
    {
        $tmp = substr($this->buffer, 0, $size);
        $this->buffer = substr($this->buffer, $size);

        return $tmp;
    }

    /**
     * @return string|\Tg\SimpleRPC\ReceivedRpcMessage[]
     */
    public function resolveMessages()
    {
        return ReceivedRpcMessage::fromData($this);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getBuffer()
    {
        return $this->buffer;
    }

    public function close()
    {
        $this->connection->close();
    }

    public function __toString()
    {
        return $this->connection->getRemoteAddress()." #".$this->getId();
    }

}