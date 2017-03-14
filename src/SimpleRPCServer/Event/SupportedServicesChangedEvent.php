<?php

namespace Tg\SimpleRPC\SimpleRPCServer\Event;

use Symfony\Component\EventDispatcher\Event;

class SupportedServicesChangedEvent extends Event
{
    /** @var string[] */
    private $services = [];

    /** @param \string[] $services */
    public function __construct(array $services)
    {
        $this->services = $services;
    }

    /** @return \string[] */
    public function getServices(): array
    {
        return $this->services;
    }

}