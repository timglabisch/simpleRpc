<?php

namespace Tg\SimpleRPC\SimpleRPCMessage;


class EasyBuf
{
    private $buffer;

    public function __construct(string $buffer = '')
    {
        $this->buffer = $buffer;
    }

    public function len()
    {
        return strlen($this->buffer);
    }

    public function hasLen(int $expectedLen)
    {
        return $this->len() >= $expectedLen;
    }

    public function look_at_next_bytes(int $bytes)
    {
        return substr($this->buffer, 0, $bytes);
    }

    public function unpack_next_bytes(int $bytes, string $format)
    {
        return unpack($format, $this->look_at_next_bytes($bytes));
    }

    public function pushBytes(string $data)
    {
        $this->buffer = $this->buffer . $data;
    }

    public function drainAt(int $bytes): string {
        $tmp = substr($this->buffer, 0, $bytes);
        $this->buffer = substr($this->buffer, $bytes);
        return $tmp;
    }
}