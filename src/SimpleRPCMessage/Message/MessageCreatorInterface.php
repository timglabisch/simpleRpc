<?php

namespace Tg\SimpleRPC\SimpleRPCMessage\Message;



use Tg\SimpleRPC\SimpleRPCMessage\Codec\CodecInterface;

interface MessageCreatorInterface
{
    public function create($msg);

    public function supports($message, CodecInterface $codec);
}