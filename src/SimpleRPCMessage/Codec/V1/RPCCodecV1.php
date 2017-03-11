<?php

namespace Tg\SimpleRPC\SimpleRPCMessage\Codec\V1;


use Tg\SimpleRPC\SimpleRPCMessage\Codec\CodecInterface;
use Tg\SimpleRPC\SimpleRPCMessage\Codec\Exception\MalformedDataException;
use Tg\SimpleRPC\SimpleRPCMessage\EasyBuf;

class RPCCodecV1 implements CodecInterface
{
    const PROTOCOL_IDENTIFIER = 1337;

    const PROTOCOL_VERSION = 1;

    const TYPE_RPC_REQUEST = 1;

    const TYPE_RPC_RESPONSE = 2;

    const TYPE_REJECT_RPC_REQUEST = 3;

    const TYPE_WORKER_CONFIGURATION_REQUEST = 5;

    const TYPE_WORKER_CONFIGURATION_RESPONSE = 6;

    const TYPE_PING = 7;

    const TYPE_PONG = 8;

    protected static $headerSize;

    public static function getHeaderSize() {
        if (!static::$headerSize) {
            static::$headerSize = strlen($x = pack(
                'nnnNNN',
                1, // protocol
                1, // version
                1, // type
                1, // size header
                1, // size body
                1  // id
            ));
        }

        return static::$headerSize;
    }

    public function supportsDecode(EasyBuf $easyBuf)
    {
        if (!$easyBuf->hasLen(static::getHeaderSize())) {
            return CodecInterface::SUPPORTS_NEEDS_MORE_BYTES;
        }

        if (array_values($easyBuf->unpack_next_bytes(4, 'na/nb')) === [static::PROTOCOL_IDENTIFIER, static::PROTOCOL_VERSION]) {
            return CodecInterface::SUPPORTS_YES;
        }

        return CodecInterface::SUPPORTS_NO;
    }

    public function decode(EasyBuf $easyBuf)
    {
        $unpacked = $easyBuf->unpack_next_bytes(
            static::getHeaderSize(),
            'nprotocol/nversion/ntype/Nid/Nheader_length/Nlength'
        );

        if (!isset(
            $unpacked['protocol'],
            $unpacked['version'],
            $unpacked['type'],
            $unpacked['id'],
            $unpacked['header_length'],
            $unpacked['length']
        )) {
            throw new MalformedDataException('Missing Fields on Codec.');
        }

        $expectedSize = static::getHeaderSize() + (int)$unpacked['header_length'] + (int)$unpacked['length'];

        if (!$easyBuf->hasLen($expectedSize)) {
            return CodecInterface::DECODE_NEEDS_MORE_BYTES;
        }

        $easyBuf->drainAt(static::getHeaderSize()); // consume the protocol header

        return new RPCCodecMessageV1(
            (int)$unpacked['id'],
            $unpacked['header_length'] ? $easyBuf->drainAt((int)$unpacked['header_length']) : '',
            $easyBuf->drainAt((int)$unpacked['length']),
            (int)$unpacked['type']
        );
    }

    public function supportsEncode($msg): bool
    {
        return $msg instanceof RPCCodecMessageV1;
    }

    /** @param RPCCodecMessageV1 $msg */
    public function encode($msg): string
    {
        return pack(
                'nnnNNN',
                static::PROTOCOL_IDENTIFIER,
                static::PROTOCOL_VERSION,
                $msg->getType(),
                $msg->getId(),
                strlen($msg->getHeader()),
                strlen($msg->getBody())
            ).
            $msg->getHeader().
            $msg->getBody()
        ;
    }

}