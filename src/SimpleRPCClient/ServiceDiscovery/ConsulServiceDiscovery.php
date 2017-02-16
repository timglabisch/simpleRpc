<?php

namespace Tg\SimpleRPC\SimpleRPCClient\ServiceDiscovery;


use SensioLabs\Consul\Services\Catalog;
use Tg\SimpleRPC\SimpleRPCClient\Exception\CouldNotDiscoverServiceException;

class ConsulServiceDiscovery implements ServiceDiscoveryInterface
{
    /** @var string[] */
    private $consuls = [];

    /** @var int */
    private $numberOfConnections;

    /** @var null|string[] */
    private $connectionsStrings = null;

    /** @param \string[] $consuls */
    public function __construct(array $consuls, int $numberOfConnections = 1)
    {
        $this->consuls = $consuls;
        $this->numberOfConnections = $numberOfConnections;
    }

    private function getConnectionStrings()
    {
        foreach ($this->consuls as $consul) {
            $sf = new \SensioLabs\Consul\ServiceFactory([
                'base_uri' => $consul
            ]);

            /** @var $catalog Catalog */
            $catalog = $sf->get('catalog');
            $services = $catalog->service('RPC-Server')->json();

            if (!is_array($services)) {
                continue;
            }

            return array_map(function($connectionString) {
                return $connectionString['ServiceAddress'].':'.$connectionString['ServicePort'];
            }, $services);
        }

        throw new CouldNotDiscoverServiceException();
    }

    public function getConnectionString(): string
    {
        if ($this->connectionsStrings == null) {
            $connectionStrings = $this->getConnectionStrings();

            $connectionKeys = (array)array_rand($connectionStrings, min($this->numberOfConnections, count($connectionStrings)));

            foreach ($connectionKeys as $connectionKey) {
                $this->connectionsStrings[] = $connectionStrings[$connectionKey];
            }
        }
        
        return $this->connectionsStrings[array_rand($this->connectionsStrings)];
    }
}