<?php

namespace Tg\SimpleRPC\SimpleRPCMessage\Message;


class MessageRPCWorkerConfiguration
{
    /** @var string */
    private $name;

    /** @var int */
    private $max_tasks;

    /** @var string[] */
    private $services;

    /** @var string */
    private $connectionString;

    public function __construct(string $name, int $max_tasks, array $services, string $connectionString)
    {
        $this->name = $name;
        $this->max_tasks = $max_tasks;
        $this->services = $services;
        $this->connectionString = $connectionString;
    }

    /** @return string */
    public function getName(): string
    {
        return $this->name;
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