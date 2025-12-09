<?php
$page_title = "about";
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
    .about-container {
        min-height: 80vh;
        max-width: 1200px;
        margin: 0 auto;
        padding: 3rem 2rem;
    } 
    .about-header {
        text-align: center;
        margin-bottom: 3rem;
    }
    .about-header h1 {
        font-size: 2.8rem;
        color: #d32f2f;
        margin-bottom: 1rem;
    }
    .about-header p {
        font-size: 1.2rem;
        color: #424141ff;
        max-width: 800px;
        margin: 0 auto;
    } 
    .about-content {
        display: flex;
        flex-wrap: wrap;
        gap: 2rem;
        margin-bottom: 3rem;
    }
    .about-text {
        flex: 1;
        min-width: 300px;
    }
    .about-text h2 {
        color: #2E5E4E;
        margin-bottom: 1.5rem;
        font-size: 1.8rem;
    }
    .about-text p {
        margin-bottom: 1.5rem;
        line-height: 1.8;
        color: #424141ff;
    }
</style>
<body>
<div class="about-container">
    <div class="about-header">
        <h1>About Our Blood Bank Management System</h1>
        <p>Learn about our mission, values, and the team behind our life-saving platform</p>
    </div>
        
    <div class="about-content">
        <div class="about-text">
            <h2>Who We Are?</h2>
            <p>We are students who built this project with the aim of solving real-life problems in the healthcare field. Through this system, we want to show how technology can be used to support blood banks, encourage donation, and save lives.</p>
            <p>Our project is designed to make blood donation and distribution easier, faster, and more reliable. We developed this system to keep proper records of donors, recipients, and available blood stock, helping hospitals and people in need to get blood on time.</p>
            <p>This project is a simple step toward creating a more efficient and transparent way of managing blood information. We hope it inspires further improvements in the future.</p>
        </div>
    </div> 
</div>
    <?php include 'footer.php'; ?>
</body>
</html>
 