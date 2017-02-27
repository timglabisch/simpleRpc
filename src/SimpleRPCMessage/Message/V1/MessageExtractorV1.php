<?php

namespace Tg\SimpleRPC\SimpleRPCMessage\Message\V1;

use Google\Protobuf\Internal\InputStream;
use Tg\SimpleRPC\SimpleRPCMessage\Codec\Exception\MalformedDataException;
use Tg\SimpleRPC\SimpleRPCMessage\Codec\V1\RPCCodecMessageV1;
use Tg\SimpleRPC\SimpleRPCMessage\Codec\V1\RPCCodecV1;
use Tg\SimpleRPC\SimpleRPCMessage\Generated\RPCRequestHeader;
use Tg\SimpleRPC\SimpleRPCMessage\Generated\RPCResponseHeader;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageExtractorInterface;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCPing;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCPong;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCRequest;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCResponse;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCWorkerConfiguration;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCWorkerConfigurationRequest;
use Tg\SimpleRPC\SimpleRPCMessage\Message\MessageRPCWorkerConfigurationResponse;

class MessageExtractorV1 implements MessageExtractorInterface
{
    /**
     * @param RPCCodecMessageV1 $message
     * @return MessageRPCPing|MessageRPCPong|MessageRPCRequest|MessageRPCResponse|MessageRPCWorkerConfigurationRequest|MessageRPCWorkerConfigurationResponse
     */
    public function extract($message)
    {

        if ($message->getType() === RPCCodecV1::TYPE_RPC_REQUEST) {
            return $this->extractRPCRequest($message);
        } elseif ($message->getType() === RPCCodecV1::TYPE_RPC_RESPONSE) {
            return $this->extractRPCResponse($message);
        } elseif ($message->getType() === RPCCodecV1::TYPE_PING) {
            return new MessageRPCPing($message->getId());
        } elseif ($message->getType() === RPCCodecV1::TYPE_PONG) {
            return new MessageRPCPong($message->getId());
        } elseif ($message->getType() === RPCCodecV1::TYPE_WORKER_CONFIGURATION_REQUEST) {
            return $this->extractWorkerConfigurationRequest($message);
        } elseif ($message->getType() === RPCCodecV1::TYPE_WORKER_CONFIGURATION_RESPONSE) {
            return $this->extractWorkerConfigurationResponse($message);
        }

        throw new MalformedDataException('Unknown Message Type');
    }

    private function extractWorkerConfigurationRequest(RPCCodecMessageV1 $message)
    {
        /** @var $header MessageRPCWorkerConfigurationRequest */
        $header = (new RPCRequestHeader())->parseFromStream(new InputStream($message->getHeader()));

        if (!$header) {
            throw new MalformedDataException('Could not parse Header');
        }

        $configuration = $header->getConfiguration();

        return new MessageRPCWorkerConfigurationRequest(
            $message->getId(),
            new MessageRPCWorkerConfiguration(
                $configuration->getName(),
                $configuration->getMaxTasks(),
                $configuration->getServices(),
                $configuration->getConnectionString()
            )
        );
    }

    private function extractWorkerConfigurationResponse(RPCCodecMessageV1 $message)
    {
        /** @var $header MessageRPCWorkerConfigurationResponse */
        $header = (new RPCResponseHeader())->parseFromStream(new InputStream($message->getHeader()));

        if (!$header) {
            throw new MalformedDataException('Could not parse Header');
        }

        $configuration = $header->getConfiguration();

        return new MessageRPCWorkerConfigurationResponse(
            $message->getId(),
            new MessageRPCWorkerConfiguration(
                $configuration->getName(),
                $configuration->getMaxTasks(),
                $configuration->getServices(),
                $configuration->getConnectionString()
            )
        );
    }

    private function extractRPCResponse(RPCCodecMessageV1 $message)
    {
        /** @var $header RPCResponseHeader */
        $header = (new RPCResponseHeader())->parseFromStream(new InputStream($message->getHeader()));

        if (!$header) {
            throw new MalformedDataException('Could not parse Header');
        }

        return new MessageRPCResponse(
            $message->getId(),
            $header->getDuration(),
            $message->getBody()
        );
    }

    private function extractRPCRequest(RPCCodecMessageV1 $message)
    {
        /** @var $header RPCRequestHeader */
        $header = (new RPCRequestHeader())->parseFromStream(new InputStream($message->getHeader()));

        if (!$header) {
            throw new MalformedDataException('Could not parse Header');
        }

        $validUntil = null;
        if ($header->getDatetime() !== 0) {
            if (!($validUntil = date_create('@' . $header->getDatetime())->setTimezone(\DateTimeZone::UTC))) {
                throw new MalformedDataException('Could not Parse ValidUntil');
            }
        }

        return new MessageRPCRequest(
            $message->getId(),
            $header->getMethod(),
            $message->getBody(),
            $validUntil,
            $header->getRepeatable()
        );
    }
}