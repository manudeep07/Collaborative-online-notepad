<?php
$emailError = '';
$passwordError = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate Email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailError = "Invalid email format.";
    }

    // If no email error, proceed to check credentials
    if ($emailError == '') {
        // Connect to DB
        $conn = new mysqli("localhost", "root", "", "notepad_db");

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Check if the user exists
        $sql = "SELECT id, password FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // Verify the password
            if (password_verify($password, $row['password'])) {
                // Set session and redirect to dashboard
                session_start();
                $_SESSION['user_id'] = $row['id'];
                header('Location: dashboard.php'); // Redirect to dashboard
                exit();
            } else {
                $passwordError = "Incorrect password.";
            }
        } else {
            $emailError = "No user found with that email.";
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - CollaborativeNotepad</title>
  <link rel="stylesheet" href="css/login.css">
</head>
<body>

  <!-- 🌐 Navigation -->
  <nav>
    <div class="nav-left">
      <h1>📝 Collaborative Notepad</h1>
    </div>
    <ul class="nav-right">
      <li><a href="index.php">Home</a></li>
      <li><a href="about.php">About</a></li>
      <li><a href="login.php" class="active">Login</a></li>
      <li><a href="register.php">Register</a></li>
    </ul>
  </nav>

  <!-- 🔐 Login Form -->
  <main class="login-section">
    <h2>Login to Your Account</h2>
    <form action="login.php" method="POST" class="login-form">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required />
      <span style="color: red;"><?php echo $emailError; ?></span>

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required />
      <span style="color: red;"><?php echo $passwordError; ?></span>

      <button type="submit">Login</button>
      <p>Don't have an account? <a href="register.php">Register here</a>.</p>
    </form>
  </main>

  <!-- 🔻 Footer -->
  <footer>
    <p>&copy; 2025 CollaborativeNotepad. All rights reserved.</p>
  </footer>

</body>
</html>
