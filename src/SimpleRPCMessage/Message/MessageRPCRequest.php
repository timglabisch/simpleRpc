<?php

namespace Tg\SimpleRPC\SimpleRPCMessage\Message;


class MessageRPCRequest
{
    /** @var int */
    private $id;

    /** @var string */
    private $method;

    /** @var string */
    private $body;

    /** @var \DateTime|null */
    private $relevantUntil;

    /** @var bool */
    private $repeatAble;

    public function __construct(int $id, string $method, string $body, \DateTime $relevantUntil = null, bool $repeatAble)
    {
        $this->id = $id;
        $this->method = $method;
        $this->body = $body;
        $this->relevantUntil = $relevantUntil;
        $this->repeatAble = $repeatAble;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    /** @return \DateTime|null */
    public function getRelevantUntil()
    {
        return $this->relevantUntil;
    }

    public function isRepeatAble(): bool
    {
        return $this->repeatAble;
    }

}