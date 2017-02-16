<?php

namespace Tg\SimpleRPC\SimpleRPCClient\ServiceDiscovery;

interface ServiceDiscoveryInterface
{
    public function getConnectionString(): string;
}