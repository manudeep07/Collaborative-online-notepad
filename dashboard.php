<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$conn = new mysqli("localhost", "root", "", "notepad_db");

// Handle deleting a note
if (isset($_GET['delete_note'])) {
    $note_id = $_GET['delete_note'];
    // Prepare a delete statement
    $stmt = $conn->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $note_id, $userId);
    $stmt->execute();
    $stmt->close();
    // Redirect to the dashboard after deleting
    header("Location: dashboard.php");
    exit();
}

// Get the user info (name)
$result = $conn->query("SELECT name FROM users WHERE id = $userId");
$user = $result->fetch_assoc();

// Get the user's notes
$notes = $conn->query("SELECT id, note_title FROM notes WHERE user_id = $userId ORDER BY updated_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard - Collaborative Notepad</title>
    <link rel="stylesheet" href="css/dashboard.css" />
</head>
<body>
    <!-- Navbar -->
    <nav>
        <div class="nav-left">
            <h1>📝 Collaborative Notepad</h1>
        </div>
        <ul class="nav-right">
            <li><a href="#">Welcome, <?php echo htmlspecialchars($user['name']); ?></a></li>
            <li class="log-out"><a href="login.php">Logout</a></li>
        </ul>
    </nav>

    <!-- Dashboard Layout -->
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <button class="new-note-btn" onclick="openNewNote()">+ Create New Note</button>
            <ul class="notes-list">
                <?php while ($note = $notes->fetch_assoc()): ?>
                    <li class="note-title" onclick="openNote(<?php echo $note['id']; ?>)">
                        <?php echo htmlspecialchars($note['note_title']); ?>
                        <!-- Delete Button -->
                        <a href="dashboard.php?delete_note=<?php echo $note['id']; ?>" class="delete-btn">Delete</a>
                    </li>
                <?php endwhile; ?>
            </ul>
        </aside>

        <!-- Main Editor Area -->
        <main class="editor-area">
            <form action="save_note.php" method="POST" id="noteForm">
                <input type="hidden" name="note_id" id="note_id" value="">
                <input type="text" class="title-input" name="note_title" id="note_title" placeholder="Note Title" required />
                <div class="toolbar">
                    <button type="button" onclick="execCmd('bold')"><b>B</b></button>
                    <button type="button" onclick="execCmd('italic')"><i>I</i></button>
                    <button type="button" onclick="execCmd('underline')"><u>U</u></button>
                    <button type="button" onclick="execCmd('insertUnorderedList')">
                        <img src="text-bullet-list-svgrepo-com.svg" alt="bullet-point" height="19px" width="18px">
                    </button>
                    <button type="button" onclick="execCmd('insertOrderedList')">
                        <img src="number-list-svgrepo-com.svg" alt="numberlist" height="29px" width="19px">
                    </button>
                    <input type="color" onchange="execCmdArg('foreColor', this.value)" title="Text Color" />
                </div>

                <div class="note-body" contenteditable="true" id="note_body" placeholder="Write your notes here...">
                  <textarea name="note_content" id="note_content" style="display: none;"></textarea>
                </div>
                
                <button type="submit" class="save-btn" onclick="syncNoteContent()">Save</button>
            </form>
        </main>
    </div>

    <script>
        // Function to handle rich text editing commands
        function execCmd(command) {
            document.execCommand(command, false, null);
        }

        function execCmdArg(command, arg) {
            document.execCommand(command, false, arg);
        }

        // Sync the content from the editor to the hidden textarea before submitting
        function syncNoteContent() {
            var noteContent = document.getElementById('note_body').innerHTML;
            document.getElementById('note_content').value = noteContent;
        }

        // Open a new note (reset form)
        function openNewNote() {
            document.getElementById('note_id').value = '';
            document.getElementById('note_title').value = '';
            document.getElementById('note_body').innerHTML = '';
        }

        // Open an existing note for editing
        function openNote(noteId) {
            fetch('get_note.php?note_id=' + noteId)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('note_id').value = data.id;
                    document.getElementById('note_title').value = data.note_title;
                    document.getElementById('note_body').innerHTML = data.note_content;
                });
        }
    </script>
</body>
</html>
