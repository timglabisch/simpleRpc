<?php

namespace Tg\SimpleRPC\SimpleRPCServer\ServerHandler\Worker;


class WorkerClientConfiguration
{
    /** @var string[] */
    private $services = [];

    /** @var integer */
    private $active;

    /** @var integer */
    private $maxTasks;

    /**
     * @return \string[]
     */
    public function getServices(): array
    {
        return $this->services;
    }

    /**
     * @param \string[] $services
     */
    public function setServices(array $services)
    {
        $this->services = $services;
    }

    /**
     * @return int
     */
    public function getActive(): ?int
    {
        return $this->active;
    }

    /**
     * @param int $active
     */
    public function setActive(int $active)
    {
        $this->active = $active;
    }

    /**
     * @return int
     */
    public function getMaxTasks(): int
    {
        return $this->maxTasks;
    }

    /**
     * @param int $maxTasks
     */
    public function setMaxTasks(int $maxTasks)
    {
        $this->maxTasks = $maxTasks;
    }

}