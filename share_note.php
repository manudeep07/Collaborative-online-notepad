<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "notepad_db");
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit();
}

$noteId = $_POST['note_id'] ?? '';
$sharedWithUserId = $_POST['shared_with_user_id'] ?? '';
$permissionLevel = $_POST['permission_level'] ?? '';

if (!is_numeric($noteId) || !is_numeric($sharedWithUserId) || !in_array($permissionLevel, ['view', 'edit'])) {
    header("Location: dashboard.php?error=Invalid input");
    exit();
}

// Verify the note belongs to the user
$stmt = $conn->prepare("SELECT user_id FROM notes WHERE id = ?");
$stmt->bind_param("i", $noteId);
$stmt->execute();
$result = $stmt->get_result();
$note = $result->fetch_assoc();

if (!$note || $note['user_id'] != $userId) {
    header("Location: dashboard.php?error=Unauthorized");
    exit();
}

// Check if already shared
$stmt = $conn->prepare("SELECT id FROM shared_notes WHERE note_id = ? AND shared_with_user_id = ?");
$stmt->bind_param("ii", $noteId, $sharedWithUserId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    header("Location: dashboard.php?error=Note already shared with this user");
    exit();
}

// Share the note
$stmt = $conn->prepare("INSERT INTO shared_notes (note_id, shared_with_user_id, permission_level) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $noteId, $sharedWithUserId, $permissionLevel);
$stmt->execute();

$stmt->close();
$conn->close();

header("Location: dashboard.php?shared=1");
exit();
?>