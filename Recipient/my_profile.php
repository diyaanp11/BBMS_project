<?php
session_start();

// Check if recipient is logged in
if (!isset($_SESSION['recipient_loggedin']) || $_SESSION['recipient_loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

$success = $error = "";
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Blood Bank</title>
   <link rel="stylesheet" href="../profile.css">     
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="request_blood.php">Request Blood</a></li>
                <li><a href="request_status.php">Request Status</a></li>
                <li><a href="my_profile.php">My Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="profile-container">
                <h1 class="page-title">My Profile</h1>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <!-- Profile Information Section -->
                <div class="profile-section">
                    <h2 class="section-title">Profile Information</h2>
                    <div class="profile-info">
                        <div class="info-group">
                            <div class="info-label">Full Name</div>
                            <div class="info-value"><?php echo $_SESSION['recipient_name']; ?></div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Email Address</div>
                            <div class="info-value"><?php echo $_SESSION['recipient_email']; ?></div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Location</div>
                            <div class="info-value"><?php echo $_SESSION['recipient_location']; ?></div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Account Type</div>
                            <div class="info-value">Blood Recipient</div>
                        </div>
                    </div>
                    
                    <div class="member-info">
                        Welcome to LifeBlood Donation System
                    </div>
                </div>
                
                <!-- Quick Stats Section -->
                <div class="profile-section">
                    <h2 class="section-title">Quick Actions</h2>
                    <div class="profile-info">
                        <div class="info-group">
                            <div class="info-label">Need Blood?</div>
                            <div class="info-value">
                                <a href="request_blood.php" class="btn btn-primary" style="display: block; text-align: center;">
                                    Request Blood Now
                                </a>
                            </div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Check Status</div>
                            <div class="info-value">
                                <a href="request_status.php" class="btn btn-primary" style="display: block; text-align: center;">
                                    View Request Status
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>