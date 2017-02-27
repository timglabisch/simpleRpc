<?php

namespace Tg\SimpleRPC\SimpleRPCMessage\Message;



interface MessageExtractorInterface
{
    public function supports($message): bool;

    public function extract($message);
}