<?php

namespace Tg\SimpleRPC\Tests;

use PHPUnit_Framework_TestCase;
use React\Socket\ConnectionInterface;
use Tg\SimpleRPC\ReceivedRpcMessage;
use Tg\SimpleRPC\RpcMessage;
use Tg\SimpleRPC\SimpleRPCServer\RpcClient;

class ReceivedRpcMessageTest extends PHPUnit_Framework_TestCase
{

    public function testEncodeDecodeSimpleMessage()
    {
        $client = new RpcClient(123, $this->prophesize(ConnectionInterface::class)->reveal());

        $message = new RpcMessage("fooo");
        $client->pushBytes($message->encode());

        $receivedMessages = ReceivedRpcMessage::fromData($client);

        static::assertTrue(is_array($receivedMessages));
        static::assertCount(1, $receivedMessages);

        $this->assertEquals('fooo', $receivedMessages[0]->getBuffer());
    }

    public function testEncodeDecodeMultiMessage()
    {
        $client = new RpcClient(123, $this->prophesize(ConnectionInterface::class)->reveal());

        $client->pushBytes((new RpcMessage("fooo1"))->encode());
        $client->pushBytes((new RpcMessage("fooo2"))->encode());

        $receivedMessages = ReceivedRpcMessage::fromData($client);

        static::assertTrue(is_array($receivedMessages));
        static::assertCount(2, $receivedMessages);

        $this->assertEquals('fooo1', $receivedMessages[0]->getBuffer());
        $this->assertEquals('fooo2', $receivedMessages[1]->getBuffer());
    }

    public function testEncodeDecodeMessage()
    {
        $client = new RpcClient(123, $this->prophesize(ConnectionInterface::class)->reveal());

        $message = (new RpcMessage("fooo"))->encode();

        for($i=0; $i < strlen($message); $i++) {
            $x = ReceivedRpcMessage::fromData($client);
            $this->assertEquals(ReceivedRpcMessage::STATE_NEEDS_MORE_BYTES, $x);
            $client->pushBytes($message[$i]);
        }

        $a = ReceivedRpcMessage::fromData($client);
        $this->assertCount(1, $a);
        $this->assertEquals("fooo", $a[0]->getBuffer());
    }

}
