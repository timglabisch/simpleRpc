<?php

namespace Tg\SimpleRPC\SimpleRpcAngel;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Tg\SimpleRPC\SimpleRPCServer\Angel\ProtectedServer;
use Tg\SimpleRPC\SimpleRPCServer\Angel\ProtectedWorker;

class SimpleRpcAngel
{
    private $startPortWorker = 1390;

    private $startAdminPort = 1190;


    private function startServer(int $portServer, int $numWorkers): ProtectedServer
    {
        $portAdmin = $this->startAdminPort++;
        $portWorker = $this->startPortWorker++;

        $process = new Process($x = 'php server.php server --port-client='. $portServer .'  --port-worker='. $portWorker .'  --port-admin='. $portAdmin.' > /dev/null');
        $process->start();

        $workers = array_map(function() use ($portWorker, $process) {
            // todo, why is this needed?
            sleep(1);

            $processBuilder = new ProcessBuilder(['php','worker.php']);
            $processBuilder->setEnv('RPC_SERVER', '127.0.0.1:'.$portWorker);
            $workerProcess = $processBuilder->getProcess();
            $workerProcess->start();

            return new ProtectedWorker($workerProcess);
        }, range(1, $numWorkers));

        return new ProtectedServer($portServer, $portWorker, $portAdmin, $process, $workers);
    }


    public function run(array $serverPorts, int $numWorkers) {

        /** @var $servers ProtectedServer[] */
        $servers = array_map(function(int $serverPort) use ($numWorkers) {
            return $this->startServer($serverPort, $numWorkers);
        }, $serverPorts);

        while (true) {

            foreach ($servers as $server) {

                echo $server->getProcess()->getOutput();
                echo $server->getProcess()->getErrorOutput();
                $server->getProcess()->clearErrorOutput();
                $server->getProcess()->clearOutput();

                if (!$server->getProcess()->isTerminated()) {
                    echo "{$server->getName()} is running.\n";
                    continue;
                }

                echo $server->getProcess()->getOutput();
                echo "restart {$server->getName()}\n";
                $server->getProcess()->restart();
            }

            foreach ($servers as $server) {
                foreach ($server->getWorkers() as $worker) {
                    if (!$worker->getProcess()->isTerminated()) {
                        echo ".";
                        continue;
                    }

                    echo $worker->getProcess()->getOutput();
                    echo $worker->getProcess()->getErrorOutput();
                    $worker->getProcess()->clearErrorOutput();
                    $worker->getProcess()->clearOutput();

                    echo "{$server->getName()}\n";
                    echo "\t{$worker->getName()} restarted\n";
                    $worker->getProcess()->restart();
                }
            }
            echo "\n";

            sleep(3);
        }

    }

}