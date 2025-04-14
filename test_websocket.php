<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
require __DIR__ . '/vendor/autoload.php';

class TestServer implements MessageComponentInterface {
    public function onOpen(ConnectionInterface $conn) {
        echo "New connection: {$conn->resourceId}\n";
    }
    public function onMessage(ConnectionInterface $from, $msg) {
        echo "Message: $msg\n";
    }
    public function onClose(ConnectionInterface $conn) {
        echo "Connection closed: {$conn->resourceId}\n";
    }
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }
}

$server = \Ratchet\Server\IoServer::factory(
    new \Ratchet\Http\HttpServer(
        new \Ratchet\WebSocket\WsServer(
            new TestServer()
        )
    ),
    8080
);

$server->run();