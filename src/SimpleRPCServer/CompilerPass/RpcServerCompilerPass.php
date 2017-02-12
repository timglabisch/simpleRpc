<?php

namespace Tg\SimpleRPC\SimpleRPCServer\CompilerPass;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;


class RpcServerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $services = [];
        foreach ($container->findTaggedServiceIds('module') as $id => $exporterReference) {
            $services[] = $container->getDefinition($id);
        }
        $container->getDefinition('command_start_server')->addArgument($services);
    }
}