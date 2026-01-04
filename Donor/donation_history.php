<?php
session_start();
include '../Logical_Database/connection.php';

// Check if donor is logged in
if (!isset($_SESSION['donor_id'])) {
    header("Location: login.php");
    exit();
}

$donor_id = $_SESSION['donor_id'];
$success = $error = "";

// ========== DONATION COOLDOWN CHECK ==========
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
// ========== END COOLDOWN CHECK ==========

// Handle donation cancellation (DELETE operation)
if (isset($_GET['cancel_id'])) {
    $cancel_id = $_GET['cancel_id'];
    
    // Only allow cancellation of pending donations
    $sql = "DELETE FROM blood_donations WHERE id = ? AND donor_id = ? AND status = 'Pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $cancel_id, $donor_id);
    
    if ($stmt->execute()) {
        $success = "Donation request cancelled successfully!";
    } else {
        $error = "Error cancelling donation request. Only pending donations can be cancelled.";
    }
    $stmt->close();
}

// Fetch donation history for this donor
$sql = "SELECT * FROM blood_donations WHERE donor_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation History - Blood Bank</title>
    <link rel="stylesheet" href="../profile.css">
    <style>
        /* ... keep your existing styles ... */
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="donate_blood.php">Donate Blood</a></li>
                <li class="active"><a href="donation_history.php">Donation History</a></li>
                <li><a href="my_profile.php">My Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="profile-container">
                <h1 class="page-title">Donation History</h1>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <!-- Last Donation Info -->
                <?php if ($last_donation): ?>
                    <div style="background: #e9ecef; padding: 10px 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #dc3545;">
                        <strong>Last Successful Donation:</strong> <?php echo date('M d, Y', strtotime($last_donation['donation_date'])); ?>
                        <small style="color: #666; display: block; margin-top: 5px;">
                            Next eligible donation: <?php echo date('M d, Y', strtotime($last_donation['donation_date'] . ' + 56 days')); ?>
                            (56 days required between donations)
                        </small>
                    </div>
                <?php endif; ?>
                
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): 
                        $health_data = json_decode($row['health_questionnaire'], true);
                    ?>
                    <div class="donation-card">
                        <div class="donation-header">
                            <div class="donation-id">Donation #<?php echo $row['id']; ?></div>
                            <div class="donation-status status-<?php echo strtolower($row['status']); ?>">
                                <?php echo $row['status']; ?>
                            </div>
                        </div>
                        
                        <div class="donation-details">
                            <div class="detail-group">
                                <label>Blood Type:</label>
                                <div><?php echo $row['blood_type']; ?></div>
                            </div>
                            <div class="detail-group">
                                <label>Quantity:</label>
                                <div><?php echo $row['quantity']; ?> units</div>
                            </div>
                            <div class="detail-group">
                                <label>Donation Date:</label>
                                <div><?php echo date('M j, Y', strtotime($row['donation_date'])); ?></div>
                            </div>
                            <div class="detail-group">
                                <label>Request Date:</label>
                                <div><?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?></div>
                            </div>
                            
                            <?php if (!empty($row['document_path'])): ?>
                            <div class="detail-group">
                                <label>Medical Document:</label>
                                <div>
                                    <a href="<?php echo $row['document_path']; ?>" target="_blank" class="document-link">
                                        📄 View Medical Report
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($health_data): ?>
                        <div class="health-info">
                            <strong>Health Status:</strong> 
                            <?php echo $health_data['feel_well_today'] == 'Yes' ? 'Fit to donate' : 'Not fit to donate'; ?>
                            <?php if ($health_data['recent_sickness'] == 'Yes'): ?> | Recent sickness reported<?php endif; ?>
                            <?php if ($health_data['medications'] == 'Yes'): ?> | Taking medications<?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="donation-actions">
                            <a href="donation_history.php?id=<?php echo $row['id']; ?>" class="btn btn-view">View Details</a>
                            
                            <?php if ($row['status'] == 'Pending'): ?>
                                <a href="edit_donation.php?id=<?php echo $row['id']; ?>" class="btn btn-edit">Edit</a>
                                <a href="donation_history.php?cancel_id=<?php echo $row['id']; ?>" 
                                   class="btn btn-delete"
                                   onclick="return confirm('Are you sure you want to cancel this donation request?')">
                                    Cancel Request
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($row['status'] == 'Completed'): ?>
                                <span style="color: #28a745; font-weight: bold;">Thank you for your donation!</span>
                            <?php endif; ?>
                            
                            <?php if ($row['status'] == 'Rejected'): ?>
                                <span style="color: #dc3545; font-weight: bold;">Donation request was rejected</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-donations">
                        <h3>No donation history found</h3>
                        <p>You haven't made any blood donation requests yet.</p>
                        <a href="donate_blood.php" class="btn btn-primary" style="margin-top: 15px;">Make Your First Donation Request</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
<?php
// Close statements
$stmt_last->close();
$stmt->close();
$conn->close();
?>