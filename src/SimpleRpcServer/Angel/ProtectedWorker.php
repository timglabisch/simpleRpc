<?php

namespace Tg\SimpleRPC\SimpleRPCServer\Angel;


use Symfony\Component\Process\Process;

class ProtectedWorker
{
    /** @var Process */
    private $process;

    public function __construct(Process $process)
    {
        $this->process = $process;
    }

    public function getProcess(): Process
    {
        return $this->process;
    }


    public function getName()
    {
        return 'Worker pid: '.$this->getProcess()->getPid();
    }
}