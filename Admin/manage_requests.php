<?php
session_start();
include '../Logical_Database/connection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$success = $error = "";

// Handle approve/reject actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_id = $_POST['request_id'];
    $admin_notes = trim($_POST['admin_notes']);
    
    if (isset($_POST['approve_request'])) {
        // Get request details
        $sql = "SELECT blood_type, quantity FROM blood_requests WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $request = $stmt->get_result()->fetch_assoc();
        
        // Check inventory availability
        $check_sql = "SELECT quantity FROM blood_inventory WHERE blood_type = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $request['blood_type']);
        $check_stmt->execute();
        $current_inventory = $check_stmt->get_result()->fetch_assoc()['quantity'];
        
        if ($current_inventory >= $request['quantity']) {
            // Update request status
            $sql1 = "UPDATE blood_requests SET status = 'Approved', admin_notes = ? WHERE id = ?";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->bind_param("si", $admin_notes, $request_id);
            
            // Update blood inventory
            $sql2 = "UPDATE blood_inventory SET quantity = quantity - ? WHERE blood_type = ?";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bind_param("is", $request['quantity'], $request['blood_type']);
            
            if ($stmt1->execute() && $stmt2->execute()) {
                $success = "Request approved successfully! Blood inventory updated.";
            } else {
                $error = "Error approving request.";
            }
        } else {
            $error = "Insufficient inventory! Only $current_inventory units of {$request['blood_type']} available.";
        }
        
    } elseif (isset($_POST['reject_request'])) {
        $sql = "UPDATE blood_requests SET status = 'Rejected', admin_notes = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $admin_notes, $request_id);
        
        if ($stmt->execute()) {
            $success = "Request rejected successfully!";
        } else {
            $error = "Error rejecting request.";
        }
    }
}

