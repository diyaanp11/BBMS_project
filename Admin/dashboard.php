<?php
session_start();
include '../Logical_Database/connection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
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
$total_recipients = $conn->query("SELECT COUNT(*) as count FROM recipients")->fetch_assoc()['count'] ?? 0;
$pending_requests = $conn->query("SELECT COUNT(*) as count FROM blood_requests WHERE status='Pending'")->fetch_assoc()['count'] ?? 0;

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Blood Bank</title>
    <link rel="stylesheet" href="../dashboard.css">
    <style>
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: #dc3545;
            color: white;
            padding: 20px 0;
        }
        
        .nav-links {
            list-style: none;
        }
        
        .nav-links li {
            padding: 15px 25px;
            border-left: 4px solid transparent;
        }
        
        .nav-links li.active {
            background: rgba(255,255,255,0.1);
            border-left: 4px solid white;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
        }
        
        .main-content {
            flex: 1;
            padding: 30px;
        }
        
        .welcome-box {
            background: #dc3545;
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        
        .blood-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .blood-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            background: white;
        }
        
        .blood-type {
            font-size: 1.5em;
            font-weight: bold;
            color: #dc3545;
        }
        
        .blood-units {
            font-size: 2em;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .blood-label {
            color: #666;
            font-size: 0.9em;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #dc3545;
        }
        
        .stat-label {
            color: #666;
            margin-top: 10px;
        }
    </style>
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
                <h1>WELCOME BACK, <?php echo strtoupper($_SESSION['admin_name'] ?? 'ADMIN'); ?>!</h1>
                <p>System Overview & Blood Bank Management</p>
            </div>
            
            <!-- Stats Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_donors; ?></div>
                    <div class="stat-label">Total Donors</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_recipients; ?></div>
                    <div class="stat-label">Total Recipients</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $pending_requests; ?></div>
                    <div class="stat-label">Pending Requests</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_blood; ?></div>
                    <div class="stat-label">Total Blood Units</div>
                </div>
            </div>
            
            <!-- Blood Availability Grid -->
            <div class="blood-grid">
                <?php foreach ($blood_data as $type => $quantity): ?>
                <div class="blood-card">
                    <div class="blood-type"><?php echo $type; ?></div>
                    <div class="blood-units"><?php echo $quantity; ?></div>
                    <div class="blood-label">UNITS AVAILABLE</div>
                </div>
                <?php endforeach; ?>
                
                <!-- Total Blood Available -->
                <div class="blood-card">
                    <div class="blood-type">TOTAL BLOOD</div>
                    <div class="blood-units"><?php echo $total_blood; ?></div>
                    <div class="blood-label">ACROSS ALL TYPES</div>
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