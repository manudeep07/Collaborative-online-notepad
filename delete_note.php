<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "notepad_db");
$userId = $_SESSION['user_id'];

// Check if note_id is provided
if (!isset($_GET['note_id']) || !is_numeric($_GET['note_id'])) {
    header("Location: dashboard.php");
    exit();
}

$noteId = $_GET['note_id'];

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

// Delete shared notes references
$stmt = $conn->prepare("DELETE FROM shared_notes WHERE note_id = ?");
$stmt->bind_param("i", $noteId);
$stmt->execute();

// Delete the note
$stmt = $conn->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $noteId, $userId);
$stmt->execute();

$stmt->close();
$conn->close();

// Redirect with success message
header("Location: dashboard.php?deleted=1");
exit();
?>