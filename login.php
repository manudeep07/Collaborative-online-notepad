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
                header('Location: dashboard.php');
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Collaborative Notepad</title>
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

        /* Navigation (Unchanged) */
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

        /* Login Form */
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            padding: 1rem;
        }
        .login-box {
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
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header h1 {
            font-size: 2rem;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }
        .login-header h1 i {
            color: #29c00b;
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
            .login-container {
                padding: 1rem;
            }
            .login-box {
                padding: 1.5rem;
            }
            .login-header h1 {
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
            <li><a href="login.php" class="active">Login</a></li>
            <li><a href="register.php">Register</a></li>
        </ul>
    </nav>

    <main class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1><i class="fas fa-lock"></i> Login</h1>
            </div>
            <div class="login-content">
                <form action="login.php" method="POST" id="login-form">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" 
                               placeholder="Enter your email">
                        <?php if (!empty($emailError)): ?>
                            <span class="error-message"><?php echo $emailError; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Enter your password">
                        <?php if (!empty($passwordError)): ?>
                            <span class="error-message"><?php echo $passwordError; ?></span>
                        <?php endif; ?>
                    </div>
                    <button type="submit">
                        <span class="spinner"></span>
                        <span><i class="fas fa-sign-in-alt"></i> Login</span>
                    </button>
                    <div class="auth-links">
                        <p>Don't have an account? <a href="register.php">Register here</a></p>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer>
        <p>© <?php echo date('Y'); ?> Collaborative Notepad. All rights reserved.</p>
    </footer>

   
</body>
</html>