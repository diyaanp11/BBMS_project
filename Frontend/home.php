<?php
$page_title = "Home";
include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Bank System</title>
</head>
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
        color: #424141ff;
    }
    .hero-img {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .hero-img img {
        width: 500px;
        height: 300px; 
        object-fit: cover;
        border-radius: 10px;
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
        background: #e23939ff;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(152, 57, 57, 0.4);
    }
</style>
<body>
 
    <!-- Hero section  -->
    <section class="hero">
        <div class="write">
        <h1>Blood Bank Management System</h1>
        <p>Welcome to our redesigned Blood Bank Management System. This system helps connect blood donors with those in need, ensuring timely access to safe blood.</p>
        <a href="learn.php" class="cta-button">Learn More</a>
        </div>
         <div class="hero-img">
            <img src="homeimg.webp" alt="My Desktop Image">
         </div>
    </section>
</body>
</html>
  <?php include 'footer.php'; ?>