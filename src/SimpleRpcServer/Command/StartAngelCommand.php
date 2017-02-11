<?php

namespace Tg\SimpleRPC\SimpleRPCServer\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tg\SimpleRPC\SimpleRpcAngel\SimpleRpcAngel;

class StartAngelCommand extends Command
{

    protected function configure()
    {
        $this->setName('angel');
    }
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $output->writeln('<info>Starting Angel</info>');

        (new SimpleRpcAngel())->run();
    }

}