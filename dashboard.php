<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "notepad_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$userId = $_SESSION['user_id'];

// Get username
$userQuery = $conn->prepare("SELECT username FROM users WHERE id = ?");
$userQuery->bind_param("i", $userId);
$userQuery->execute();
$userResult = $userQuery->get_result();
if ($userResult->num_rows === 0) {
    session_destroy();
    header("Location: login.php");
    exit();
}
$user = $userResult->fetch_assoc();
$username = $user['username'];

// Get user's notes
$stmt = $conn->prepare("SELECT id, note_title, created_at, updated_at FROM notes WHERE user_id = ? ORDER BY updated_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userNotes = $stmt->get_result();

// Get shared notes
$stmt = $conn->prepare("
    SELECT n.id, n.note_title, n.created_at, n.updated_at, u.username as owner_name, sn.permission_level 
    FROM shared_notes sn 
    JOIN notes n ON sn.note_id = n.id 
    JOIN users u ON n.user_id = u.id 
    WHERE sn.shared_with_user_id = ? 
    ORDER BY n.updated_at DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$sharedNotes = $stmt->get_result();

// Get all users for sharing
$usersQuery = $conn->query("SELECT id, username FROM users WHERE id != $userId");
$users = $usersQuery->fetch_all(MYSQLI_ASSOC);

// Check for messages
$message = '';
if (isset($_GET['deleted'])) {
    $message = 'Note deleted successfully!';
} elseif (isset($_GET['shared'])) {
    $message = 'Note shared successfully!';
} elseif (isset($_GET['error'])) {
    $message = 'Error: ' . htmlspecialchars($_GET['error']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Collaborative Notepad</title>
    <link rel="stylesheet" href="https://cdn.quilljs.com/1.3.6/quill.snow.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #1a4d7a; color: #f5f5f5; line-height: 1.6; display: flex; flex-direction: column; }
        main { flex: 1 0 auto; display: flex; justify-content: center; align-items: stretch; padding: 2rem; }
        nav { background-color: #0e2a47; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); }
        .nav-left h1 { color: #ffffff; font-size: 1.5rem; display: flex; align-items: center; gap: 0.5rem; }
        .nav-right { display: flex; list-style: none; gap: 2rem; align-items: center; }
        .nav-right a, .nav-right span { color: #ffffff; text-decoration: none; font-weight: 500; transition: color 0.3s ease; padding-bottom: 0.25rem; }
        .nav-right a:hover { color: #00bfff; }
        .nav-right .welcome { font-style: italic; }
        .dashboard { display: flex; flex: 1; max-width: 1200px; background: linear-gradient(145deg, #0e2a47, #123a62); border-radius: 16px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4); overflow: hidden; animation: scaleIn 0.5s ease-out; }
        @keyframes scaleIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .sidebar { width: 300px; background: linear-gradient(145deg, #0e2a47, #123a62); border-right: 1px solid rgba(255, 255, 255, 0.1); display: flex; flex-direction: column; overflow-y: auto; }
        .sidebar-section { padding: 1.5rem; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        .new-note-btn { width: 100%; padding: 0.75rem; background: linear-gradient(90deg, #29c00b, #23a00a); color: #ffffff; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; transition: all 0.3s ease; }
        .new-note-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(41, 192, 11, 0.4); }
        .sidebar-section h2 { color: #ffffff; font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
        .notes, .shared-notes { list-style: none; }
        .notes li, .shared-notes li { padding: 0.75rem; border-radius: 10px; background-color: #1a4d7a; margin-bottom: 0.5rem; cursor: pointer; display: flex; align-items: center; gap: 0.75rem; border: 2px solid transparent; transition: all 0.3s ease; animation: slideIn 0.3s ease; }
        .notes li:hover, .shared-notes li:hover { border-color: #00bfff; transform: translateX(5px); }
        @keyframes slideIn { from { transform: translateY(-10px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .note-info { flex: 1; display: flex; flex-direction: column; gap: 0.25rem; }
        .note-title { font-weight: 500; color: #ffffff; font-size: 1rem; }
        .note-owner, .note-date { font-size: 0.875rem; color: #dcdcdc; }
        .notes li i, .shared-notes li i { color: #29c00b; font-size: 1.25rem; }
        .delete-note-btn { background: linear-gradient(90deg, #dc3545, #c82333); color: #ffffff; border: none; border-radius: 8px; padding: 0.5rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; }
        .delete-note-btn:hover { transform: scale(1.1); box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); }
        .delete-note-btn:focus { outline: 3px solid #00bfff; outline-offset: 2px; }
        .note-actions { display: flex; flex-direction: column; gap: 0.75rem; }
        .action-btn { width: 100%; padding: 0.75rem; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; transition: all 0.3s ease; }
        .save-btn { background: linear-gradient(90deg, #29c00b, #23a00a); color: #ffffff; }
        .share-btn { background: linear-gradient(90deg, #00bfff, #0099cc); color: #ffffff; }
        .action-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2); }
        .action-btn:focus { outline: 3px solid #00bfff; outline-offset: 2px; }
        .main-content { flex: 1; display: flex; flex-direction: column; padding: 1.5rem; background: #1a4d7a; }
        .note-title-input { width: 100%; padding: 0.75rem 1rem; background-color: #123a62; border: 2px solid transparent; border-radius: 10px; color: #ffffff; font-size: 1.5rem; font-weight: 500; margin-bottom: 1rem; transition: all 0.3s ease; }
        .note-title-input:focus { border-color: #00bfff; box-shadow: 0 0 8px rgba(0, 191, 255, 0.3); outline: none; }
        .note-title-input::placeholder { color: #a0a0a0; }
        #editor { flex: 1; border-radius: 0 0 10px 10px; overflow: hidden; }
        .ql-toolbar.ql-snow { border: 2px solid transparent; border-radius: 10px 10px 0 0; background: linear-gradient(145deg, #0e2a47, #123a62); padding: 0.75rem; }
        .ql-container.ql-snow { border: 2px solid transparent; border-top: none; border-radius: 0 0 10px 10px; background-color: #123a62; color: #ffffff; }
        .ql-editor { min-height: calc(100vh - 300px); font-size: 1rem; }
        .ql-snow .ql-picker, .ql-snow .ql-icon-picker { color: #ffffff; }
        .ql-snow .ql-stroke { stroke: #ffffff; }
        .ql-snow .ql-fill { fill: #ffffff; }
        .ql-snow .ql-picker-options { background-color: #0e2a47; color: #ffffff; }
        .ql-snow .ql-toolbar button, .ql-snow .ql-toolbar .ql-picker-label { transition: all 0.3s ease; border-radius: 4px; padding: 6px; }
        .ql-snow .ql-toolbar button:hover, .ql-snow .ql-toolbar .ql-picker-label:hover { transform: scale(1.1); background: linear-gradient(90deg, #00bfff, #0099cc); box-shadow: 0 0 8px rgba(0, 191, 255, 0.5); }
        .ql-snow .ql-toolbar button.ql-active, .ql-snow .ql-toolbar .ql-picker-label.ql-active { transform: scale(1.05); background: linear-gradient(90deg, #29c00b, #23a00a); box-shadow: 0 0 8px rgba(41, 192, 11, 0.5); }
        .ql-snow .ql-picker-item:hover { background-color: #00bfff; color: #ffffff; }
        .ql-snow .ql-toolbar:hover { border-color: #00bfff; box-shadow: 0 0 10px rgba(0, 191, 255, 0.3); }
        .ql-snow .ql-toolbar button[title]:hover:after, .ql-snow .ql-toolbar .ql-picker-label[title]:hover:after { content: attr(title); position: absolute; top: 100%; left: 50%; transform: translateX(-50%); background: #0e2a47; color: #ffffff; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; white-space: nowrap; z-index: 10; margin-top: 4px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3); }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: #1a4d7a; padding: 2rem; border-radius: 16px; max-width: 400px; width: 90%; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4); animation: fadeIn 0.3s ease; }
        .modal-content h2 { color: #ffffff; margin-bottom: 1rem; }
        .modal-content select, .modal-content input { width: 100%; padding: 0.75rem; margin-bottom: 1rem; background: #123a62; border: 2px solid transparent; border-radius: 10px; color: #ffffff; font-size: 1rem; }
        .modal-content select:focus, .modal-content input:focus { border-color: #00bfff; outline: none; }
        .modal-content button { width: 100%; padding: 0.75rem; background: linear-gradient(90deg, #29c00b, #23a00a); color: #ffffff; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; }
        .modal-content button:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(41, 192, 11, 0.4); }
        .close-btn { background: linear-gradient(90deg, #dc3545, #c82333); margin-top: 0.5rem; }
        .message { background: linear-gradient(90deg, #29c00b, #23a00a); color: #ffffff; padding: 1rem; margin-bottom: 1rem; border-radius: 10px; text-align: center; }
        .message.error { background: linear-gradient(90deg, #dc3545, #c82333); }
        footer { background-color: #0e2a47; color: #ffffff; text-align: center; padding: 1rem; font-size: 0.875rem; flex-shrink: 0; }
        @media (max-width: 768px) { main { padding: 1rem; } .dashboard { flex-direction: column; border-radius: 10px; } .sidebar { width: 100%; max-height: 300px; } .main-content { min-height: calc(100vh - 400px); } .note-actions { flex-direction: row; flex-wrap: wrap; } .action-btn { flex: 1; min-width: 120px; } .note-title-input { font-size: 1.25rem; } nav { flex-direction: column; gap: 1rem; } .nav-right { gap: 1rem; flex-wrap: wrap; justify-content: center; } }
    </style>
</head>
<body>
    <nav>
        <div class="nav-left">
            <h1><i class="fas fa-book-open"></i> Collaborative Notepad</h1>
        </div>
        <ul class="nav-right">
            <li><a href="dashboard.php">Home</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="logout.php">Logout</a></li>
            <li><span class="welcome">Welcome, <?php echo htmlspecialchars($username); ?></span></li>
        </ul>
    </nav>

    <main>
        <div class="dashboard">
            <div class="sidebar">
                <div class="sidebar-section">
                    <button class="new-note-btn"><i class="fas fa-plus"></i> Create New Note</button>
                </div>
                <div class="sidebar-section">
                    <h2><i class="fas fa-file-alt"></i> My Notes</h2>
                    <?php if ($message): ?>
                        <div class="message <?php echo strpos($message, 'Error') === 0 ? 'error' : ''; ?>">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    <ul class="notes">
                        <?php while ($note = $userNotes->fetch_assoc()): ?>
                            <li data-note-id="<?php echo $note['id']; ?>">
                                <i class="fas fa-file-lines"></i>
                                <div class="note-info">
                                    <span class="note-title"><?php echo htmlspecialchars($note['note_title']); ?></span>
                                    <span class="note-date"><?php echo date('M d, Y', strtotime($note['updated_at'])); ?></span>
                                </div>
                                <a href="delete_note.php?note_id=<?php echo $note['id']; ?>" class="delete-note-btn" onclick="return confirm('Are you sure you want to delete this note?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
                <div class="sidebar-section">
                    <h2><i class="fas fa-share-alt"></i> Shared With Me</h2>
                    <ul class="shared-notes">
                        <?php while ($note = $sharedNotes->fetch_assoc()): ?>
                            <li data-note-id="<?php echo $note['id']; ?>" data-permission="<?php echo $note['permission_level']; ?>">
                                <i class="fas fa-share-nodes"></i>
                                <div class="note-info">
                                    <span class="note-title"><?php echo htmlspecialchars($note['note_title']); ?></span>
                                    <span class="note-owner">by <?php echo htmlspecialchars($note['owner_name']); ?></span>
                                    <span class="note-date"><?php echo date('M d, Y', strtotime($note['updated_at'])); ?></span>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
                <div class="sidebar-section note-actions">
                    <button class="action-btn save-btn"><i class="fas fa-save"></i> Save Note</button>
                    <button class="action-btn share-btn"><i class="fas fa-share-alt"></i> Share Note</button>
                </div>
            </div>
            <div class="main-content">
                <input type="text" id="note-title" class="note-title-input" placeholder="Enter note title...">
                <div id="editor"></div>
            </div>
        </div>
    </main>

    <div class="modal" id="shareModal">
        <div class="modal-content">
            <h2>Share Note</h2>
            <form action="share_note.php" method="POST">
                <input type="hidden" name="note_id" id="shareNoteId">
                <select name="shared_with_user_id" required>
                    <option value="">Select User</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="permission_level" required>
                    <option value="view">View Only</option>
                    <option value="edit">Edit</option>
                </select>
                <button type="submit">Share</button>
                <button type="button" class="close-btn" onclick="closeModal()">Cancel</button>
            </form>
        </div>
    </div>

    <footer>
        <p>© <?php echo date('Y'); ?> Collaborative Notepad. All rights reserved.</p>
    </footer>

    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script>
        var quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'align': [] }], // Added text alignment options
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link', 'image'],
                    ['clean'],
                    ['undo', 'redo'] // Added undo/redo buttons
                ],
                history: {
                    delay: 1000,
                    maxStack: 500,
                    userOnly: true
                }
            }
        });

        // Add custom undo/redo icons
        Quill.import('ui/icons')['undo'] = '<i class="fas fa-undo"></i>';
        Quill.import('ui/icons')['redo'] = '<i class="fas fa-redo"></i>';

        let currentNoteId = null;
        let currentPermission = 'edit';
        let ws = null;

        function connectWebSocket() {
            ws = new WebSocket('ws://localhost:8080');
            ws.onopen = () => console.log('WebSocket connected');
            ws.onmessage = (event) => {
                const data = JSON.parse(event.data);
                if (data.noteId == currentNoteId && data.userId !== '<?php echo $userId; ?>' && currentPermission === 'edit') {
                    quill.updateContents(data.delta);
                }
            };
            ws.onclose = () => {
                console.log('WebSocket disconnected, reconnecting...');
                setTimeout(connectWebSocket, 1000);
            };
            ws.onerror = (error) => console.error('WebSocket error:', error);
        }
        connectWebSocket();

        quill.on('text-change', (delta, oldDelta, source) => {
            if (source === 'user' && currentNoteId && ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({
                    noteId: currentNoteId,
                    delta: delta,
                    userId: '<?php echo $userId; ?>'
                }));
            }
        });

        document.querySelectorAll('.notes li, .shared-notes li').forEach(note => {
            note.addEventListener('click', function(e) {
                if (e.target.closest('.delete-note-btn')) return;
                const noteId = this.dataset.noteId;
                const permission = this.dataset.permission || 'edit';
                currentNoteId = noteId;
                currentPermission = permission;
                fetch(`get_note.php?note_id=${noteId}`)
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            alert(data.error);
                            return;
                        }
                        document.getElementById('note-title').value = data.note_title;
                        quill.setContents(JSON.parse(data.note_content));
                        quill.enable(permission === 'edit');
                        document.querySelector('.save-btn').style.display = permission === 'edit' ? 'block' : 'none';
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        alert('Failed to load note. Check console for details.');
                    });
            });
        });

        document.querySelector('.save-btn').addEventListener('click', function() {
            const noteTitle = document.getElementById('note-title').value;
            const noteContent = JSON.stringify(quill.getContents());
            if (!noteTitle) {
                alert('Note title is required!');
                return;
            }
            fetch('save_note.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `note_title=${encodeURIComponent(noteTitle)}¬e_content=${encodeURIComponent(noteContent)}${currentNoteId ? '¬e_id=' + currentNoteId : ''}`
            })
            .then(response => {
                if (!response.ok) throw new Error('Server error');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Note saved successfully!');
                    if (data.note_id && !currentNoteId) currentNoteId = data.note_id;
                    location.reload();
                } else {
                    alert('Error saving note: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Save error:', error);
                alert('Failed to save note. Check console for details or ensure server is running.');
            });
        });

        document.querySelector('.new-note-btn').addEventListener('click', function() {
            document.getElementById('note-title').value = '';
            quill.setContents([]);
            quill.enable(true);
            currentNoteId = null;
            currentPermission = 'edit';
            document.querySelector('.save-btn').style.display = 'block';
        });

        document.querySelector('.share-btn').addEventListener('click', function() {
            if (!currentNoteId) {
                alert('Please select a note to share.');
                return;
            }
            document.getElementById('shareNoteId').value = currentNoteId;
            document.getElementById('shareModal').style.display = 'flex';
        });

        function closeModal() {
            document.getElementById('shareModal').style.display = 'none';
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>