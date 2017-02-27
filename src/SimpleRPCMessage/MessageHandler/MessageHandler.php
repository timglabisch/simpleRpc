<?php

namespace Tg\SimpleRPC\SimpleRPCMessage\MessageHandler;


use Tg\SimpleRPC\SimpleRPCMessage\Codec\CodecDecodeInterface;
use Tg\SimpleRPC\SimpleRPCMessage\Codec\CodecEncodeInterface;
use Tg\SimpleRPC\SimpleRPCMessage\Codec\Exception\CodecException;
use Tg\SimpleRPC\SimpleRPCMessage\EasyBuf;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageCreatorInterface;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageExtractorInterface;

class MessageHandler
{
    /** @var CodecDecodeInterface[] */
    private $codecsDecode;

    /** @var CodecEncodeInterface[] */
    private $codecsEncode;

    /** @var MessageExtractorInterface[] */
    private $messageExtractors;

    /** @var MessageCreatorInterface[] */
    private $messageCreators;

    public function __construct(array $codecsDecode, array $codecsEncode, array $messageExtractors, array $messageCreators)
    {
        $this->codecsDecode = $codecsDecode;
        $this->codecsEncode = $codecsEncode;
        $this->messageExtractors = $messageExtractors;
        $this->messageCreators = $messageCreators;
    }

    /**
     * @param EasyBuf $buffer
     * @return int|CodecDecodeInterface
     */
    private function getCodecDecode(EasyBuf $buffer)
    {
        // first try to find a codec that supports the current buffer
        foreach ($this->codecsDecode as $codecDecode) {
            if ($codecDecode->supportsDecode($buffer) === CodecDecodeInterface::SUPPORTS_YES) {
                return $codecDecode;
            }
        }

        // try to find a codec that may need some more bytes
        foreach ($this->codecsDecode as $codecDecode) {
            if ($codecDecode->supportsDecode($buffer) === CodecDecodeInterface::SUPPORTS_NEEDS_MORE_BYTES) {
                return CodecDecodeInterface::SUPPORTS_NEEDS_MORE_BYTES;
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

    public function decode(string $bytes)
    {
        $buffer = new EasyBuf($bytes);

        $msgs = [];

        do {

            if (($codec = $this->getCodecDecode($buffer)) === CodecDecodeInterface::SUPPORTS_NEEDS_MORE_BYTES) {
                return $msgs;
            }

            $decded = $codec->decode($buffer);
            $msgs[] = $this->getMessageExtractor($decded)->extract($decded);


        } while($buffer->len());

        return $msgs;
    }


    private function getCodecEncode($msg)
    {
        // first try to find a codec that supports the current buffer
        foreach ($this->codecsEncode as $codecsEncode) {
            if ($codecsEncode->supportsEncode($msg)) {
                return $codecsEncode;
            }
        }

        throw new CodecException("Could not find matching encode codec");
    }

    private function getMessageCreators($msg)
    {
        foreach ($this->messageCreators as $messageCreator) {
            if ($messageCreator->supports($msg)) {
                return $messageCreator;
            }
        }

        throw new CodecException("Could not find matching message extractor");
    }

    public function encode($msg)
    {
        $raw = $this->getMessageCreators($msg);
        return $this->getCodecEncode($raw)->encode($raw);
    }

}