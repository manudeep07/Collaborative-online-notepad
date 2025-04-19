<?php
// PHP code to handle form submission and error messages
$usernameError = '';
$emailError = '';
$passwordError = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validate username
    if (strlen($username) < 3) {
        $usernameError = "Username must be at least 3 characters long.";
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailError = "Invalid email format.";
    }

    // Validate password
    if (strlen($password) < 8) {
        $passwordError = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirmPassword) {
        $passwordError = "Passwords do not match.";
    }

    // If no errors, proceed with registration
    if ($usernameError == '' && $emailError == '' && $passwordError == '') {
        $conn = new mysqli("localhost", "root", "", "notepad_db");

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $emailError = "Username or email already exists.";
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashedPassword);

            if ($stmt->execute()) {
                // Registration successful, redirect to login
                header('Location: login.php?registered=1');
                exit();
            } else {
                $emailError = "Registration failed. Please try again.";
            }
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Collaborative Notepad</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Reset & Base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            height: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #1a4d7a;
            color: #f5f5f5;
            line-height: 1.6;
            display: flex;
            flex-direction: column;
        }
        main {
            flex: 1 0 auto;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Navigation */
        nav {
            background-color: #0e2a47;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .nav-left h1 {
            color: #ffffff;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .nav-right {
            display: flex;
            list-style: none;
            gap: 2rem;
        }
        .nav-right a {
            color: #ffffff;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
            padding-bottom: 0.25rem;
        }
        .nav-right a:hover {
            color: #00bfff;
        }
        .nav-right a.active {
            color: #29c00b;
            border-bottom: 2px solid #29c00b;
        }

        /* Register Form */
        .register-section {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            padding: 1rem;
        }
        .register-form {
            background: linear-gradient(145deg, #0e2a47, #123a62);
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            animation: scaleIn 0.5s ease-out;
            width: 100%;
            max-width: 450px;
        }
        @keyframes scaleIn {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .register-form h2 {
            font-size: 2rem;
            color: #ffffff;
            text-align: center;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }
        .register-form h2 i {
            color: #29c00b;
        }
        .register-form p {
            text-align: center;
            color: #dcdcdc;
            font-size: 1rem;
            margin-bottom: 2rem;
        }

        /* Form Group with Labels Above */
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            color: #ffffff;
            font-size: 1rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            background-color: #1a4d7a;
            border: 2px solid transparent;
            border-radius: 10px;
            color: #ffffff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .form-group input:focus {
            border-color: #00bfff;
            box-shadow: 0 0 8px rgba(0, 191, 255, 0.3);
            outline: none;
        }
        .form-group input::placeholder {
            color: #a0a0a0;
        }

        /* Error Messages */
        .error-message {
            display: block;
            background: rgba(255, 107, 107, 0.2);
            color: #ff6b6b;
            font-size: 0.875rem;
            padding: 0.5rem;
            margin-top: 0.5rem;
            border-radius: 6px;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { transform: translateY(-10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Submit Button */
        button[type="submit"] {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(90deg, #29c00b, #23a00a);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(41, 192, 11, 0.4);
        }
        button[type="submit"]:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        button[type="submit"] .spinner {
            display: none;
            width: 1.2rem;
            height: 1.2rem;
            border: 2px solid #ffffff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-right: 0.5rem;
        }
        button[type="submit"]:disabled .spinner {
            display: inline-block;
        }
        button[type="submit"]:disabled span {
            display: none;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Auth Links */
        .auth-links {
            text-align: center;
            margin-top: 1.5rem;
        }
        .auth-links p {
            color: #dcdcdc;
            font-size: 0.9rem;
        }
        .auth-links a {
            color: #00bfff;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .auth-links a:hover {
            color: #29c00b;
        }

        /* Footer */
        footer {
            background-color: #0e2a47;
            color: #ffffff;
            text-align: center;
            padding: 1rem;
            font-size: 0.875rem;
            flex-shrink: 0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .register-section {
                padding: 1rem;
            }
            .register-form {
                padding: 1.5rem;
            }
            .register-form h2 {
                font-size: 1.75rem;
            }
            .form-group input {
                padding: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <nav>
        <div class="nav-left">
            <h1><i class="fas fa-book-open"></i> Collaborative Notepad</h1>
        </div>
        <ul class="nav-right">
            <li><a href="index.php">Home</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php" class="active">Register</a></li>
        </ul>
    </nav>

    <main class="register-section">
        <div class="register-form">
            <h2><i class="fas fa-user-plus"></i> Create Account</h2>
            <p>Join us to start creating and sharing notes!</p>
            <form action="register.php" method="POST" id="register-form">
                <div class="form-group">
                    <label for="username"><i class="fas fa-user"></i> Username</label>
                    <input type="text" id="username" name="username" required
                           value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>"
                           placeholder="Choose a username">
                    <?php if (!empty($usernameError)): ?>
                        <span class="error-message"><?php echo $usernameError; ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                    <input type="email" id="email" name="email" required
                           value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                           placeholder="Enter your email">
                    <?php if (!empty($emailError)): ?>
                        <span class="error-message"><?php echo $emailError; ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" id="password" name="password" required
                           placeholder="Create a password">
                    <?php if (!empty($passwordError)): ?>
                        <span class="error-message"><?php echo $passwordError; ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="confirm_password"><i class="fas fa-lock"></i> Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required
                           placeholder="Confirm your password">
                </div>
                <button type="submit">
                    <span class="spinner"></span>
                    <span><i class="fas fa-user-plus"></i> Create Account</span>
                </button>
                <div class="auth-links">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <p>© <?php echo date('Y'); ?> Collaborative Notepad. All rights reserved.</p>
    </footer>

   
</body>
</html>