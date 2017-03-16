<?php

namespace Tg\SimpleRPC\SimpleRPCMessage\Message\V1;


use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\GPBUtil;
use Google\Protobuf\Internal\InputStream;
use Google\Protobuf\Internal\RepeatedField;
use Tg\SimpleRPC\SimpleRPCMessage\Codec\CodecInterface;
use Tg\SimpleRPC\SimpleRPCMessage\Codec\Exception\CodecException;
use Tg\SimpleRPC\SimpleRPCMessage\Codec\V1\RPCCodecMessageV1;
use Tg\SimpleRPC\SimpleRPCMessage\Codec\V1\RPCCodecV1;
use Tg\SimpleRPC\SimpleRPCMessage\Generated\RPCRequestHeader;
use Tg\SimpleRPC\SimpleRPCMessage\Generated\RPCResponseHeader;
use Tg\SimpleRPC\SimpleRPCMessage\Generated\RPCWorkerConfiguration;
use Tg\SimpleRPC\SimpleRPCMessage\Generated\RPCWorkerRequest;
use Tg\SimpleRPC\SimpleRPCMessage\Generated\RPCWorkerResponse;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageCreatorInterface;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCPing;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCPong;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCRequest;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCResponse;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCWorkerConfiguration;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCWorkerConfigurationRequest;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCWorkerConfigurationResponse;

class MessageCreatorV1 implements MessageCreatorInterface
{
    /** @return RPCCodecMessageV1 */
    public function create($message)
    {
        if ($message instanceof MessageRPCRequest) {
            return $this->createRPCRequest($message);
        } elseif ($message instanceof MessageRPCResponse) {
            return $this->createRPCResponse($message);
        } elseif ($message instanceof MessageRPCPing) {
            return $this->createRPCPing($message);
        } elseif ($message instanceof MessageRPCPong) {
            return $this->createRPCPong($message);
        } elseif ($message instanceof MessageRPCWorkerConfigurationRequest) {
            return $this->createWorkerConfigurationRequest($message);
        } elseif ($message instanceof MessageRPCWorkerConfigurationResponse) {
            return $this->createWorkerConfigurationResponse($message);
        }

        throw new CodecException('Message is not supported for class '.get_class($message));
    }

    public function supports($message, CodecInterface $codec)
    {
        return $codec instanceof RPCCodecV1;
    }

    private function createRPCRequest(MessageRPCRequest $message): RPCCodecMessageV1
    {
        /*
        $header = new RPCRequestHeader();
        $header->setMethod($message->getMethod());
        $header->setRepeatable($message->isRepeatAble());

        if ($message->getRelevantUntil()) {
            $header->setDatetime($message->getRelevantUntil()->getTimestamp());
        }
        */

        return new RPCCodecMessageV1(
            $message->getId(),
            json_encode(['m' => $message->getMethod()]),
            $message->getBody(),
            RPCCodecV1::TYPE_RPC_REQUEST
        );
    }

    private function createRPCResponse(MessageRPCResponse $message): RPCCodecMessageV1
    {
        return new RPCCodecMessageV1(
            $message->getId(),
            '',
            $message->getBody(),
            RPCCodecV1::TYPE_RPC_RESPONSE
        );
    }

    private function createRPCPing(MessageRPCPing $message): RPCCodecMessageV1
    {
        return new RPCCodecMessageV1(
            $message->getId(),
            '',
            '',
            RPCCodecV1::TYPE_PING
        );
    }

    private function createRPCPong(MessageRPCPong $message): RPCCodecMessageV1
    {
        return new RPCCodecMessageV1(
            $message->getId(),
            '',
            '',
            RPCCodecV1::TYPE_PONG
        );
    }

    private function createWorkerConfiguration(MessageRPCWorkerConfiguration $configuration)
    {
        $c = new RPCWorkerConfiguration();
        foreach ($configuration->getServices() as $service) {
            $c->getServices()[] = $service;
        }
        $c->setActive($configuration->getActive());
        $c->setMaxTasks($configuration->getMaxTasks());
        $c->setConnectionString($configuration->getConnectionString());
        return $c;
    }

    private function createWorkerConfigurationRequest(MessageRPCWorkerConfigurationRequest $message): RPCCodecMessageV1
    {

        $content = new RPCWorkerRequest();
        $configuration = $this->createWorkerConfiguration($message->getConfiguration());
        $content->setConfiguration($configuration);

        return new RPCCodecMessageV1(
            $message->getId(),
            '',
            $content->encode(),
            RPCCodecV1::TYPE_WORKER_CONFIGURATION_REQUEST
        );
    }

    private function createWorkerConfigurationResponse(MessageRPCWorkerConfigurationResponse $message): RPCCodecMessageV1
    {
        $content = new RPCWorkerRequest();
        $configuration = $this->createWorkerConfiguration($message->getConfiguration());
        $content->setConfiguration($configuration);

        return new RPCCodecMessageV1(
            $message->getId(),
            '',
            $content->encode(),
            RPCCodecV1::TYPE_WORKER_CONFIGURATION_RESPONSE
        );
    }
}