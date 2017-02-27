<?php


namespace Tg\SimpleRPC\SimpleRPCMessage\Codec\V1;


class RPCCodecMessageV1
{
    /** @var int */
    private $id;

    /** @var string|null */
    private $header;

    /** @var string */
    private $body;

    /** @var string */
    private $type;

    public function __construct(int $id, string $header, string $body, $type)
    {
        $this->id = $id;
        $this->header = $header;
        $this->body = $body;
        $this->type = $type;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getHeader(): string
    {
        return $this->header;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getType(): string
    {
        return $this->type;
    }

}