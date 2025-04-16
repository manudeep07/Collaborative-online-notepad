<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit();
}

$conn = new mysqli("localhost", "root", "", "notepad_db");
$userId = $_SESSION['user_id'];
$noteId = isset($_GET['note_id']) ? (int)$_GET['note_id'] : 0;

$stmt = $conn->prepare("
    SELECT n.id, n.note_title, n.note_content 
    FROM notes n 
    LEFT JOIN shared_notes sn ON n.id = sn.note_id 
    WHERE n.id = ? AND (n.user_id = ? OR sn.shared_with_user_id = ?)
");
$stmt->bind_param("iii", $noteId, $userId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$note = $result->fetch_assoc();

header('Content-Type: application/json');
if ($note) {
    // Convert plain text back to Quill delta for editor compatibility
    $delta = ['ops' => [['insert' => $note['note_content']]]];
    echo json_encode([
        'note_title' => $note['note_title'],
        'note_content' => json_encode($delta)
    ]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Note not found or unauthorized']);
}
$stmt->close();
$conn->close();
?>