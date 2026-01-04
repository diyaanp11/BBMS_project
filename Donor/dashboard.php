<?php
session_start();
// Include database connection
include '../Logical_Database/connection.php';

// Check if donor is logged in
if (!isset($_SESSION['donor_id'])) {
    header("Location: login.php");
    exit();
}

// ========== DONATION COOLDOWN CHECK ==========
$donor_id = $_SESSION['donor_id'];
$min_days_between_donations = 56; // WHO recommends 56 days (8 weeks)

// Get last approved donation date
$sql_last_donation = "SELECT donation_date FROM blood_donations 
                      WHERE donor_id = ? AND status = 'Approved' 
                      ORDER BY donation_date DESC LIMIT 1";
$stmt_last = $conn->prepare($sql_last_donation);
$stmt_last->bind_param("i", $donor_id);
$stmt_last->execute();
$last_donation_result = $stmt_last->get_result();
$last_donation = $last_donation_result->fetch_assoc();

$next_eligible_date = '';
$days_remaining = 0;
$is_eligible = true;

if ($last_donation) {
    $last_date = new DateTime($last_donation['donation_date']);
    $today = new DateTime();
    $interval = $today->diff($last_date);
    $days_since = $interval->days;
    
    if ($days_since < $min_days_between_donations) {
        $days_remaining = $min_days_between_donations - $days_since;
        $next_eligible_date = date('Y-m-d', strtotime($last_donation['donation_date'] . " + $min_days_between_donations days"));
        $is_eligible = false;
    }
}
// ========== END COOLDOWN CHECK ==========

// Fetch donor details - CORRECTED: using donor_id not id
$sql_donor = "SELECT full_name, blood_type FROM donors WHERE donor_id = ?";
$stmt = $conn->prepare($sql_donor);
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$result = $stmt->get_result();
$donor = $result->fetch_assoc();

// Fetch blood inventory (will be 0 initially until admin adds blood)
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

// Close statements
$stmt_last->close();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Dashboard - Blood Bank</title>
    <link rel="stylesheet" href="../dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <ul class="nav-links">
                <li class="active"><a href="dashboard.php">Dashboard</a></li>
                <li><a href="donate_blood.php">Donate Blood</a></li>
                <li><a href="donation_history.php">Donation History</a></li>
                <li><a href="my_profile.php">My Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Welcome Section -->
            <div class="welcome-box">
                <h1>WELCOME BACK, <?php echo strtoupper($donor['full_name']); ?>!</h1>
                <p>Your blood type: <strong><?php echo $donor['blood_type']; ?></strong> | Your generosity saves lives!</p>
            </div>
            
            <!-- Donation Eligibility Card -->
            <div class="eligibility-card" style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-left: 4px solid <?php echo $is_eligible ? '#28a745' : '#dc3545'; ?>;">
                <h3 style="color: #333; margin-bottom: 10px;">Donation Eligibility Status</h3>
                
                <?php if ($is_eligible): ?>
                    <div style="color: #28a745; font-weight: bold; font-size: 1.1em;">
                         ELIGIBLE TO DONATE
                    </div>
                    <p style="color: #666; margin-top: 5px;">
                        You can submit a new donation request now.
                        <?php if ($last_donation): ?>
                            <br><small>Last donation: <?php echo date('M d, Y', strtotime($last_donation['donation_date'])); ?></small>
                        <?php endif; ?>
                    </p>
                <?php else: ?>
                    <div style="color: #dc3545; font-weight: bold; font-size: 1.1em;">
                         NOT ELIGIBLE YET
                    </div>
                    <p style="color: #666; margin-top: 5px;">
                        Next eligible donation date: <strong><?php echo date('M d, Y', strtotime($next_eligible_date)); ?></strong><br>
                        <small><?php echo $days_remaining; ?> days remaining (<?php echo $min_days_between_donations; ?> days required between donations)</small>
                    </p>
                <?php endif; ?>
                
                <div style="margin-top: 10px; font-size: 0.9em; color: #6c757d;">
                    <small>Based on WHO recommendation of <?php echo $min_days_between_donations; ?> days between whole blood donations.</small>
                </div>
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