// Fetch all blood requests with recipient details
$sql = "SELECT br.*, r.full_name as recipient_name, r.email as recipient_email 
        FROM blood_requests br 
        JOIN recipients r ON br.recipient_id = r.id 
        ORDER BY 
            CASE br.status 
                WHEN 'Pending' THEN 1
                WHEN 'Approved' THEN 2
                WHEN 'Rejected' THEN 3
                ELSE 4
            END,
            br.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Requests - Admin</title>
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
        
        /* Request Cards */
        .request-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background: #fafafa;
            border-left: 4px solid #dc3545;
        }
        
        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .request-id {
            font-weight: bold;
            color: #333;
            font-size: 1.1em;
        }
        
        .request-status {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d1ecf1; color: #0c5460; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .status-fulfilled { background: #d4edda; color: #155724; }
        
        .request-details {
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
        
        .request-actions {
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
        .no-requests {
            text-align: center;
            padding: 50px;
            color: #666;
        }
        
        .no-requests h3 {
            margin-bottom: 10px;
            color: #333;
            font-size: 1.3em;
        }
        
        .no-requests p {
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

        /* Request Stats */
        .request-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: #e9ecef;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 1.5em;
            font-weight: bold;
            color: #dc3545;
        }
        
        .stat-label {
            font-size: 0.9em;
            color: #666;
        }

        /* Urgency Badges */
        .urgency-high { color: #dc3545; font-weight: bold; }
        .urgency-medium { color: #ffc107; font-weight: bold; }
        .urgency-low { color: #28a745; font-weight: bold; }
        
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="manage_donations.php">Manage Donations</a></li>
                <li class="active"><a href="manage_requests.php">Manage Requests</a></li>
                <li><a href="blood_inventory.php">Blood Inventory</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="container">
                <h1 class="page-title">Manage Blood Requests</h1>
                
                <!-- Request Statistics -->
                <?php
                $stats_sql = "SELECT 
                    COUNT(*) as total_requests,
                    COUNT(CASE WHEN status = 'Pending' THEN 1 END) as pending_requests,
                    COUNT(CASE WHEN status = 'Approved' THEN 1 END) as approved_requests,
                    COUNT(CASE WHEN status = 'Rejected' THEN 1 END) as rejected_requests
                    FROM blood_requests";
                $stats_result = $conn->query($stats_sql);
                $stats = $stats_result->fetch_assoc();
                ?>
                
                <div class="request-stats">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['total_requests'] ?? 0; ?></div>
                        <div class="stat-label">Total Requests</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['pending_requests'] ?? 0; ?></div>
                        <div class="stat-label">Pending</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['approved_requests'] ?? 0; ?></div>
                        <div class="stat-label">Approved</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['rejected_requests'] ?? 0; ?></div>
                        <div class="stat-label">Rejected</div>
                    </div>
                </div>
                
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="request-card">
                        <div class="request-header">
                            <div class="request-id">
                                Request #<?php echo $row['id']; ?> - <?php echo $row['recipient_name']; ?>
                                <div style="font-size: 0.8em; color: #666; margin-top: 5px;">
                                    <?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?>
                                </div>
                            </div>
                            <div class="request-status status-<?php echo strtolower($row['status']); ?>">
                                <?php echo $row['status']; ?>
                            </div>
                        </div>
                        
                        <div class="request-details">
                            <div class="detail-group">
                                <label>Recipient:</label>
                                <div><?php echo $row['recipient_name']; ?> (<?php echo $row['recipient_email']; ?>)</div>
                            </div>
                            <div class="detail-group">
                                <label>Patient Name:</label>
                                <div><?php echo $row['patient_name']; ?></div>
                            </div>
                            <div class="detail-group">
                                <label>Hospital:</label>
                                <div><?php echo $row['hospital_name']; ?></div>
                            </div>
                            <div class="detail-group">
                                <label>Blood Type:</label>
                                <div><?php echo $row['blood_type']; ?></div>
                            </div>
                            <div class="detail-group">
                                <label>Quantity:</label>
                                <div><?php echo $row['quantity']; ?> units</div>
                            </div>
                            <div class="detail-group">
                                <label>Urgency:</label>
                                <div class="urgency-<?php echo strtolower($row['urgency']); ?>">
                                    <?php echo $row['urgency']; ?>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($row['reason'])): ?>
                        <div class="detail-group">
                            <label>Reason:</label>
                            <div><?php echo $row['reason']; ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Document Section - FIXED PATH -->
                        <?php if (!empty($row['document_path'])): 
                            $document_path = $row['document_path'];
                            
                            // The recipient saves files to "documents/" folder in Recipient directory
                            // So the correct path from Admin is: ../Recipient/documents/filename
                            $correct_path = '../Recipient/' . $document_path;
                            $file_exists = file_exists($correct_path);
                        ?>
                        <div class="document-section">
                            <label>Supporting Document:</label>
                            <div style="margin-top: 5px;">
                                <strong>File:</strong> <?php echo basename($document_path); ?>
                                <?php if ($file_exists): ?>
                                    <span style="color: #28a745; font-weight: bold;"> ( Available)</span>
                                <?php else: ?>
                                    <span style="color: #dc3545; font-weight: bold;"> ( File not found)</span>
                                <?php endif; ?>
                            </div>
                            <div class="document-actions">
                                <?php if ($file_exists): ?>
                                    <a href="<?php echo $correct_path; ?>" target="_blank" class="btn btn-view">
                                        View Document
                                    </a>
                                    <a href="<?php echo $correct_path; ?>" download class="btn" style="background: #6f42c1; color: white;">
                                        Download Document
                                    </a>
                                <?php else: ?>
                                    <button class="btn" style="background: #6c757d; color: white;" disabled>
                                        Document Not Available
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="document-section">
                            <label>Supporting Document:</label>
                            <div style="color: #666; font-style: italic; margin-top: 5px;">
                                No document uploaded for this request.
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
                                <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                <label for="admin_notes_<?php echo $row['id']; ?>">Admin Notes:</label>
                                <textarea name="admin_notes" id="admin_notes_<?php echo $row['id']; ?>" class="admin-notes-input" placeholder="Add notes for approval/rejection..."><?php echo $row['admin_notes'] ?? ''; ?></textarea>
                                <div class="request-actions">
                                    <button type="submit" name="approve_request" class="btn btn-approve">Approve Request</button>
                                    <button type="submit" name="reject_request" class="btn btn-reject">Reject Request</button>
                                </div>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-requests">
                        <h3>No blood requests found</h3>
                        <p>There are no blood requests to manage at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Notification Container -->
    <div class="notification-container" id="notificationContainer">
        <?php if ($success): ?>
        <div class="notification success show" id="successNotification">
            <button class="notification-close" onclick="closeNotification(this)">×</button>
            <?php echo $success; ?>
            <div class="notification-progress"></div>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="notification error show" id="errorNotification">
            <button class="notification-close" onclick="closeNotification(this)">×</button>
            <?php echo $error; ?>
            <div class="notification-progress"></div>
        </div>
        <?php endif; ?>
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