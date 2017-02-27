<?php

namespace Tg\SimpleRPC\SimpleRPCMessage\Message;



interface MessageExtractorInterface
{
    public function extract($message);
}