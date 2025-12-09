<?php
session_start();
include '../Logical_Database/connection.php';

// Check if admin is logged in - UPDATED TO MATCH YOUR LOGIN SYSTEM
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

$success = $error = "";

// Handle delete user action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    $user_type = $_POST['user_type'];
    
    if ($user_type == 'donor') {
        $sql = "DELETE FROM donors WHERE id = ?";
    } elseif ($user_type == 'recipient') {
        $sql = "DELETE FROM recipients WHERE id = ?";
    } else {
        $sql = "DELETE FROM admins WHERE id = ?";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $success = "User deleted successfully!";
    } else {
        $error = "Error deleting user.";
    }
}

// Handle search
$search = "";
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

// Fetch all users (admins, donors and recipients)
$users = [];

// Fetch admins
$admin_sql = "SELECT id, username as full_name, email, 'admin' as user_type 
              FROM admins 
              WHERE username LIKE ? OR email LIKE ?
              ORDER BY username";
$admin_stmt = $conn->prepare($admin_sql);
$search_param = "%$search%";
$admin_stmt->bind_param("ss", $search_param, $search_param);
$admin_stmt->execute();
$admin_result = $admin_stmt->get_result();

while ($row = $admin_result->fetch_assoc()) {
    $users[] = $row;
}

// Fetch donors
$donor_sql = "SELECT id, full_name, email, 'donor' as user_type 
              FROM donors 
              WHERE full_name LIKE ? OR email LIKE ?
              ORDER BY full_name";
$donor_stmt = $conn->prepare($donor_sql);
$donor_stmt->bind_param("ss", $search_param, $search_param);
$donor_stmt->execute();
$donor_result = $donor_stmt->get_result();

while ($row = $donor_result->fetch_assoc()) {
    $users[] = $row;
}

// Fetch recipients
$recipient_sql = "SELECT id, full_name, email, 'recipient' as user_type 
                  FROM recipients 
                  WHERE full_name LIKE ? OR email LIKE ?
                  ORDER BY full_name";
$recipient_stmt = $conn->prepare($recipient_sql);
$recipient_stmt->bind_param("ss", $search_param, $search_param);
$recipient_stmt->execute();
$recipient_result = $recipient_stmt->get_result();

while ($row = $recipient_result->fetch_assoc()) {
    $users[] = $row;
}

// Get user statistics
$stats_sql = "SELECT 
    (SELECT COUNT(*) FROM admins) as total_admins,
    (SELECT COUNT(*) FROM donors) as total_donors,
    (SELECT COUNT(*) FROM recipients) as total_recipients";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();
$total_users = ($stats['total_admins'] + $stats['total_donors'] + $stats['total_recipients']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
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
        
        /* User Stats */
        .user-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: #e9ecef;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #dc3545;
        }
        
        .stat-value {
            font-size: 2em;
            font-weight: bold;
            color: #dc3545;
        }
        
        .stat-label {
            font-size: 0.9em;
            color: #666;
            margin-top: 5px;
        }
        
        /* Search Bar */
        .search-container {
            margin-bottom: 25px;
        }
        
        .search-form {
            display: flex;
            gap: 10px;
            max-width: 500px;
        }
        
        .search-input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }
        
        .search-btn {
            padding: 12px 25px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .search-btn:hover {
            background: #c82333;
        }
        
        /* Users Table */
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .users-table th {
            background: #dc3545;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: bold;
        }
        
        .users-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .users-table tr:hover {
            background: #f8f9fa;
        }
        
        /* User Type Badges */
        .user-type {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .user-type.admin {
            background: #dc3545;
            color: white;
        }
        
        .user-type.donor {
            background: #d4edda;
            color: #155724;
        }
        
        .user-type.recipient {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.85em;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s ease;
            font-weight: bold;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .btn-delete:hover {
            background: #c82333;
        }
        
        /* Empty State */
        .no-users {
            text-align: center;
            padding: 50px;
            color: #666;
        }
        
        .no-users h3 {
            margin-bottom: 10px;
            color: #333;
            font-size: 1.3em;
        }
        
        .no-users p {
            font-size: 1em;
            margin-bottom: 20px;
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

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 0;
            border-radius: 10px;
            width: 400px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            background: #dc3545;
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            padding: 15px 20px;
            background: #f8f9fa;
            border-radius: 0 0 10px 10px;
            text-align: right;
        }
        
        .btn-cancel {
            background: #6c757d;
            color: white;
            margin-right: 10px;
        }
        
        .btn-cancel:hover {
            background: #5a6268;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
            }
            
            .main-content {
                padding: 15px;
            }
            
            .users-table {
                display: block;
                overflow-x: auto;
            }
            
            .user-stats {
                grid-template-columns: 1fr;
            }
            
            .search-form {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li class="active"><a href="manage_users.php">Manage Users</a></li>
                <li><a href="manage_donations.php">Manage Donations</a></li>
                <li><a href="manage_requests.php">Manage Requests</a></li>
                <li><a href="blood_inventory.php">Blood Inventory</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="container">
                <h1 class="page-title">Manage Users</h1>
                
                <!-- User Statistics -->
                <div class="user-stats">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $total_users; ?></div>
                        <div class="stat-label">Total Users</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['total_admins'] ?? 0; ?></div>
                        <div class="stat-label">Total Admins</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['total_donors'] ?? 0; ?></div>
                        <div class="stat-label">Total Donors</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['total_recipients'] ?? 0; ?></div>
                        <div class="stat-label">Total Recipients</div>
                    </div>
                </div>
                
                <!-- Search Bar -->
                <div class="search-container">
                    <form method="GET" class="search-form">
                        <input type="text" name="search" class="search-input" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="search-btn">Search</button>
                        <?php if ($search): ?>
                            <a href="manage_users.php" class="btn" style="background: #6c757d; color: white;">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <!-- Users Table -->
                <?php if (count($users) > 0): ?>
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>S.No</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>User Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $index => $user): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="user-type <?php echo $user['user_type']; ?>">
                                        <?php echo ucfirst($user['user_type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <!-- Only show delete button, no view button -->
                                        <?php if (!($user['user_type'] == 'admin' && $user['id'] == $_SESSION['admin_id'])): ?>
                                            <button class="btn btn-delete" onclick="openDeleteModal(<?php echo $user['id']; ?>, '<?php echo $user['user_type']; ?>', '<?php echo htmlspecialchars($user['full_name']); ?>')">Delete</button>
                                        <?php else: ?>
                                            <button class="btn" style="background: #6c757d; color: white;" disabled>Current User</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-users">
                        <h3>No users found</h3>
                        <p><?php echo $search ? 'No users match your search criteria.' : 'There are no users registered in the system.'; ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Delete</h3>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete user: <strong id="deleteUserName"></strong>?</p>
                <p style="color: #dc3545; font-size: 0.9em; margin-top: 10px;">
                    This action cannot be undone. All associated data will be permanently deleted.
                </p>
            </div>
            <div class="modal-footer">
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="user_id" id="deleteUserId">
                    <input type="hidden" name="user_type" id="deleteUserType">
                    <button type="button" class="btn btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                    <button type="submit" name="delete_user" class="btn btn-delete">Delete</button>
                </form>
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
        // Modal functions
        function openDeleteModal(userId, userType, userName) {
            document.getElementById('deleteUserId').value = userId;
            document.getElementById('deleteUserType').value = userType;
            document.getElementById('deleteUserName').textContent = userName;
            document.getElementById('deleteModal').style.display = 'block';
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target == modal) {
                closeDeleteModal();
            }
        }

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
                }, 5000);
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