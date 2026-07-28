<?php
$page_title = "Home";
include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Bank Management System - Home</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 4rem 5%;
            background: #fff;
            color: white;
            min-height: 80vh;
        }
        .write {
            flex: 1;
            max-width: 600px;
            padding-right: 2rem;
        }
        .write h1 {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            color: #d32f2f;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
        }
        .write p {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            color: #424141;
        }
        .hero-img {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .hero-img img {
            width: 500px;
            max-width: 100%;
            height: auto;
            object-fit: cover;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }
        .hero-img img:hover {
            transform: translateY(-5px);
        }
        .cta-button {
            display: inline-block;
            background: #ff6b6b;
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }
        .cta-button:hover {
            background: #e23939;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(152, 57, 57, 0.4);
        }
        .features {
            padding: 4rem 5%;
            background: #f8f9fa;
        }
        .features h2 {
            text-align: center;
            color: #d32f2f;
            font-size: 2rem;
            margin-bottom: 2rem;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            text-align: center;
            transition: transform 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .feature-card i {
            font-size: 2.5rem;
            color: #d32f2f;
            margin-bottom: 1rem;
        }
        .feature-card h3 {
            color: #333;
            margin-bottom: 0.5rem;
        }
        .feature-card p {
            color: #666;
            font-size: 0.95rem;
        }
        @media (max-width: 768px) {
            .hero {
                flex-direction: column;
                text-align: center;
                padding: 2rem 1rem;
            }
            .write {
                padding-right: 0;
                margin-bottom: 2rem;
            }
            .write h1 {
                font-size: 2rem;
            }
            .hero-img img {
                width: 100%;
                max-width: 350px;
            }
        }
    </style>
</head>
<body>
    <!-- Hero section -->
    <section class="hero">
        <div class="write">
            <h1>Blood Bank Management System</h1>
            <p>Welcome to our redesigned Blood Bank Management System. This system helps connect blood donors with those in need, ensuring timely access to safe blood.</p>
            <a href="learn.php" class="cta-button">Learn More</a>
        </div>
        <div class="hero-img">
            <img src="homeimg.webp" alt="Blood Bank System">
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <h2>Why Choose Our System?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <i class="fas fa-hand-holding-heart"></i>
                <h3>Easy Donation</h3>
                <p>Register as a donor and schedule blood donations with just a few clicks.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-search"></i>
                <h3>Blood Search</h3>
                <p>Find available blood types quickly with real-time inventory tracking.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-shield-alt"></i>
                <h3>Safe & Secure</h3>
                <p>All medical documents and personal data are securely stored and verified.</p>
            </div>
        </div>
    </section>
</body>
</html>
<?php include 'chatbot.php'; ?>
<?php include 'footer.php'; ?>
