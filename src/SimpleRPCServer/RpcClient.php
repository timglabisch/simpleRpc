<?php

namespace Tg\SimpleRPC\SimpleRPCServer;


use React\Socket\ConnectionInterface;
use React\Stream\DuplexStreamInterface;
use Tg\SimpleRPC\ReceivedRpcMessage;
use Tg\SimpleRPC\SimpleRPCMessage\Codec\CodecInterface;
use Tg\SimpleRPC\SimpleRPCMessage\Codec\Exception\CodecException;
use Tg\SimpleRPC\SimpleRPCMessage\EasyBuf;
use Tg\SimpleRPC\SimpleRPCMessage\MessageHandler\MessageHandler;

class RpcClient
{
    private $id;

    /** @var ConnectionInterface */
    private $connection;

    /** @var EasyBuf */
    private $buffer;

    /** @var CodecInterface|null */
    private $codec;

    /** @var MessageHandler */
    private $messageHandler;

    /**
     * @param ConnectionInterface $connection
     */
    public function __construct($id, DuplexStreamInterface $connection, MessageHandler $messageHandler, CodecInterface $codec = null)
    {
        $this->buffer = new EasyBuf();
        $this->id = $id;
        $this->connection = $connection;
        $this->messageHandler = $messageHandler;
        $this->codec = $codec;
    }

    public function send($message)
    {
        if (!$this->codec instanceof CodecInterface) {
            throw new CodecException("Codec is unknwon.");
        }

        $this->connection->write(
            $this->messageHandler->encode($message, $this->codec)
        );
    }

    /**
     * @return EasyBuf
     */
    public function getBuffer()
    {
        return $this->buffer;
    }

    /**
     * @return ReceivedRpcMessage[]
     */
    public function resolveMessages()
    {
        if (!$this->codec) {
            $codec = $this->messageHandler->getCodecByBuffer($this->getBuffer());
            if ($codec instanceof CodecInterface) {
                $this->codec = $codec;
            } elseif ($codec === CodecInterface::DECODE_NEEDS_MORE_BYTES) {
                return [];
            }
        }

        return array_map(function($msg) {
            return new ReceivedRpcMessage(
                $this,
                $msg
            );
        }, $this->messageHandler->decode(
            $this->getBuffer(),
            $this->codec
        ));


    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function close()
    {
        $this->connection->close();
    }

    public function __toString()
    {
        return " #".$this->getId();
    }

}