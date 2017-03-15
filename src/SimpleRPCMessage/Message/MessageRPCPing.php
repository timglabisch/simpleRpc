<?php

namespace Tg\SimpleRPC\SimpleRPCMessage\Message;


class MessageRPCPing implements MessageInterface
{
    /** @var int */
    private $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    /** @return int */
    public function getId(): int
    {
        return $this->id;
    }

}