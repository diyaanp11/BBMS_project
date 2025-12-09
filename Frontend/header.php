<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood bank system</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, #d32f2f 0%, #b71c1c 100%);
        padding: 0.8rem 5%;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        position: relative;
        z-index: 1000;
    }
     .brand {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 1.8rem;
        font-weight: 700;
        color: white;
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
    }
    .brand i {
        color: #ffcdd2;
        font-size: 2rem;
    }
    .nav-links {
        display: flex;
        list-style: none;
        gap: 0.2rem;
    }
     .nav-links a {
        display: flex;
        align-items: center;
        gap: 8px;
        color: white;
        text-decoration: none;
        font-weight: 500;
        padding: 0.9rem 1.5rem;
        border-radius: 50px;
        transition: all 0.3s ease;
        position: relative;
    }
    .nav-links a:hover:before {
        transform: translateX(0);
    }
    .nav-links a:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-2px);
    }
    .nav-links i {
        font-size: 1.2rem;
    }
</style>
<body>
       <!-- Navbar -->
<nav class="navbar">
<div class="brand"><i class="fa-solid fa-droplet"></i>BBMS</div>
<ul class="nav-links">
    <li><a href="home.php"><i class="fas fa-home"></i>Home</a></li>
    <li><a href="aboutus.php"><i class="fas fa-info-circle"></i>About Us</a></li>
    <li><a href="contactus.php"><i class="fa-solid fa-phone"></i> Contact Us</a></li>
    <li><a href="continue.php"><i class="fas fa-sign-in-alt"></i>Login</a></li>
    </ul>
</nav>
</body>
</html>