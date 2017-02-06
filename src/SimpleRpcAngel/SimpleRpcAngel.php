<?php

namespace Tg\SimpleRPC\SimpleRpcAngel;

use Symfony\Component\Process\Process;

class SimpleRpcAngel
{
    /** @var Process[] */
    private $servers = [];

    /** @var Process[] */
    private $workers = [];

    /** @var int */
    private $numServers;

    /** @var int */
    private $numWorkers;

    public function __construct(int $numServers = 1, int $numWorkers = 10)
    {
        $this->numServers = $numServers;
        $this->numWorkers = $numWorkers;
    }

    private function startServer()
    {
        $process = new Process('php server.php > /dev/null', null, [], null, 100);
        $process->start();

        return $process;
    }

    private function startWorker()
    {
        $process = new Process('php worker.php > /dev/null', null, [], null, 100);
        $process->start();

        return $process;
    }

    private function runServers() {

    }

    public function run() {

        foreach (range(1, $this->numServers) as $i) {
            $this->servers[] = $this->startServer();
        }

        foreach (range(1, $this->numWorkers) as $i) {
            $this->workers[] = $this->startWorker();
        }

        while (true) {

            foreach ($this->servers as $k => $v) {

                // echo $v->getOutput();

                if (!$v->isTerminated()) {
                    echo "Server {$v->getPid()} is running.\n";
                    continue;
                }

                echo "restart Server\n";
                $this->servers[$k] = $this->startServer();

            }


            foreach ($this->workers as $k => $v) {

                // echo $v->getOutput();

                if (!$v->isTerminated()) {
                    echo "Worker {$v->getPid()} is started.\n";
                    continue;
                }

                echo "restart Worker\n";
                $this->workers[$k] = $this->startWorker();
            }

            sleep(3);
        }

    }

}