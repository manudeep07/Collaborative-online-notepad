<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

if (!isset($_GET['note_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No note ID']);
    exit();
}

$userId = $_SESSION['user_id'];
$noteId = intval($_GET['note_id']);

$conn = new mysqli("localhost", "root", "", "notepad_db");

$stmt = $conn->prepare("SELECT id, note_title, note_content FROM notes WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $noteId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(['error' => 'Note not found']);
}

$stmt->close();
$conn->close();
