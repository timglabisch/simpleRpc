<?php

namespace Tg\SimpleRPC\SimpleRPCMessage\Message;



class MessageRPCWorkerConfigurationRequest
{
    /** @var int */
    private $id;

    /** @var MessageRPCWorkerConfiguration */
    private $configuration;

    public function __construct($id, MessageRPCWorkerConfiguration $configuration)
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