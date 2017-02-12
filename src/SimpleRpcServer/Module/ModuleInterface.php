<?php


namespace Tg\SimpleRPC\SimpleRPCServer\Module;


interface ModuleInterface
{
    public function run(array $arguments);
}