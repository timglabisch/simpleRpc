<?php

namespace Tg\SimpleRPC;


use Google\Protobuf\Internal\InputStream;
use Tg\SimpleRPC\SimpleRPCMessage\Generated\RpcClientHeaderRequest;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageInterface;
use Tg\SimpleRPC\SimpleRPCServer\RpcClient;

class ReceivedRpcMessage
{
    /** @var RpcClient */
    private $sender;

    /** @var MessageInterface */
    private $msg;

    public function __construct(
        RpcClient $sender,
        MessageInterface $msg
    ) {
        $this->sender = $sender;
        $this->msg = $msg;
    }

    /**
     * @return RpcClient
     */
    public function getSender(): RpcClient
    {
        return $this->sender;
    }

    /**
     * @return MessageInterface
     */
    public function getMsg()
    {
        return $this->msg;
    }

}