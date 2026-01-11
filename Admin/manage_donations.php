<?php
session_start();
include '../Logical_Database/connection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

$success = $error = "";

// Check for success message from session
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Handle approve/reject actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $donation_id = $_POST['donation_id'];
    $admin_notes = trim($_POST['admin_notes']);
    
    if (isset($_POST['approve_donation'])) {
        // Get donation details
        $sql = "SELECT blood_type, quantity FROM blood_donations WHERE donation_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $donation_id);
        $stmt->execute();
        $donation = $stmt->get_result()->fetch_assoc();
        
        if ($donation) {
            // Update donation status
            $sql1 = "UPDATE blood_donations SET status = 'Approved', admin_notes = ? WHERE donation_id = ?";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->bind_param("si", $admin_notes, $donation_id);
            
            // Update blood inventory
            $sql2 = "UPDATE blood_inventory SET quantity = quantity + ? WHERE blood_type = ?";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bind_param("is", $donation['quantity'], $donation['blood_type']);
            
            if ($stmt1->execute() && $stmt2->execute()) {
                $_SESSION['success'] = "Donation approved successfully! Blood inventory updated.";
            } else {
                $_SESSION['error'] = "Error approving donation.";
            }
            $stmt1->close();
            $stmt2->close();
        } else {
            $_SESSION['error'] = "Donation not found.";
        }
        $stmt->close();
        
    } elseif (isset($_POST['reject_donation'])) {
        $sql = "UPDATE blood_donations SET status = 'Rejected', admin_notes = ? WHERE donation_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $admin_notes, $donation_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Donation rejected successfully!";
        } else {
            $_SESSION['error'] = "Error rejecting donation.";
        }
        $stmt->close();
    }
    
    // Redirect to avoid form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch all blood donations with donor details
