<?php

namespace Tg\SimpleRPC\SimpleRPCClient;

use React\EventLoop\LoopInterface;
use SensioLabs\Consul\Services\Catalog;
use Tg\SimpleRPC\RpcMessage;

class SimpleRpcClient
{

    /** @var ClientConnection[] */
    static $connections = null;

    /** @var LoopInterface */
    private $loop;

    /** @var string[] */
    private $consuls = [];

    /**
     * SimpleRpcClient constructor.
     * @param ClientConnection[] $connections
     */
    public function __construct(LoopInterface $loop, array $consuls)
    {
        $this->loop = $loop;
        $this->consuls = $consuls;
    }

    private function initializeConnections()
    {
        if (static::$connections !== null) {
            return;
        }

        $this->loadEndpointsFromConsul();

        static::$connections = array_map(function(array $connection) {
            return new ClientConnection($this->loop, $connection['ServiceAddress'].':'.$connection['ServicePort']);
        }, $this->loadEndpointsFromConsul());
    }

    /** @return \React\Promise\PromiseInterface */
    public function send(RpcMessage $message) {
        $this->initializeConnections();
        return static::$connections[array_rand(static::$connections)]->send($message);
    }

    private function loadEndpointsFromConsul()
    {
        foreach ($this->consuls as $consul) {
            $sf = new \SensioLabs\Consul\ServiceFactory(
                [
                    'base_uri' => $consul,
                ]
            );
            /** @var $catalog Catalog */
            $catalog = $sf->get('catalog');
            $services = $catalog->service('RPC-Server')->json();

            if (is_array($services)) {
                return $services;
            }
        }

        return [];
    }


}