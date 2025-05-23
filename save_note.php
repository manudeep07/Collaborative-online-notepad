<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$conn = new mysqli("localhost", "root", "", "notepad_db");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}
$userId = $_SESSION['user_id'];
$noteTitle = $_POST['note_title'] ?? '';
$noteContent = $_POST['note_content'] ?? '';
$noteId = isset($_POST['note_id']) ? (int)$_POST['note_id'] : 0;

header('Content-Type: application/json');
if (empty($noteTitle)) {
    echo json_encode(['error' => 'Note title is required']);
    exit();
}

// Parse Quill delta to get plain text
$decodedContent = json_decode($noteContent, true);
$plainText = '';
if (is_array($decodedContent) && isset($decodedContent['ops'])) {
    foreach ($decodedContent['ops'] as $op) {
        if (isset($op['insert'])) {
            $plainText .= $op['insert'];
        }
    }
} else {
    $plainText = $noteContent; // Fallback if not a delta
}

if ($noteId) {
    // Check if user owns the note or has edit permission
    $stmt = $conn->prepare("SELECT user_id FROM notes WHERE id = ?");
    $stmt->bind_param("i", $noteId);
    $stmt->execute();
    $result = $stmt->get_result();
    $note = $result->fetch_assoc();

    $hasEditPermission = false;
    if ($note && $note['user_id'] == $userId) {
        $hasEditPermission = true; // Owner
    } else {
        $stmt = $conn->prepare("SELECT permission_level FROM shared_notes WHERE note_id = ? AND shared_with_user_id = ?");
        $stmt->bind_param("ii", $noteId, $userId);
        $stmt->execute();
        $sharedResult = $stmt->get_result();
        $shared = $sharedResult->fetch_assoc();
        if ($shared && $shared['permission_level'] === 'edit') {
            $hasEditPermission = true; // Shared user with edit permission
        }
    }

    if (!$hasEditPermission) {
        echo json_encode(['error' => 'Note not found or unauthorized']);
        exit();
    }

    // Update the note
    $stmt = $conn->prepare("UPDATE notes SET note_title = ?, note_content = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("ssi", $noteTitle, $plainText, $noteId);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'note_id' => $noteId]);
    } else {
        echo json_encode(['error' => 'Failed to update note']);
    }
} else {
    // Create new note
    $stmt = $conn->prepare("INSERT INTO notes (user_id, note_title, note_content, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
    $stmt->bind_param("iss", $userId, $noteTitle, $plainText);
    $stmt->execute();
    $noteId = $conn->insert_id;
    echo json_encode(['success' => true, 'note_id' => $noteId]);
}

$stmt->close();
$conn->close();
?>