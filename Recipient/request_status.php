<?php
session_start();
include '../Logical_Database/connection.php';

// Check if recipient is logged in
if (!isset($_SESSION['recipient_id'])) {
    header("Location: login.php");
    exit();
}

$recipient_id = $_SESSION['recipient_id'];
$success = $error = "";

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $sql = "DELETE FROM blood_requests WHERE id = ? AND recipient_id = ? AND status = 'Pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $delete_id, $recipient_id);
    
    if ($stmt->execute()) {
        $success = "Request deleted successfully!";
    } else {
        $error = "Error deleting request. Only pending requests can be deleted.";
    }
}

// Fetch all requests for this recipient
$sql = "SELECT * FROM blood_requests WHERE recipient_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $recipient_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Status - Blood Bank</title>
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
        
        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background: #e74c3c;
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
        
        .nav-links a {
            color: white;
            text-decoration: none;
            display: block;
            font-weight: 500;
        }
        
        /* Main Content */
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
            color: #e74c3c;
            font-size: 1.6em;
        }
        
        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .request-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background: #fafafa;
        }
        
        .request-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .request-id {
            font-weight: bold;
            color: #333;
        }
        
        .request-status {
            padding: 5px 12px;
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
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-view {
            background: #17a2b8;
            color: white;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .btn-edit {
            background: #ffc107;
            color: #212529;
        }
        
        .no-requests {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .admin-notes {
            background: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            border-left: 4px solid #6c757d;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="request_blood.php">Request Blood</a></li>
                <li class="active"><a href="request_status.php">Request Status</a></li>
                <li><a href="my_profile.php">My Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="container">
                <h1 class="page-title">Your Blood Requests Status</h1>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="request-card">
                        <div class="request-header">
                            <div class="request-id">Request #<?php echo $row['request_id']; ?></div>
                            <div class="request-status status-<?php echo strtolower($row['status']); ?>">
                                <?php echo $row['status']; ?>
                            </div>
                        </div>
                        
                        <div class="request-details">
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
                                <div><?php echo $row['urgency']; ?></div>
                            </div>
                            <div class="detail-group">
                                <label>Request Date:</label>
                                <div><?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?></div>
                            </div>
                        </div>
                        
                        <?php if (!empty($row['reason'])): ?>
                        <div class="detail-group">
                            <label>Reason:</label>
                            <div><?php echo $row['reason']; ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($row['admin_notes'])): ?>
                        <div class="admin-notes">
                            <strong>Admin Notes:</strong> <?php echo $row['admin_notes']; ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="request-actions">
                            <a href="request_status.php?id=<?php echo $row['request_id']; ?>" class="btn btn-view">View Details</a>
                            <?php if ($row['status'] == 'Pending'): ?>
                                <a href="edit_request.php?id=<?php echo $row['request_id']; ?>" class="btn btn-edit">Edit</a>
                                <a href="request_status.php?delete_id=<?php echo $row['request_id']; ?>" class="btn btn-delete" 
                                   onclick="return confirm('Are you sure you want to delete this request?')">Delete</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-requests">
                        <h3>No blood requests found</h3>
                        <p>You haven't made any blood requests yet.</p>
                        <a href="request_blood.php" class="btn btn-view" style="margin-top: 15px;">Make Your First Request</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>