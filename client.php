<?php


use React\SocketClient\TcpConnector;
use Tg\SimpleRPC\ReceivedRpcMessage;
use Tg\SimpleRPC\RpcMessage;

require __DIR__ . '/vendor/autoload.php';
@require __DIR__ . '/foo/example.pb.php';





$i = 0;


while (true) {


    $loop = React\EventLoop\Factory::create();
    $tcpConnector = new TcpConnector($loop);

    $connection = $tcpConnector->connect('127.0.0.1:1338');

    foreach (range(0, 550) as $foo) {

        $i++;
        $connection->then(
            function (React\Stream\Stream $stream) use ($i) {
                $stream->write((new RpcMessage("Some Message"))->encode());

                $client = new \Tg\SimpleRPC\SimpleRPCServer\RpcClient(0, $stream);

                $stream->on(
                    'data',
                    function ($data) use ($stream, $client, $i) {
                     //   echo "on data\n";
                        $client->pushBytes($data);

                        $msg = ReceivedRpcMessage::fromData($client);

                        if ($msg == ReceivedRpcMessage::STATE_NEEDS_MORE_BYTES) {
                           // echo "needs more byte\n";

                            return;
                        }

                        if (!is_array($msg)) {
                            die("got bad message\n");
                            $stream->end();
                        }

                        echo $i . " got message " . $msg[0]->getBuffer() . "\n";

                        $stream->end();
                        $client->close();
                    }
                );

            }
        );
    }

    //sleep(1);

    $loop->run();

    gc_collect_cycles();

}