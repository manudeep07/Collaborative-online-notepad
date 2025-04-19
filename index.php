<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Collaborative Notepad</title>
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

    /* Hero Section */
    .hero {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 60vh;
      padding: 2rem;
      animation: fadeIn 0.8s ease-out;
      background: linear-gradient(145deg, #1a4d7a, #1a4d7a);
    }
    .hero-content {
      text-align: center;
      max-width: 800px;
      animation: scaleIn 0.5s ease-out;
    }
    .hero-content h1 {
      font-size: 3.5rem;
      margin-bottom: 1.5rem;
      color: #ffffff;
    }
    .hero-content h1 span {
      color: #29c00b;
    }
    .hero-content p {
      font-size: 1.25rem;
      color: #dcdcdc;
      margin-bottom: 2rem;
    }
    .hero-buttons {
      display: flex;
      justify-content: center;
      gap: 1.5rem;
    }
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 1rem 2rem;
      border-radius: 12px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    .primary-btn {
      background: linear-gradient(90deg, #29c00b, #23a00a);
      color: #ffffff;
      box-shadow: 0 4px 15px rgba(41, 192, 11, 0.3);
    }
    .primary-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(41, 192, 11, 0.5);
    }
    .secondary-btn {
      background-color: #0e2a47;
      color: #ffffff;
      border: 2px solid #00bfff;
    }
    .secondary-btn:hover {
      background-color: #123a62;
      transform: translateY(-3px);
    }
    .btn:focus {
      outline: 3px solid #00bfff;
      outline-offset: 2px;
    }
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    @keyframes scaleIn {
      from { transform: scale(0.9); opacity: 0; }
      to { transform: scale(1); opacity: 1; }
    }

    /* Carousel Section */
    .carousel-section {
      padding: 4rem 2rem;
      background: #123a62;
      text-align: center;
    }
    .carousel-section h2 {
      font-size: 2.5rem;
      color: #ffffff;
      margin-bottom: 2rem;
    }
    .carousel {
      position: relative;
      max-width: 800px;
      margin: 0 auto;
      overflow: hidden;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
    }
    .carousel-images {
      display: flex;
      width: 200%;
      transition: transform 0.5s ease;
    }
    .carousel-images input {
      display: none;
    }
    .carousel-slide {
      width: 100%;
      position: relative;
    }
    .carousel-slide img {
      width: 100%;
      height: 400px;
      object-fit: cover;
      display: block;
      cursor: pointer;
      transition: opacity 0.3s ease;
    }
    .carousel-slide img:hover {
      opacity: 0.9;
    }
    .carousel-caption {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      background: rgba(0, 0, 0, 0.7);
      color: #ffffff;
      padding: 1.2rem;
      font-size: 1.1rem;
      text-align: center;
    }
    .carousel-dots {
      position: absolute;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      gap: 10px;
    }
    .dot {
      width: 12px;
      height: 12px;
      background: #ffffff;
      border-radius: 50%;
      cursor: pointer;
      opacity: 0.5;
      transition: opacity 0.3s ease;
    }
    .dot:hover, .dot.active {
      opacity: 1;
    }
    @keyframes slide {
      0%, 40% { transform: translateX(0); }
      50%, 90% { transform: translateX(-50%); }
      100% { transform: translateX(0); }
    }
    .carousel-images.auto-slide {
      animation: slide 10s infinite;
    }
    @media (max-width: 768px) {
      .carousel-slide img {
        height: 250px;
      }
      .carousel-caption {
        font-size: 0.9rem;
        padding: 0.8rem;
      }
    }

    /* Features Section */
    .features {
      padding: 4rem 2rem;
      text-align: center;
      background: linear-gradient(145deg, #0e2a47, #123a62);
    }
    .features h2 {
      font-size: 2.5rem;
      color: #ffffff;
      margin-bottom: 3rem;
    }
    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 2rem;
      max-width: 1200px;
      margin: 0 auto;
    }
    .feature-card {
      background: #1a4d7a;
      padding: 2.5rem;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      animation: scaleIn 0.5s ease-out;
    }
    .feature-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4);
    }
    .feature-card i {
      font-size: 3rem;
      color: #29c00b;
      margin-bottom: 1.5rem;
    }
    .feature-card h3 {
      font-size: 1.75rem;
      color: #ffffff;
      margin-bottom: 1rem;
    }
    .feature-card p {
      color: #dcdcdc;
      font-size: 1.1rem;
    }

    /* Testimonials Section */
    .testimonials {
      padding: 4rem 2rem;
      text-align: center;
      background: #1a4d7a;
    }
    .testimonials h2 {
      font-size: 2.5rem;
      color: #ffffff;
      margin-bottom: 3rem;
    }
    .testimonials-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 2rem;
      max-width: 1200px;
      margin: 0 auto;
    }
    .testimonial-card {
      background: #0e2a47;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
      transition: transform 0.3s ease;
      animation: scaleIn 0.5s ease-out;
    }
    .testimonial-card:hover {
      transform: translateY(-5px);
    }
    .testimonial-card p {
      color: #dcdcdc;
      font-size: 1rem;
      margin-bottom: 1rem;
    }
    .testimonial-card h4 {
      color: #29c00b;
      font-size: 1.2rem;
      font-weight: 600;
    }

    /* Footer */
    footer {
      background-color: #0e2a47;
      color: #ffffff;
      text-align: center;
      padding: 1.5rem;
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
      .hero-content h1 {
        font-size: 2.5rem;
      }
      .hero-content p {
        font-size: 1rem;
      }
      .hero-buttons {
        flex-direction: column;
        gap: 1rem;
      }
      .features, .carousel-section, .testimonials {
        padding: 2rem 1rem;
      }
      .features h2, .carousel-section h2, .testimonials h2 {
        font-size: 2rem;
      }
      .feature-card, .testimonial-card {
        padding: 1.5rem;
      }
      .carousel {
        max-width: 100%;
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
      <li><a href="index.php" class="active">Home</a></li>
      <li><a href="about.php">About</a></li>
      <li><a href="login.php">Login</a></li>
      <li><a href="register.php">Register</a></li>
    </ul>
  </nav>

  <main>
    <!-- Hero Section -->
    <section class="hero">
      <div class="hero-content">
        <h1>Welcome to <span>Collaborative Notepad</span></h1>
        <p>The simplest way to write, share, and edit notes with your team in real-time.</p>
        <div class="hero-buttons">
          <a href="register.php" class="btn primary-btn"><i class="fas fa-user-plus"></i> Get Started</a>
          <a href="about.php" class="btn secondary-btn"><i class="fas fa-info-circle"></i> Learn More</a>
        </div>
      </div>
    </section>

    <!-- Carousel Section -->
    <section class="carousel-section">
      <h2>Collaboration in Action</h2>
      <div class="carousel">
        <div class="carousel-images auto-slide">
          <div class="carousel-slide">
            <img src="img.jpg" alt="Team Collaboration 1">
            <div class="carousel-caption">Brainstorming ideas together in real-time.</div>
          </div>
          <div class="carousel-slide">
            <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80" alt="Team Collaboration 2">
            <div class="carousel-caption">Collaborating on projects with ease.</div>
          </div>
        </div>
        
      </div>
    </section>

    <!-- Features Section -->
    <section class="features">
      <h2>Why Choose Us?</h2>
      <div class="features-grid">
        <div class="feature-card">
          <i class="fas fa-edit"></i>
          <h3>Rich Text Editor</h3>
          <p>Format your notes with our powerful rich text editor.</p>
        </div>
        <div class="feature-card">
          <i class="fas fa-share-alt"></i>
          <h3>Easy Sharing</h3>
          <p>Share notes with team members in just one click.</p>
        </div>
        <div class="feature-card">
          <i class="fas fa-sync"></i>
          <h3>Real-time Updates</h3>
          <p>See changes instantly as your team collaborates.</p>
        </div>
        
      </div>
    </section>

   
    
  </main>

  <footer>
    <p>© <?php echo date('Y'); ?> Collaborative Notepad. All rights reserved.</p>
  </footer>

  <script>
    const carouselImages = document.querySelector('.carousel-images');
    const dots = document.querySelectorAll('.carousel-dots .dot');
    let currentSlide = 0;
    let autoSlide = true;

    // Function to show a specific slide
    function showSlide(index) {
      carouselImages.style.transform = `translateX(-${index * 50}%)`;
      dots.forEach(dot => dot.classList.remove('active'));
      dots[index].classList.add('active');
      currentSlide = index;
    }

    // Handle image tap/click
    document.querySelectorAll('.carousel-slide img').forEach(img => {
      img.addEventListener('click', () => {
        autoSlide = false;
        carouselImages.classList.remove('auto-slide');
        currentSlide = (currentSlide + 1) % 2; // Cycle to next slide (2 images)
        showSlide(currentSlide);
        setTimeout(() => {
          autoSlide = true;
          carouselImages.classList.add('auto-slide');
        }, 5000); // Resume auto-slide after 5 seconds
      });
    });

    // Handle dot clicks
    dots.forEach(dot => {
      dot.addEventListener('click', () => {
        autoSlide = false;
        carouselImages.classList.remove('auto-slide');
        const slideIndex = parseInt(dot.getAttribute('data-slide'));
        showSlide(slideIndex);
        setTimeout(() => {
          autoSlide = true;
          carouselImages.classList.add('auto-slide');
        }, 5000); // Resume auto-slide after 5 seconds
      });
    });

    // Sync dots with auto-slide
    setInterval(() => {
      if (autoSlide) {
        currentSlide = (currentSlide + 1) % 2;
        showSlide(currentSlide);
      }
    }, 10000); // Match CSS animation duration
  </script>
</body>
</html>