<?php

namespace Tg\SimpleRPC\SimpleRPCMessage\Message;


class MessageRPCWorkerConfiguration
{
    /** @var string */
    private $active;

    /** @var int */
    private $max_tasks;

    /** @var string[] */
    private $services;

    /** @var string */
    private $connectionString;

    public function __construct(bool $active, int $max_tasks, array $services, string $connectionString)
    {
        $this->active = $active;
        $this->max_tasks = $max_tasks;
        $this->services = $services;
        $this->connectionString = $connectionString;
    }

    /** @return string */
    public function getActive(): string
    {
        return $this->active;
    }

    /** @return int */
    public function getMaxTasks(): int
    {
        return $this->max_tasks;
    }

    /** @return \string[] */
    public function getServices(): array
    {
        return $this->services;
    }

    /** @return string */
    public function getConnectionString(): string
    {
        return $this->connectionString;
    }

}