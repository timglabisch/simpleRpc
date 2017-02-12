<?php

namespace Tg\SimpleRPC\SimpleRPCServer\Command;


use React\EventLoop\LoopInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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

        $this->addOption('port-client', null, InputOption::VALUE_OPTIONAL, 'Ports Clients are using', 1338);
        $this->addOption('port-worker', null, InputOption::VALUE_OPTIONAL, 'Ports Worker are using', 1337);
        $this->addOption('port-admin', null, InputOption::VALUE_OPTIONAL, 'Ports Admin are using', 3333);
    }
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $output->writeln("\n".'<info>Starting Server</info>');

        $output->writeln(sprintf(
            "Clients can connect on Port: <info>%s</info>, Worker on Port <info>%s</info>, Admin on Port <info>%s</info>",
            $input->getOption('port-client'),
            $input->getOption('port-worker'),
            $input->getOption('port-admin')
        ));


        (new SimpleRpcServerHandler($this->clientServerHandler))->run(
            $input->getOption('port-client'),
            $this->loop
        );


        (new SimpleRpcServerHandler($this->workerServerHandler))->run(
            $input->getOption('port-worker'),
            $this->loop
        );

        foreach ($this->modules as $module) {
            $module->run($input->getOptions());
        }

        $this->loop->run();
    }
}