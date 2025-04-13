<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$conn = new mysqli("localhost", "root", "", "notepad_db");

// Get the form data
$note_id = isset($_POST['note_id']) ? $_POST['note_id'] : null;
$note_title = $_POST['note_title'];
$note_content = $_POST['note_content'];

// Check for duplicate titles
$checkQuery = "SELECT id FROM notes WHERE user_id = ? AND note_title = ? AND id != ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("isi", $userId, $note_title, $note_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Title is not unique
    $_SESSION['error'] = "A note with this title already exists.";
    header("Location: dashboard.php");
    exit();
}

if ($note_id) {
    // Update an existing note
    $stmt = $conn->prepare("UPDATE notes SET note_title = ?, note_content = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("ssi", $note_title, $note_content, $note_id);
} else {
    // Insert a new note
    $stmt = $conn->prepare("INSERT INTO notes (user_id, note_title, note_content, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
    $stmt->bind_param("iss", $userId, $note_title, $note_content);
}

if ($stmt->execute()) {
    // Successfully saved the note
    header("Location: dashboard.php");
} else {
    // Handle error
    $_SESSION['error'] = "There was an error saving the note.";
    header("Location: dashboard.php");
}
$stmt->close();
$conn->close();
?>
