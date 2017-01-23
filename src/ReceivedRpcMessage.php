<?php

namespace Tg\SimpleRPC;


use Tg\SimpleRPC\SimpleRPCServer\RpcClient;

class ReceivedRpcMessage extends RpcMessage
{
    /** @var RpcClient */
    private $sender;

    /**
     * ReceivedRpcMessage constructor.
     * @param RpcClient $sender
     */
    public function __construct(RpcClient $sender, $buffer, $protocolIdentifier = 1337, $version = 1)
    {
        $this->sender = $sender;
        parent::__construct($buffer, $protocolIdentifier, $version);
    }

    /**
     * @param RpcClient $client
     * @return RpcMessage[]|string
     */
    public static function fromData(RpcClient $client)
    {
        /** @var RpcMessage[] */
        $messages = [];

        while (true) {

            if (strlen($client->getBuffer()) <= static::getHeaderSize()) {
                return $messages ? $messages : static::STATE_NEEDS_MORE_BYTES;
            }

            $unpacked = unpack('nprotocol/nversion/Nlength', substr($client->getBuffer(), 0, 8));

            if (!isset(
                $unpacked['protocol'],
                $unpacked['version'],
                $unpacked['length']
            )) {
                return static::STATE_MALFORMED;
            }

            if ($unpacked['protocol'] != 1337) {
                return static::STATE_MALFORMED;
            }

            if ($unpacked['version'] != 1) {
                return static::STATE_MALFORMED;
            }

            if (!is_int($unpacked['length'])) {
                return static::STATE_MALFORMED;
            }

            if (strlen($client->getBuffer()) < static::getHeaderSize() + $unpacked['length']) {
                return $messages ? $messages : static::STATE_NEEDS_MORE_BYTES;
            }

            $message = $client->consumeBuffer(static::getHeaderSize() + $unpacked['length']);

            $messages[] = new static(
                $client,
                substr($message, static::getHeaderSize(), $unpacked['length']),
                $unpacked['protocol'],
                $unpacked['version']
            );
        }

        return $messages;
    }

    /**
     * @return RpcClient
     */
    public function getSender()
    {
        return $this->sender;
    }


}