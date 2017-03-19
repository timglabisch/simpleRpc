<?php

namespace Tg\SimpleRPC\SimpleRPCMessage\Tests;


use Tg\SimpleRPC\SimpleRPCMessage\Codec\V1\RPCCodecV1;
use Tg\SimpleRPC\SimpleRPCMessage\EasyBuf;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCPing;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCPong;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCRequest;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCResponse;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCWorkerConfiguration;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCWorkerConfigurationRequest;
use Tg\SimpleRPC\SimpleRPCMessage\Codec\V1\MessageCreatorV1;
use Tg\SimpleRPC\SimpleRPCMessage\Codec\V1\MessageExtractorV1;
use Tg\SimpleRPC\SimpleRPCMessage\MessageHandler\MessageHandler;

class PingPongTest extends \PHPUnit_Framework_TestCase
{
    /*
    public function testFoo2()
    {

        $messageHandler = new MessageHandler([$codec = new RPCCodecV1()], [new MessageExtractorV1()], [new MessageCreatorV1()]);

        $this->assertEquals(
            [
                $ping = new MessageRPCPing(1234),
            ],
            $messageHandler->decode(
                $messageHandler->encode(
                    $ping,
                    $codec
                ),
                $codec
            )
        );

    }*/

    public function dataProviderEncodeDecode() {
        yield [new MessageRPCPing(1234)];
        yield [new MessageRPCPong(1234)];
        yield [new MessageRPCRequest(10, 'some_method', 'some_body')];
        yield [new MessageRPCResponse(10, 12, 'some_body')];
        yield [new MessageRPCWorkerConfigurationRequest(123, new MessageRPCWorkerConfiguration('foo', 10, ['la', 'le', 'lu'], 'foo'))];
    }

    /**
     * @dataProvider dataProviderEncodeDecode
     */
    public function testEncodeDecode($message)
    {
        $codec = new RPCCodecV1();

        // encode
        static::assertTrue($codec->supportsEncode($message));
        $encodedMessage = $codec->encode($message);

        // decode
        $this->assertEquals(RPCCodecV1::SUPPORTS_YES, $codec->supportsDecode(new EasyBuf($encodedMessage)));
        $rpcMessage = $codec->decode(new EasyBuf($encodedMessage));

        $this->assertEquals($message, $rpcMessage);
    }

}