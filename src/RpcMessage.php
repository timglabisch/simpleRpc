<?php

namespace Tg\SimpleRPC;

use Google\Protobuf\Internal\Message;
use Tg\SimpleRPC\SimpleRPCMessage\Generated\RpcClientHeaderRequest;
use Tg\SimpleRPC\SimpleRPCServer\RpcClient;

class RpcMessage
{
    const STATE_NEEDS_MORE_BYTES = 'STATE_NEEDS_MORE_BYTES';

    const STATE_IS_READY = 'STATE_IS_READY';

    const STATE_MALFORMED = 'STATE_MALFORMED';

    /** @var int */
    private $protocolIdentifier;

    /** @var int */
    private $version;

    /** @var string */
    private $buffer;

    protected static $headerSize;

    private static $messageIdCounter = 0;

    private $messageId;

    /** @var Message */
    private $header;

    public function __construct(
        $buffer,
        Message $header,
        $protocolIdentifier = 1337,
        $version = 1,
        $messageId = null
    ) {
        $this->protocolIdentifier = $protocolIdentifier;
        $this->header = $header;
        $this->version = $version;
        $this->buffer = $buffer;
        $this->messageId = $messageId === null ? static::$messageIdCounter++ : $messageId;
    }

    /** @return int */
    public function getId()
    {
        return $this->messageId;
    }

    public static function getHeaderSize() {
        if (!static::$headerSize) {
            static::$headerSize = strlen($x = pack(
                'nnNNN',
                1337,
                1,
                PHP_INT_MAX,
                PHP_INT_MAX,
                PHP_INT_MAX
            ));
        }

        return static::$headerSize;
    }

    /**
     * @return int
     */
    public function getProtocolIdentifier()
    {
        return $this->protocolIdentifier;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getBuffer()
    {
        return $this->buffer;
    }
    
    public function encode(): string
    {
        $encodedHeader = $this->header->encode();

        return pack(
            'nnNNN',
            $this->protocolIdentifier,    // protocolIdentifier
            $this->version,    // protocolVersion
            $this->getId(),    // messageId
            strlen($encodedHeader), // HeaderCount
            strlen($this->buffer) // MessageCount
        ).$encodedHeader.$this->buffer;
    }

    /** @return Message */
    public function getHeader()
    {
        return $this->header;
    }


    
}