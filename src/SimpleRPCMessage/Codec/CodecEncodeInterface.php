<?php

namespace Tg\SimpleRPC\SimpleRPCMessage\Codec;


interface CodecEncodeInterface
{

    public function supportsEncode($msg): bool;

    public function encode($msg);
}