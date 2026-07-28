<?php
$page_title = "About";
include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Blood Bank Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *{ margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
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
            color: #424141;
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
            color: #424141;
        }
        .team-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }
        .team-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }
        .team-card:hover {
            transform: translateY(-5px);
        }
        .team-card i {
            font-size: 3rem;
            color: #d32f2f;
            margin-bottom: 1rem;
        }
        .team-card h3 {
            color: #333;
            margin-bottom: 0.5rem;
        }
        .team-card p {
            color: #666;
        }
        @media (max-width: 768px) {
            .about-header h1 { font-size: 2rem; }
        }
    </style>
</head>
<body>
<div class="about-container">
    <div class="about-header">
        <h1>About Our Blood Bank Management System</h1>
        <p>Learn about our mission, values, and the team behind our life-saving platform</p>
    </div>
        
    <div class="about-content">
        <div class="about-text">
            <h2>Who We Are</h2>
            <p>We are students who built this project with the aim of solving real-life problems in the healthcare field. Through this system, we want to show how technology can be used to support blood banks, encourage donation, and save lives.</p>
            <p>Our project is designed to make blood donation and distribution easier, faster, and more reliable. We developed this system to keep proper records of donors, recipients, and available blood stock, helping hospitals and people in need to get blood on time.</p>
            <p>This project is a simple step toward creating a more efficient and transparent way of managing blood information. We hope it inspires further improvements in the future.</p>
        </div>
    </div> 

    <div class="team-section">
        <div class="team-card">
            <i class="fas fa-code"></i>
            <h3>Built with PHP & MySQL</h3>
            <p>A full-stack web application using server-side scripting and relational database management.</p>
        </div>
        <div class="team-card">
            <i class="fas fa-user-shield"></i>
            <h3>Three User Roles</h3>
            <p>Admin, Donor, and Recipient — each with dedicated dashboards and functionality.</p>
        </div>
        <div class="team-card">
            <i class="fas fa-heartbeat"></i>
            <h3>Life-Saving Mission</h3>
            <p>Connecting donors with recipients to ensure no one waits for blood in an emergency.</p>
        </div>
    </div>
</div>
<?php include 'chatbot.php'; ?>
<?php include 'footer.php'; ?>
</body>
</html>
