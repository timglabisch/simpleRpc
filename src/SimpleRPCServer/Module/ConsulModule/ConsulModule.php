<?php

namespace Tg\SimpleRPC\SimpleRPCServer\Module\ConsulModule;

use React\EventLoop\LoopInterface;
use SensioLabs\Consul\Services\Agent;
use Tg\SimpleRPC\SimpleRPCServer\Module\ModuleInterface;

class ConsulModule implements ModuleInterface
{
    /** @var LoopInterface */
    private $eventLoop;

    public function __construct(LoopInterface $eventLoop)
    {
        $this->eventLoop = $eventLoop;
    }

    public function run(array $arguments)
    {
        $sf = new \SensioLabs\Consul\ServiceFactory([
            'base_uri' => 'http://172.20.20.10:8500',
        ]);
        /** @var $agent Agent */
        $agent = $sf->get('agent');

        $ip = gethostbyname(trim(`hostname`));
        $checkid = 'rpc_'.$ip.'_'.$arguments['port-client'];
        $q = [
            "ID" => $checkid,
            "Name" => "RPC-Server",
            "Tags" => [
                "v1"
            ],
            "Address" => $ip,
            "Port" => (int)$arguments['port-client'],
            "EnableTagOverride" => false,
            "Check" => [
                "ID" => $checkid,
                "DeregisterCriticalServiceAfter" => "2m",
                "HTTP" => "http://{$ip}:{$arguments['port-admin']}/health",
                "Interval" => "10s"
            ]
        ];

        $agent->registerService($q);
    }

}