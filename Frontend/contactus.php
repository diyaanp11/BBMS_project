<?php
$page_title = "Contact";
include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Blood Bank Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .contact-container {
            min-height: 80vh;
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }
        .contact-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        .contact-header h1 {
            font-size: 2.8rem;
            color: #d32f2f;
            margin-bottom: 1rem;
        }
        .contact-header p {
            font-size: 1.2rem;
            color: #424141;
            max-width: 800px;
            margin: 0 auto;
        }
        .contact-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        .contact-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }
        .contact-card:hover {
            transform: translateY(-5px);
        }
        .contact-card .profile-icon {
            font-size: 4rem;
            color: #d32f2f;
            margin-bottom: 1rem;
            background: #fde8e8;
            width: 80px;
            height: 80px;
            line-height: 80px;
            border-radius: 50%;
            margin: 0 auto 1rem;
        }
        .contact-card h3 {
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 1.3rem;
        }
        .contact-card p {
            color: #555;
            margin: 0.5rem 0;
            font-size: 0.95rem;
        }
        .contact-card p i {
            color: #d32f2f;
            width: 25px;
        }
        .social-icons {
            margin-top: 1.2rem;
        }
        .social-icons a {
            text-decoration: none;
            margin: 0 10px;
            font-size: 1.5rem;
            color: #555;
            transition: color 0.3s ease;
        }
        .social-icons a:hover {
            color: #d32f2f;
        }

        .faq-section {
            background: white;
            padding: 3rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-top: 2rem;
        }
        .faq-section h2 {
            text-align: center;
            color: #d32f2f;
            margin-bottom: 2rem;
            font-size: 2rem;
        }
        .faq-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }
        .faq-item h4 {
            color: #2E5E4E;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }
        .faq-item p {
            color: #555;
            line-height: 1.6;
            font-size: 0.95rem;
        }

        @media (max-width: 768px) {
            .contact-header h1 { font-size: 2rem; }
            .faq-section { padding: 1.5rem; }
        }
    </style>
</head>
<body>
<div class="contact-container">
    <!-- Header -->
    <div class="contact-header">
        <h1>Contact Us</h1>
        <p>Get in touch with our project team. We'd love to hear from you!</p>
    </div>

    <!-- Contact Cards -->
    <div class="contact-cards">
        <!-- Diya Sharma -->
        <div class="contact-card">
            <div class="profile-icon">
                <i class="fas fa-user-circle"></i>
            </div>
            <h3>Diya Sharma</h3>
            <p><i class="fas fa-envelope"></i> diyaanp11@gmail.com</p>
            <p><i class="fas fa-phone"></i> +977-98XXXXXXX</p>
            <div class="social-icons">
                <a href="https://github.com/diyaanp11"><i class="fab fa-github"></i></a>
                <a href="https://www.facebook.com/diya.n.yaupane.2025"><i class="fab fa-facebook"></i></a>
                <a href="https://www.instagram.com/diya.np_11/"><i class="fab fa-instagram"></i></a>
                <a href="https://x.com/Diyaanp11"><i class="fab fa-twitter"></i></a>
            </div>
        </div>

        <!-- Kabita Pandey -->
        <div class="contact-card">
            <div class="profile-icon">
                <i class="fas fa-user-circle"></i>
            </div>
            <h3>Kabita Pandey</h3>
            <p><i class="fas fa-envelope"></i> kabitapanday02@gmail.com</p>
            <p><i class="fas fa-phone"></i> +977-98XXXXXXX</p>
            <div class="social-icons">
                <a href="#"><i class="fab fa-github"></i></a>
                <a href="https://www.facebook.com/kabita.pandey.670393"><i class="fab fa-facebook"></i></a>
                <a href="https://www.instagram.com/kabita.446/"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="faq-section">
        <h2><i class="fas fa-question-circle"></i> Frequently Asked Questions</h2>
        <div class="faq-grid">
            <div class="faq-item">
                <h4>Who can donate blood?</h4>
                <p>Generally, healthy individuals aged 18-65, weighing at least 50kg, can donate blood. Specific eligibility criteria apply.</p>
            </div>
            <div class="faq-item">
                <h4>How often can I donate?</h4>
                <p>You can donate whole blood every 56 days (8 weeks). Platelet donations can be made every 7 days, up to 24 times per year.</p>
            </div>
            <div class="faq-item">
                <h4>Is blood donation safe?</h4>
                <p>Yes, blood donation is completely safe. We use sterile, single-use equipment and follow strict safety protocols.</p>
            </div>
            <div class="faq-item">
                <h4>How long does donation take?</h4>
                <p>The entire process takes about 45-60 minutes, with the actual donation taking only 8-10 minutes.</p>
            </div>
        </div>
    </div>
</div>

<?php include 'chatbot.php'; ?>
<?php include 'footer.php'; ?>
</body>
</html>