<?php

namespace Tg\SimpleRPC\SimpleRPCServer\Angel;


use Symfony\Component\Process\Process;

class ProtectedServer
{
    /** @var int */
    private $portClient;

    /** @var int */
    private $portWorker;

    /** @var int */
    private $portAdmin;

    /** @var Process */
    private $process;

    /** @var ProtectedWorker[] */
    private $workers;

    public function __construct($portClient, $portWorker, $portAdmin, Process $process, array $workers)
    {
        $this->portClient = $portClient;
        $this->portWorker = $portWorker;
        $this->portWorker = $portWorker;
        $this->portAdmin = $portAdmin;
        $this->process = $process;
        $this->workers = $workers;
    }

    public function getPortClient(): int
    {
        return $this->portClient;
    }

    public function getPortWorker(): int
    {
        return $this->portWorker;
    }

    public function getPortAdmin(): int
    {
        return $this->portAdmin;
    }

    public function getProcess(): Process
    {
        return $this->process;
    }

    /** @return ProtectedWorker[] */
    public function getWorkers(): array
    {
        return $this->workers;
    }

    public function getName()
    {
        return 'Server :'.$this->getPortClient().' (admin :'.$this->getPortAdmin().') pid: '.$this->getProcess()->getPid();
    }

}