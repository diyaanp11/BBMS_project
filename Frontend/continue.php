<?php
$page_title = "Continue";
include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Continue - Blood Bank Management System</title>
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
        .login-selection {
            padding: 3rem 2rem;
            text-align: center;
        }
        .login-header {
            margin-bottom: 2.5rem;
        }
        .login-header h1 {
            color: #d32f2f;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        .login-header p {
            color: #6c757d;
            font-size: 1.1rem;
        }
        .selection-cards {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        .selection-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            width: 280px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
        }
        .selection-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            border-color: #d32f2f;
        }
        .card-icon {
            font-size: 3rem;
            height: 80px;
            width: 80px;
            line-height: 80px;
            border-radius: 50%;
            margin: 0 auto 1.5rem;
        }
        .admin .card-icon {
            background: rgba(211, 47, 47, 0.1);
            color: #d32f2f;
        }
        .donor .card-icon {
            background: rgba(46, 94, 78, 0.1);
            color: #2E5E4E;
        }
        .recipient .card-icon {
            background: rgba(255, 107, 107, 0.1);
            color: #ff6b6b;
        }
        .selection-card h3 {
            color: #d32f2f;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }
        .selection-card p {
            color: #6c757d;
            margin-bottom: 1.5rem;
        }
        .select-btn {
            display: inline-block;
            background: #d32f2f;
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .select-btn:hover {
            background: #b71c1c;
            transform: scale(1.05);
        }
        @media (max-width: 768px) {
            .selection-cards {
                flex-direction: column;
                align-items: center;
            }
            .login-header h1 { font-size: 1.8rem; }
        }
    </style>
</head>
<body>
<section class="login-selection">
    <div class="login-header">
        <h1>Welcome to BBMS</h1>
        <p>Please select your role to continue</p>
    </div>
    <div class="selection-cards">
        <div class="selection-card admin">
            <div class="card-icon"><i class="fas fa-user-shield"></i></div>
            <h3>Continue as Admin</h3>
            <p>Access administrative functions and manage the blood bank system</p>
            <a href="../Admin/login.php" class="select-btn">Continue</a>
        </div>
        <div class="selection-card donor">
            <div class="card-icon"><i class="fas fa-hand-holding-heart"></i></div>
            <h3>Continue as Donor</h3>
            <p>Donate blood, schedule appointments, and manage your donor profile</p>
            <a href="../Donor/signup.php" class="select-btn">Continue</a>
        </div>
        <div class="selection-card recipient">
            <div class="card-icon"><i class="fas fa-heartbeat"></i></div>
            <h3>Continue as Recipient</h3>
            <p>Request blood, check availability, and manage your requests</p>
            <a href="../Recipient/signup.php" class="select-btn">Continue</a>
        </div>
    </div>
</section>
</body>
</html>
<?php include 'chatbot.php'; ?>
<?php include 'footer.php'; ?>
