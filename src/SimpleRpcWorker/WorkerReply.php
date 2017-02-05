<?php

namespace Tg\SimpleRPC\SimpleRPCWorker;

class WorkerReply implements WorkerReplyInterface
{
    private $bytes;

    /**
     * WorkerReply constructor.
     * @param $bytes
     */
    public function __construct($bytes)
    {
        $this->bytes = $bytes;
    }

    function toBytes()
    {
        return $this->bytes;
    }
}