<?php

namespace Tg\SimpleRPC\SimpleRPCServer\Module\ConsulModule;

use React\EventLoop\LoopInterface;
use SensioLabs\Consul\Services\Agent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tg\SimpleRPC\SimpleRPCServer\Event\SupportedServicesChangedEvent;
use Tg\SimpleRPC\SimpleRPCServer\Module\ModuleInterface;

class ConsulModule implements ModuleInterface
{
    /** @var LoopInterface */
    private $eventLoop;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    private $arguments = [];

    public function __construct(
        LoopInterface $eventLoop,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->eventLoop = $eventLoop;

        $eventDispatcher->addListener(SupportedServicesChangedEvent::class, function(SupportedServicesChangedEvent $event) {
            $this->ensureServiceIsRegistered($event->getServices());
        });
    }

    public function run(array $arguments)
    {
        $this->arguments = $arguments;
        $this->ensureServiceIsRegistered();
    }

    private function ensureServiceIsRegistered(array $tags = []) {
        $sf = new \SensioLabs\Consul\ServiceFactory([
            'base_uri' => 'http://172.20.20.10:8500',
        ]);
        /** @var $agent Agent */
        $agent = $sf->get('agent');

        $ip = gethostbyname(trim(`hostname`));
        $checkid = 'rpc_'.$ip.'_'.$this->arguments['port-client'];
        $q = [
            "ID" => $checkid,
            "Name" => "RPC-Server",
            "Tags" => $tags,
            "Address" => $ip,
            "Port" => (int)$this->arguments['port-client'],
            "EnableTagOverride" => false,
            "Check" => [
                "ID" => $checkid,
                "DeregisterCriticalServiceAfter" => "2m",
                "HTTP" => "http://{$ip}:{$this->arguments['port-admin']}/health",
                "Interval" => "10s"
            ]
        ];

        $agent->registerService($q);
    }

}