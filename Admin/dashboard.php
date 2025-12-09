<?php
session_start();
include '../Logical_Database/connection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch blood inventory
$blood_data = [
    'A+' => 0, 'A-' => 0, 'B+' => 0, 'B-' => 0,
    'AB+' => 0, 'AB-' => 0, 'O+' => 0, 'O-' => 0
];

$sql_blood = "SELECT blood_type, quantity FROM blood_inventory";
$result_blood = $conn->query($sql_blood);
if ($result_blood) {
    while ($row = $result_blood->fetch_assoc()) {
        $blood_data[$row['blood_type']] = $row['quantity'];
    }
}

// Calculate total blood
$total_blood = array_sum($blood_data);

// Get admin stats
$total_donors = $conn->query("SELECT COUNT(*) as count FROM donors")->fetch_assoc()['count'];
$total_recipients = $conn->query("SELECT COUNT(*) as count FROM recipients")->fetch_assoc()['count'];
$pending_requests = $conn->query("SELECT COUNT(*) as count FROM blood_requests WHERE status='Pending'")->fetch_assoc()['count'];
// $pending_requests = 0; // Placeholder if no requests table exists
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Blood Bank</title>
    <link rel="stylesheet" href="../dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
         <div class="sidebar">
            <ul class="nav-links">
                <li class="active"><a href="dashboard.php">Dashboard</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="manage_donations.php">Manage Donations</a></li>
                <li><a href="manage_requests.php">Manage Requests</a></li>
                <li><a href="blood_inventory.php">Blood Inventory</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
        <!-- Main Content -->
        <div class="main-content">
            <!-- Welcome Section -->
            <div class="welcome-box">
                <h1>WELCOME BACK, ADMIN!</h1>
                <p>System Overview & Blood Bank Management</p>
            </div>
            
            <!-- Blood Availability Grid -->
            <div class="blood-grid">
                <!-- Blood Type A+ -->
                <div class="blood-card">
                    <div class="blood-type">A+</div>
                    <div class="blood-units"><?php echo $blood_data['A+']; ?></div>
                    <div class="blood-label">UNITS AVAILABLE</div>
                </div>
                
                <!-- Blood Type A- -->
                <div class="blood-card">
                    <div class="blood-type">A-</div>
                    <div class="blood-units"><?php echo $blood_data['A-']; ?></div>
                    <div class="blood-label">UNITS AVAILABLE</div>
                </div>
                
                <!-- Blood Type B+ -->
                <div class="blood-card">
                    <div class="blood-type">B+</div>
                    <div class="blood-units"><?php echo $blood_data['B+']; ?></div>
                    <div class="blood-label">UNITS AVAILABLE</div>
                </div>
                
                <!-- Blood Type B- -->
                <div class="blood-card">
                    <div class="blood-type">B-</div>
                    <div class="blood-units"><?php echo $blood_data['B-']; ?></div>
                    <div class="blood-label">UNITS AVAILABLE</div>
                </div>
                
                <!-- Blood Type AB+ -->
                <div class="blood-card">
                    <div class="blood-type">AB+</div>
                    <div class="blood-units"><?php echo $blood_data['AB+']; ?></div>
                    <div class="blood-label">UNITS AVAILABLE</div>
                </div>
                
                <!-- Blood Type AB- -->
                <div class="blood-card">
                    <div class="blood-type">AB-</div>
                    <div class="blood-units"><?php echo $blood_data['AB-']; ?></div>
                    <div class="blood-label">UNITS AVAILABLE</div>
                </div>
                
                <!-- Blood Type O+ -->
                <div class="blood-card">
                    <div class="blood-type">O+</div>
                    <div class="blood-units"><?php echo $blood_data['O+']; ?></div>
                    <div class="blood-label">UNITS AVAILABLE</div>
                </div>
                
                <!-- Blood Type O- -->
                <div class="blood-card">
                    <div class="blood-type">O-</div>
                    <div class="blood-units"><?php echo $blood_data['O-']; ?></div>
                    <div class="blood-label">UNITS AVAILABLE</div>
                </div>
                
                <!-- Total Blood Available -->
                <div class="blood-card">
                    <div class="blood-type">TOTAL BLOOD AVAILABLE</div>
                    <div class="blood-units"><?php echo $total_blood; ?> UNITS</div>
                    <div class="blood-label">ACROSS ALL BLOOD TYPES</div>
                </div>
            </div>
        </div>
    </div>
<script>
document.querySelector('a[href="logout.php"]').addEventListener('click', function(e) {
    if(!confirm('Are you sure you want to logout?')) {
        e.preventDefault();
    }
});
</script>
</body>
</html>