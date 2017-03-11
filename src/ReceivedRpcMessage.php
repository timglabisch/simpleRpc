<?php

namespace Tg\SimpleRPC;


use Google\Protobuf\Internal\InputStream;
use Tg\SimpleRPC\SimpleRPCMessage\Generated\RpcClientHeaderRequest;
use Tg\SimpleRPC\SimpleRPCServer\RpcClient;

class ReceivedRpcMessage
{
    /** @var RpcClient */
    private $sender;

    private $msg;

    public function __construct(
        RpcClient $sender,
        $msg
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
     * @return mixed
     */
    public function getMsg()
    {
        return $this->msg;
    }

}