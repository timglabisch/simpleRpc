<?php

namespace Tg\SimpleRPC\SimpleRPCMessage\MessageHandler;


use Tg\SimpleRPC\SimpleRPCMessage\Codec\CodecInterface;
use Tg\SimpleRPC\SimpleRPCMessage\Codec\CodecEncodeInterface;
use Tg\SimpleRPC\SimpleRPCMessage\Codec\Exception\CodecException;
use Tg\SimpleRPC\SimpleRPCMessage\EasyBuf;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageCreatorInterface;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageExtractorInterface;

class MessageHandler
{
    /** @var CodecInterface[] */
    private $codecs;

    /** @var MessageExtractorInterface[] */
    private $messageExtractors;

    /** @var MessageCreatorInterface[] */
    private $messageCreators;

    public function __construct(array $codecs, array $messageExtractors, array $messageCreators)
    {
        $this->codecs = $codecs;
        $this->messageExtractors = $messageExtractors;
        $this->messageCreators = $messageCreators;
    }

    /**
     * @param EasyBuf $buffer
     * @return int|CodecInterface
     */
    public function getCodecByBuffer(EasyBuf $buffer)
    {
        // first try to find a codec that supports the current buffer
        foreach ($this->codecs as $codecDecode) {
            if ($codecDecode->supportsDecode($buffer) === CodecInterface::SUPPORTS_YES) {
                return $codecDecode;
            }
        }

        // try to find a codec that may need some more bytes
        foreach ($this->codecs as $codecDecode) {
            if ($codecDecode->supportsDecode($buffer) === CodecInterface::SUPPORTS_NEEDS_MORE_BYTES) {
                return CodecInterface::SUPPORTS_NEEDS_MORE_BYTES;
            }
        }

        throw new CodecException("Could not find matching codec");
    }

    private function getMessageExtractor($decoded)
    {
        foreach ($this->messageExtractors as $messageExtractor) {
            if ($messageExtractor->supports($decoded)) {
                return $messageExtractor;
            }
        }

        throw new CodecException("Could not find matching message extractor");
    }

    public function decode(EasyBuf $buffer, CodecInterface $codec)
    {
        $msgs = [];

        do {

            $codecSupports = $codec->supportsDecode($buffer);

            if ($codecSupports === CodecInterface::SUPPORTS_NEEDS_MORE_BYTES) {
                return $msgs;
            }

            if ($codecSupports === CodecInterface::SUPPORTS_NO) {
                throw new CodecException("Codec is wrong, isnt supported");
            }

            $decoded = $codec->decode($buffer);

            if ($decoded === CodecInterface::DECODE_NEEDS_MORE_BYTES) {
                return $msgs;
            }

            $msgs[] = $this->getMessageExtractor($decoded)->extract($decoded);


        } while($buffer->len());

        return $msgs;
    }


    private function getMessageCreator($msg, CodecInterface $codec)
    {
        foreach ($this->messageCreators as $messageCreator) {
            if ($messageCreator->supports($msg, $codec)) {
                return $messageCreator;
            }
        }

        throw new CodecException("Could not find matching message creator");
    }

    public function encode($msg, CodecInterface $codec)
    {
        return $codec->encode(
            $this->getMessageCreator($msg, $codec)->create($msg)
        );
    }

    public function getDefaultCodec()
    {
        return $this->codecs[0] ?? null;
    }

}