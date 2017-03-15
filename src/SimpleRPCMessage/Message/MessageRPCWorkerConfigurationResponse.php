<?php

namespace Tg\SimpleRPC\SimpleRPCMessage\Message;


class MessageRPCWorkerConfigurationResponse implements MessageInterface
{
    /** @var int */
    private $id;

    /** @var MessageRPCWorkerConfiguration */
    private $configuration;

    public function __construct(int $id, MessageRPCWorkerConfiguration $configuration)
    {
        $this->id = $id;
        $this->configuration = $configuration;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getConfiguration(): MessageRPCWorkerConfiguration
    {
        return $this->configuration;
    }
    
}