$sql = "SELECT bd.*, d.full_name as donor_name, d.email as donor_email, d.blood_type as donor_blood_type
        FROM blood_donations bd 
        JOIN donors d ON bd.donor_id = d.donor_id 
        ORDER BY 
            CASE bd.status 
                WHEN 'Pending' THEN 1
                WHEN 'Approved' THEN 2
                WHEN 'Rejected' THEN 3
                ELSE 4
            END,
            bd.donation_date DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Donations - Admin</title>
     <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background: #f8f9fa;
        }
        
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles - RED THEME */
        .sidebar {
            width: 250px;
            background: #dc3545;
            color: white;
            padding: 20px 0;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
    
        .nav-links {
            list-style: none;
        }
        
        .nav-links li {
            padding: 15px 25px;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
        }
        
        .nav-links li.active {
            background: rgba(255,255,255,0.1);
            border-left: 4px solid white;
        }
        
        .nav-links li:hover {
            background: rgba(255,255,255,0.1);
            border-left: 4px solid white;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            display: block;
            font-weight: 500;
        }
        
        /* Main Content Styles */
        .main-content {
            flex: 1;
            padding: 30px;
        }
        
        .container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .page-title {
            text-align: center;
            margin-bottom: 25px;
            color: #dc3545;
            font-size: 1.8em;
        }
        
        /* Donation Cards */
        .donation-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background: #fafafa;
            border-left: 4px solid #dc3545;
        }
        
        .donation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .donation-id {
            font-weight: bold;
            color: #333;
            font-size: 1.1em;
        }
        
        .donation-status {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d1ecf1; color: #0c5460; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .status-completed { background: #d4edda; color: #155724; }
        
        .donation-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .detail-group label {
            font-weight: bold;
            color: #666;
            font-size: 0.9em;
        }
        
        .detail-group div {
            color: #333;
            margin-top: 5px;
        }
        
        .donation-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        
        /* Buttons */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s ease;
            font-weight: bold;
        }
        
        .btn-approve {
            background: #28a745;
            color: white;
        }
        
        .btn-approve:hover {
            background: #218838;
        }
        
        .btn-reject {
            background: #dc3545;
            color: white;
        }
        
        .btn-reject:hover {
            background: #c82333;
        }
        
        .btn-view {
            background: #17a2b8;
            color: white;
        }
        
        .btn-view:hover {
            background: #138496;
        }
        
        /* Admin Notes */
        .admin-notes {
            background: #e9ecef;
            padding: 12px;
            border-radius: 5px;
            margin-top: 10px;
            border-left: 4px solid #6c757d;
        }
        
        .admin-notes-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-top: 10px;
            font-size: 0.9em;
            resize: vertical;
            height: 80px;
            font-family: inherit;
        }
        
        .action-form {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px dashed #ddd;
        }
        
        /* Empty State */
        .no-donations {
            text-align: center;
            padding: 50px;
            color: #666;
        }
        
        .no-donations h3 {
            margin-bottom: 10px;
            color: #333;
            font-size: 1.3em;
        }
        
        .no-donations p {
            font-size: 1em;
            margin-bottom: 20px;
        }

        /* Document Section */
        .document-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            border-left: 4px solid #17a2b8;
        }
        
        .document-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        /* Health Questionnaire */
        .health-section {
            background: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            border-left: 4px solid #ffc107;
        }
        
        .health-question {
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid #ffeaa7;
        }
        
        .health-question:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        /* Notification Styles */
        .notification-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            max-width: 400px;
        }
        
        .notification {
            padding: 15px 20px;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            font-weight: 600;
            transform: translateX(400px);
            transition: transform 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        
        .notification.show {
            transform: translateX(0);
        }
        
        .notification.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .notification.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .notification-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: rgba(0,0,0,0.2);
            width: 100%;
            animation: progress 5s linear;
        }
        
        .notification-close {
            position: absolute;
            top: 8px;
            right: 10px;
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: inherit;
            opacity: 0.7;
        }
        
        .notification-close:hover {
            opacity: 1;
        }
        
        @keyframes progress {
            from { width: 100%; }
            to { width: 0%; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li class="active"><a href="manage_donations.php">Manage Donations</a></li>
                <li><a href="manage_requests.php">Manage Requests</a></li>
                <li><a href="blood_inventory.php">Blood Inventory</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="container">
                <h1 class="page-title">Manage Blood Donations</h1>
                
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): 
                        $health_data = json_decode($row['health_questionnaire'], true);
                    ?>
                    <div class="donation-card">
                        <div class="donation-header">
                            <div class="donation-id">
                                Donation #<?php echo $row['donation_id']; ?> - <?php echo $row['donor_name']; ?>
                                <div style="font-size: 0.8em; color: #666; margin-top: 5px;">
                                    Submitted: <?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?>
                                </div>
                            </div>
                            <div class="donation-status status-<?php echo strtolower($row['status']); ?>">
                                <?php echo $row['status']; ?>
                            </div>
                        </div>
                        
                        <div class="donation-details">
                            <div class="detail-group">
                                <label>Donor:</label>
                                <div><?php echo $row['donor_name']; ?> (<?php echo $row['donor_email']; ?>)</div>
                            </div>
                            <div class="detail-group">
                                <label>Blood Type:</label>
                                <div><?php echo $row['blood_type']; ?></div>
                            </div>
                            <div class="detail-group">
                                <label>Donation Date:</label>
                                <div><?php echo date('M j, Y', strtotime($row['donation_date'])); ?></div>
                            </div>
                            <div class="detail-group">
                                <label>Quantity:</label>
                                <div><?php echo $row['quantity']; ?> units</div>
                            </div>
                        </div>
                        
                        <?php if ($health_data): ?>
                        <div class="health-section">
                            <label><strong>Health Questionnaire:</strong></label>
                            <div class="health-question">
                                <strong>Feeling well today?</strong>
                                <span style="color: <?php echo ($health_data['feel_well_today'] ?? 'No') == 'Yes' ? '#28a745' : '#dc3545'; ?>; font-weight: bold;">
                                    <?php echo $health_data['feel_well_today'] ?? 'Not answered'; ?>
                                </span>
                            </div>
                            <div class="health-question">
                                <strong>Recent sickness (last 2 weeks)?</strong>
                                <span style="color: <?php echo ($health_data['recent_sickness'] ?? 'No') == 'No' ? '#28a745' : '#dc3545'; ?>;">
                                    <?php echo $health_data['recent_sickness'] ?? 'Not answered'; ?>
                                </span>
                            </div>
                            <div class="health-question">
                                <strong>Taking medications?</strong>
                                <span style="color: <?php echo ($health_data['medications'] ?? 'No') == 'No' ? '#28a745' : '#dc3545'; ?>;">
                                    <?php echo $health_data['medications'] ?? 'Not answered'; ?>
                                </span>
                            </div>
                            <div class="health-question">
                                <strong>Traveled outside country (last 3 months)?</strong>
                                <span style="color: <?php echo ($health_data['travel_history'] ?? 'No') == 'No' ? '#28a745' : '#dc3545'; ?>;">
                                    <?php echo $health_data['travel_history'] ?? 'Not answered'; ?>
                                </span>
                            </div>
                            <div class="health-question">
                                <strong>Engaged in high-risk activities?</strong>
                                <span style="color: <?php echo ($health_data['high_risk_activity'] ?? 'No') == 'No' ? '#28a745' : '#dc3545'; ?>;">
                                    <?php echo $health_data['high_risk_activity'] ?? 'Not answered'; ?>
                                </span>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($row['document_path'])): 
                            $document_path = $row['document_path'];
                            $correct_path = '../Donor/'. $document_path;
                            $file_exists = file_exists($correct_path);
                        ?>
                        <div class="document-section">
                            <label><strong>Medical Document:</strong></label>
                            <div style="margin: 10px 0; color: #666;">
                                <?php if ($file_exists): ?>
                                    Document uploaded: <?php echo basename($document_path); ?>
                                <?php else: ?>
                                    <span style="color: #dc3545;">Document file not found at: <?php echo $correct_path; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="document-actions">
                                <?php if ($file_exists): ?>
                                    <a href="<?php echo $correct_path; ?>" target="_blank" class="btn btn-view">View Document</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($row['admin_notes'])): ?>
                        <div class="admin-notes">
                            <strong>Admin Notes:</strong> <?php echo $row['admin_notes']; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($row['status'] == 'Pending'): ?>
                        <div class="action-form">
                            <form method="POST">
                                <input type="hidden" name="donation_id" value="<?php echo $row['donation_id']; ?>">
                                <label for="admin_notes_<?php echo $row['donation_id']; ?>">Admin Notes:</label>
                                <textarea name="admin_notes" id="admin_notes_<?php echo $row['donation_id']; ?>" class="admin-notes-input" placeholder="Add notes for approval/rejection..."><?php echo $row['admin_notes'] ?? ''; ?></textarea>
                                <div class="donation-actions">
                                    <button type="submit" name="approve_donation" class="btn btn-approve">Approve Donation</button>
                                    <button type="submit" name="reject_donation" class="btn btn-reject">Reject Donation</button>
                                </div>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-donations">
                        <h3>No blood donations found</h3>
                        <p>There are no blood donations to manage at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
         // Auto-hide notifications after 5 seconds
        function autoHideNotifications() {
            const notifications = document.querySelectorAll('.notification.show');
            notifications.forEach(notification => {
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.style.transform = 'translateX(400px)';
                        setTimeout(() => {
                            if (notification.parentNode) {
                                notification.remove();
                            }
                        }, 400);
                    }
                }, 5000); // 5 seconds
            });
        }

        // Manual close function
        function closeNotification(button) {
            const notification = button.parentNode;
            notification.style.transform = 'translateX(400px)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 400);
        }

        // Initialize auto-hide
        document.addEventListener('DOMContentLoaded', function() {
            autoHideNotifications();
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>