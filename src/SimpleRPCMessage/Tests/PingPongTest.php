<?php

namespace Tg\SimpleRPC\SimpleRPCMessage\Tests;


use Tg\SimpleRPC\SimpleRPCMessage\Codec\V1\RPCCodecV1;
use Tg\SimpleRPC\SimpleRPCMessage\EasyBuf;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCPing;
use Tg\SimpleRPC\SimpleRPCMessage\Message\V1\MessageCreatorV1;
use Tg\SimpleRPC\SimpleRPCMessage\Message\V1\MessageExtractorV1;
use Tg\SimpleRPC\SimpleRPCMessage\MessageHandler\MessageHandler;

class PingPongTest extends \PHPUnit_Framework_TestCase
{
    public function testFoo()
    {
        $codec = new RPCCodecV1();
        $messageCreator = new MessageCreatorV1();
        $messageExtractor = new MessageExtractorV1();

        // encode
        $ping = new MessageRPCPing(1234);
        $rpcMessage = $messageCreator->create($ping);
        static::assertTrue($codec->supportsEncode($rpcMessage));
        $encodedMessage = $codec->encode($rpcMessage);

        // decode
        $this->assertEquals(RPCCodecV1::SUPPORTS_YES, $codec->supportsDecode(new EasyBuf($encodedMessage)));
        $rpcMessage = $codec->decode(new EasyBuf($encodedMessage));
        $pingExtracted = $messageExtractor->extract($rpcMessage);

        $this->assertEquals($ping, $pingExtracted);
    }

}