<?php

namespace Tg\SimpleRPC\SimpleRPCServer\Command;


use React\EventLoop\LoopInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tg\SimpleRPC\SimpleRPCServer\Module\ModuleInterface;
use Tg\SimpleRPC\SimpleRPCServer\RpcServerHandlerInterface;
use Tg\SimpleRPC\SimpleRPCServer\ServerHandler\ClientServerHandler;
use Tg\SimpleRPC\SimpleRPCServer\ServerHandler\LogableServerHandler;
use Tg\SimpleRPC\SimpleRPCServer\ServerHandler\PrometheusServerHandler;
use Tg\SimpleRPC\SimpleRPCServer\ServerHandler\ServerHandler;
use Tg\SimpleRPC\SimpleRPCServer\ServerHandler\WorkerServerHandler;
use Tg\SimpleRPC\SimpleRPCServer\SimpleRpcServerHandler;
use Tg\SimpleRPC\SimpleRPCServer\WorkQueue;


class StartServerCommand extends Command
{
    /** @var LoopInterface */
    private $loop;

    /** @var ClientServerHandler */
    private $clientServerHandler;

    /** @var WorkerServerHandler */
    private $workerServerHandler;

    /** @var WorkQueue */
    private $workQueue;

    /** @var ModuleInterface[] */
    private $modules = [];

    public function __construct(
        LoopInterface $loop,
        RpcServerHandlerInterface $clientServerHandler,
        RpcServerHandlerInterface $workerServerHandler,
        WorkQueue $workQueue,
        array $modules
    )
    {
        $this->loop = $loop;
        $this->clientServerHandler = $clientServerHandler;
        $this->workerServerHandler = $workerServerHandler;
        $this->workQueue = $workQueue;
        $this->modules = $modules;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('server');
    }
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $output->writeln('<info>Starting Server</info>');


        (new SimpleRpcServerHandler($this->clientServerHandler))->run(1337, $this->loop);


        (new SimpleRpcServerHandler($this->workerServerHandler))->run(1338, $this->loop);

        foreach ($this->modules as $module) {
            $module->run();
        }

        $this->loop->run();
    }
}