<?php

namespace Tg\SimpleRPC;


use Google\Protobuf\Internal\InputStream;
use Tg\SimpleRPC\SimpleRPCMessage\Generated\RpcClientHeaderRequest;
use Tg\SimpleRPC\SimpleRPCServer\RpcClient;

class ReceivedRpcMessage extends RpcMessage
{
    /** @var RpcClient */
    private $sender;

    /**
     * ReceivedRpcMessage constructor.
     * @param RpcClient $sender
     */
    public function __construct(
        RpcClient $sender,
        RpcClientHeaderRequest $clientHeaderRequest,
        $buffer,
        $protocolIdentifier = 1337,
        $version = 1,
        $messageId = null
    ) {
        $this->sender = $sender;
        parent::__construct($buffer, $clientHeaderRequest, $protocolIdentifier, $version, $messageId);
    }

    /**
     * @param RpcClient $client
     * @return ReceivedRpcMessage[]|string
     */
    public static function fromData(RpcClient $client)
    {
        /** @var RpcMessage[] */
        $messages = [];

        while (true) {

            if (strlen($client->getBuffer()) <= static::getHeaderSize()) {
                return $messages ? $messages : static::STATE_NEEDS_MORE_BYTES;
            }

            $unpacked = unpack('nprotocol/nversion/Nid/Nheader_length/Nlength', substr($client->getBuffer(), 0, static::getHeaderSize()));

            if (!isset(
                $unpacked['protocol'],
                $unpacked['version'],
                $unpacked['id'],
                $unpacked['header_length'],
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

            if (!is_int($unpacked['header_length'])) {
                return static::STATE_MALFORMED;
            }

            if (!is_int($unpacked['id'])) {
                return static::STATE_MALFORMED;
            }

            $expectedSize = static::getHeaderSize() + $unpacked['header_length'] + $unpacked['length'];

            if (strlen($client->getBuffer()) < $expectedSize) {
                return $messages ? $messages : static::STATE_NEEDS_MORE_BYTES;
            }

            $message = $client->consumeBuffer($expectedSize);

            $header = (new RpcClientHeaderRequest());

            if (!$header->parseFromStream(new InputStream(substr($message, static::getHeaderSize(), $unpacked['header_length'])))) {
                return static::STATE_MALFORMED;
            }

            $messages[] = new static(
                $client,
                $header,
                substr($message, static::getHeaderSize() + $unpacked['header_length'], $unpacked['length']),
                $unpacked['protocol'],
                $unpacked['version'],
                $unpacked['id']
            );
        }

        return $messages;
    }

    /** @return RpcClientHeaderRequest */
    public function getHeader()
    {
        return parent::getHeader();
    }

    /** @return RpcClient */
    public function getSender()
    {
        return $this->sender;
    }


}