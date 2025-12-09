<?php
session_start();

// Check if donor is logged in
if (!isset($_SESSION['donor_id'])) {
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
                <li><a href="donate_blood.php">Donate Blood</a></li>
                <li><a href="donation_history.php">Donation History</a></li>
                <li class="active"><a href="my_profile.php">My Profile</a></li>
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
                            <div class="info-value"><?php echo $_SESSION['donor_name']; ?></div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Email Address</div>
                            <div class="info-value"><?php echo $_SESSION['donor_email']; ?></div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Blood Type</div>
                            <div class="info-value"><?php echo $_SESSION['blood_type']; ?></div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Account Type</div>
                            <div class="info-value">Blood Donor</div>
                        </div>
                    </div>
                    
                    <div class="member-info">
                        Welcome to LifeBlood Donation System - Thank you for being a donor!
                    </div>
                </div>
                
                <!-- Quick Actions Section -->
                <div class="profile-section">
                    <h2 class="section-title">Quick Actions</h2>
                    <div class="profile-info">
                        <div class="info-group">
                            <div class="info-label">View Donation History</div>
                            <div class="info-value">
                                <a href="donation_history.php" class="btn btn-primary" style="display: block; text-align: center;">
                                    View History
                                </a>
                            </div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Give Donation</div>
                            <div class="info-value">
                                <a href="donate_blood.php" class="btn btn-primary" style="display: block; text-align: center;">
                                    Look for Donation
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