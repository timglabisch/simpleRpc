<?php

use React\Socket\ConnectionInterface;
use React\SocketClient\TcpConnector;
use Tg\SimpleRPC\ReceivedRpcMessage;
use Tg\SimpleRPC\RpcMessage;
use Tutorial\Person;

require __DIR__ . '/vendor/autoload.php';
@require __DIR__ . '/foo/example.pb.php';



$loop = React\EventLoop\Factory::create();

$tcpConnector = new TcpConnector($loop);


$tcpConnector->connect('127.0.0.1:1337')->then(function (React\Stream\Stream $stream) {

    $i = 0;

    $client = new \Tg\SimpleRPC\SimpleRPCServer\RpcClient(0, $stream);

    $stream->on('error', function() {
       $a = 0;
    });

    $stream->on('data', function ($data) use ($stream, $client, &$i) {
        echo "on data\n";
        $client->pushBytes($data);

        $msg = ReceivedRpcMessage::fromData($client);

        if ($msg == ReceivedRpcMessage::STATE_NEEDS_MORE_BYTES) {
            echo "needs more bytes\n";
            return;
        }

        if (!is_array($msg)) {
            echo "got bad message\n";
            $stream->end();
        }

        echo "got message ".$msg[0]->getBuffer()."\n";
        echo (++$i) ." send answer\n";



        $stream->write((new RpcMessage("Send Answer"))->encode());
    });

}, function() {
    $a = 0;
});
$loop->run();