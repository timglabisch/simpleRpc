<?php

namespace Tg\SimpleRPC;

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

    public function __construct($buffer, $protocolIdentifier = 1337, $version = 1)
    {
        $this->protocolIdentifier = $protocolIdentifier;
        $this->version = $version;
        $this->buffer = $buffer;
    }

    public static function getHeaderSize() {
        if (!static::$headerSize) {
            static::$headerSize = strlen($x = pack(
                'nnN',
                1337,
                1,
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

        return pack(
            'nnN',
            $this->protocolIdentifier,    // protocolIdentifier
            $this->version,    // protocolVersion
            strlen($this->buffer) // byteCount
        ).$this->buffer;
    }
    
}