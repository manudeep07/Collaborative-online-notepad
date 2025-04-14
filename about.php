<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Collaborative Notepad</title>
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

        /* About Hero Section */
        .about-hero {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 50vh;
            padding: 2rem;
            animation: fadeIn 0.8s ease-out;
        }
        .about-content {
            text-align: center;
            max-width: 800px;
            animation: scaleIn 0.5s ease-out;
        }
        .about-content h1 {
            font-size: 3rem;
            color: #ffffff;
            margin-bottom: 1rem;
        }
        .about-content h1 span {
            color: #29c00b;
        }
        .about-content p.lead {
            font-size: 1.25rem;
            color: #dcdcdc;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes scaleIn {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        /* Features Section */
        .about-features {
            padding: 4rem 2rem;
            background: linear-gradient(145deg, #0e2a47, #123a62);
        }
        .features-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        .feature-item {
            background: #1a4d7a;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
            text-align: center;
            transition: transform 0.3s ease;
            animation: scaleIn 0.5s ease-out;
        }
        .feature-item:hover {
            transform: translateY(-5px);
        }
        .feature-item i {
            font-size: 2.5rem;
            color: #29c00b;
            margin-bottom: 1rem;
        }
        .feature-item h3 {
            font-size: 1.5rem;
            color: #ffffff;
            margin-bottom: 0.75rem;
        }
        .feature-item p {
            color: #dcdcdc;
            font-size: 1rem;
        }

        /* Call to Action */
        .cta-section {
            padding: 4rem 2rem;
            text-align: center;
            animation: fadeIn 0.8s ease-out;
        }
        .cta-section h2 {
            font-size: 2.5rem;
            color: #ffffff;
            margin-bottom: 1rem;
        }
        .cta-section p {
            font-size: 1.25rem;
            color: #dcdcdc;
            margin-bottom: 2rem;
        }
        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .primary-btn {
            background: linear-gradient(90deg, #29c00b, #23a00a);
            color: #ffffff;
        }
        .primary-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(41, 192, 11, 0.4);
        }
        .secondary-btn {
            background-color: #0e2a47;
            color: #ffffff;
            border: 2px solid #00bfff;
        }
        .secondary-btn:hover {
            background-color: #123a62;
            transform: translateY(-2px);
        }
        .btn:focus {
            outline: 3px solid #00bfff;
            outline-offset: 2px;
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
            nav {
                flex-direction: column;
                gap: 1rem;
            }
            .nav-right {
                gap: 1rem;
                flex-wrap: wrap;
                justify-content: center;
            }
            .about-hero {
                padding: 1rem;
            }
            .about-content h1 {
                font-size: 2rem;
            }
            .about-content p.lead {
                font-size: 1rem;
            }
            .about-features {
                padding: 2rem 1rem;
            }
            .feature-item {
                padding: 1.5rem;
            }
            .cta-section {
                padding: 2rem 1rem;
            }
            .cta-section h2 {
                font-size: 2rem;
            }
            .cta-section p {
                font-size: 1rem;
            }
            .cta-buttons {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav>
        <div class="nav-left">
            <h1><i class="fas fa-book-open"></i> Collaborative Notepad</h1>
        </div>
        <ul class="nav-right">
            <li><a href="index.php">Home</a></li>
            <li><a href="about.php" class="active">About</a></li>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
        </ul>
    </nav>

    <main>
        <!-- About Hero Section -->
        <section class="about-hero">
            <div class="about-content">
                <h1>About <span>Collaborative Notepad</span></h1>
                <p class="lead">A powerful real-time collaborative notepad that helps teams work together seamlessly.</p>
            </div>
        </section>

        <!-- Features Section -->
        <section class="about-features">
            <div class="features-container">
                <div class="feature-item">
                    <i class="fas fa-users"></i>
                    <h3>Team Collaboration</h3>
                    <p>Work together in real-time with your team members from anywhere in the world.</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Secure & Private</h3>
                    <p>Your notes are encrypted and protected. You control who can access your content.</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-sync"></i>
                    <h3>Real-time Updates</h3>
                    <p>See changes instantly as they happen. No need to refresh or sync manually.</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-palette"></i>
                    <h3>Rich Text Formatting</h3>
                    <p>Format your notes with bold, italic, lists, and more using our intuitive editor.</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-history"></i>
                    <h3>Version History</h3>
                    <p>Track changes and revert to previous versions of your notes when needed.</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-mobile-alt"></i>
                    <h3>Mobile Friendly</h3>
                    <p>Access your notes from any device with our responsive design.</p>
                </div>
            </div>
        </section>

        <!-- Call to Action -->
        <section class="cta-section">
            <h2>Ready to Get Started?</h2>
            <p>Join thousands of teams already using Collaborative Notepad</p>
            <div class="cta-buttons">
                <a href="register.php" class="btn primary-btn"><i class="fas fa-user-plus"></i> Create Free Account</a>
                <a href="login.php" class="btn secondary-btn"><i class="fas fa-sign-in-alt"></i> Sign In</a>
            </div>
        </section>
    </main>

    <footer>
        <p>© <?php echo date('Y'); ?> Collaborative Notepad. All rights reserved.</p>
    </footer>
</body>
</html>