<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
require __DIR__ . '/vendor/autoload.php';

class NoteServer implements MessageComponentInterface {
    protected $clients;
    protected $noteClients;
    protected $conn;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->noteClients = [];
        $this->conn = new mysqli("localhost", "root", "", "notepad_db");
        echo "WebSocket server started\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection: {$conn->resourceId}\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        if (!$data || !isset($data['noteId']) || !isset($data['delta']) || !isset($data['userId'])) {
            return;
        }
        
        $noteId = $data['noteId'];
        $delta = $data['delta'];
        $userId = $data['userId'];

        // Verify permission
        $stmt = $this->conn->prepare("
            SELECT n.id 
            FROM notes n 
            LEFT JOIN shared_notes sn ON n.id = sn.note_id 
            WHERE n.id = ? AND (n.user_id = ? OR (sn.shared_with_user_id = ? AND sn.permission_level = 'edit'))
        ");
        $stmt->bind_param("iii", $noteId, $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if (!$result->fetch_assoc()) {
            return; // Unauthorized
        }
        $stmt->close();

        if (!isset($this->noteClients[$noteId])) {
            $this->noteClients[$noteId] = new \SplObjectStorage;
        }
        if (!$this->noteClients[$noteId]->contains($from)) {
            $this->noteClients[$noteId]->attach($from);
        }

        foreach ($this->noteClients[$noteId] as $client) {
            if ($client !== $from) {
                $client->send(json_encode([
                    'noteId' => $noteId,
                    'delta' => $delta
                ]));
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        foreach ($this->noteClients as $noteId => $clients) {
            if ($clients->contains($conn)) {
                $clients->detach($conn);
                if ($clients->count() === 0) {
                    unset($this->noteClients[$noteId]);
                }
            }
        }
        echo "Connection closed: {$conn->resourceId}\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }

    public function __destruct() {
        $this->conn->close();
    }
}

$server = \Ratchet\Server\IoServer::factory(
    new \Ratchet\Http\HttpServer(
        new \Ratchet\WebSocket\WsServer(
            new NoteServer()
        )
    ),
    8080
);

$server->run();