<?php

namespace Tg\SimpleRPC\SimpleRPCMessage\Codec;

use Tg\SimpleRPC\SimpleRPCMessage\EasyBuf;

interface CodecDecodeInterface
{
    const SUPPORTS_YES = 1;

    const SUPPORTS_NEEDS_MORE_BYTES = 2;

    const SUPPORTS_NO = 0;

    const DECODE_NEEDS_MORE_BYTES = null;

    public function supportsDecode(EasyBuf $easyBuf);

    public function decode(EasyBuf $easyBuf);
}