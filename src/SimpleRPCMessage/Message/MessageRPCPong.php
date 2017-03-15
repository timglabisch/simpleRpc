<?php

namespace Tg\SimpleRPC\SimpleRPCMessage\Message;


class MessageRPCPong implements MessageInterface
{
    private $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

}