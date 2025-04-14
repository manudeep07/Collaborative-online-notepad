<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit();
}

$conn = new mysqli("localhost", "root", "", "notepad_db");
$userId = $_SESSION['user_id'];
$noteTitle = $_POST['note_title'] ?? '';
$noteContent = $_POST['note_content'] ?? '';
$noteId = isset($_POST['note_id']) ? (int)$_POST['note_id'] : 0;

header('Content-Type: application/json');
if (empty($noteTitle)) {
    echo json_encode(['error' => 'Note title is required']);
    exit();
}

if ($noteId) {
    $stmt = $conn->prepare("UPDATE notes SET note_title = ?, note_content = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ssii", $noteTitle, $noteContent, $noteId, $userId);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'note_id' => $noteId]);
    } else {
        echo json_encode(['error' => 'Note not found or unauthorized']);
    }
} else {
    $stmt = $conn->prepare("INSERT INTO notes (user_id, note_title, note_content, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
    $stmt->bind_param("iss", $userId, $noteTitle, $noteContent);
    $stmt->execute();
    $noteId = $conn->insert_id;
    echo json_encode(['success' => true, 'note_id' => $noteId]);
}

$stmt->close();
$conn->close();
?>