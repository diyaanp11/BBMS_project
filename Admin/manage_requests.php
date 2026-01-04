<?php
session_start();
include '../Logical_Database/connection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
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
            $stmt1->close();
            $stmt2->close();
        } else {
            $error = "Insufficient inventory! Only $current_inventory units of {$request['blood_type']} available.";
        }
        $stmt->close();
        $check_stmt->close();
        
    } elseif (isset($_POST['reject_request'])) {
        $sql = "UPDATE blood_requests SET status = 'Rejected', admin_notes = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $admin_notes, $request_id);
        
        if ($stmt->execute()) {
            $success = "Request rejected successfully!";
        } else {
            $error = "Error rejecting request.";
        }
        $stmt->close();
    }
}

// Fetch all blood requests with recipient details
$sql = "SELECT br.*, r.full_name as recipient_name, r.email as recipient_email 
        FROM blood_requests br 
        JOIN recipients r ON br.recipient_id = r.recipient_id 
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
    <!-- [Keep all your CSS styles] -->
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