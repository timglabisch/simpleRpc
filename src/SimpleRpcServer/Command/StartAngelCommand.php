<?php

namespace Tg\SimpleRPC\SimpleRPCServer\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tg\SimpleRPC\SimpleRpcAngel\SimpleRpcAngel;

class StartAngelCommand extends Command
{

    protected function configure()
    {
        $this->setName('angel');
        $this->addOption('port-client', null, InputOption::VALUE_OPTIONAL, 'Ports Clients are using (comma seperated, one port for each cpu is recommended)', '1338');
        $this->addOption('worker-count', null, InputOption::VALUE_OPTIONAL, 'Workers for every Server', 4);
    }
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $output->writeln('<info>Starting Angel</info>');

        (new SimpleRpcAngel())->run(
            array_map('trim', explode(',', $input->getOption('port-client'))),
            (int)$input->getOption('worker-count')
        );
    }

}