<?php
// PHP code to handle form submission and error messages
$nameError = '';
$emailError = '';
$passwordError = '';
$confirmPasswordError = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validate Full Name
    if (empty($name)) {
        $nameError = "Full Name is required.";
    }

    // Validate Email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailError = "Invalid email format.";
    }

    // Validate Password
    if (strlen($password) < 8) {
        $passwordError = "Password must be at least 8 characters long.";
    }

    // Validate Confirm Password
    if ($password !== $confirmPassword) {
        $confirmPasswordError = "Passwords do not match.";
    }

    // If no errors, proceed with registration (e.g., insert into database)
    if ($nameError == '' && $emailError == '' && $passwordError == '' && $confirmPasswordError == '') {
        // Hash the password and insert into the database
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Database connection
        $conn = new mysqli("localhost", "root", "", "notepad_db");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Insert user into database
        $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $name, $email, $hashedPassword);

        if ($stmt->execute()) {
            echo "User registered successfully!";
            // Redirect to login page or dashboard
            header('Location: login.php');
            exit();
        } else {
            echo "Error: " . $stmt->error;
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
  <title>Register - CollaborativeNotepad</title>
  <link rel="stylesheet" href="css/signup.css" />
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
      <li><a href="login.php">Login</a></li>
      <li><a href="register.php" class="active">Register</a></li>
    </ul>
  </nav>

  <!-- 📝 Register Form -->
  <main class="register-section">
    <h2>Create Your Account</h2>
    <form action="register.php" method="POST" class="register-form">
      <label for="name">Full Name</label>
      <input type="text" id="name" name="name" value="<?php echo isset($name) ? $name : ''; ?>" required />
      <span style="color: red;"><?php echo $nameError; ?></span>

      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required />
      <span style="color: red;"><?php echo $emailError; ?></span>

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required />
      <span style="color: red;"><?php echo $passwordError; ?></span>

      <label for="confirm_password">Confirm Password</label>
      <input type="password" id="confirm_password" name="confirm_password" required />
      <span style="color: red;"><?php echo $confirmPasswordError; ?></span>

      <button type="submit">Register</button>
      <p>Already have an account? <a href="login.php">Login here</a>.</p>
    </form>
  </main>

  <!-- 🔻 Footer -->
  <footer>
    <p>&copy; 2025 CollaborativeNotepad. All rights reserved.</p>
  </footer>

</body>
</html>
