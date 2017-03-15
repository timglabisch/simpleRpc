<?php

namespace Tg\SimpleRPC\SimpleRPCMessage\Message;


class MessageRPCResponse implements MessageInterface
{
    /** @var int */
    private $id;

    /** @var int */
    private $duration;

    /** @var string */
    private $body;

    public function __construct(int $id, int $duration, string $body)
    {
        $this->id = $id;
        $this->duration = $duration;
        $this->body = $body;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function getBody(): string
    {
        return $this->body;
    }

}