<?php
session_start();
include '../Logical_Database/connection.php';

// Check if admin is logged in
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
        $sql = "DELETE FROM donors WHERE donor_id = ?";
    } elseif ($user_type == 'recipient') {
        $sql = "DELETE FROM recipients WHERE recipient_id = ?";
    } else {
        $sql = "DELETE FROM admins WHERE admin_id = ?";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $success = "User deleted successfully!";
    } else {
        $error = "Error deleting user.";
    }
    $stmt->close();
}

// Handle search
$search = "";
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

// Fetch all users (admins, donors and recipients)
$users = [];

// Fetch admins
$admin_sql = "SELECT admin_id as id, username as full_name, email, 'admin' as user_type 
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
$admin_stmt->close();

// Fetch donors
$donor_sql = "SELECT donor_id as id, full_name, email, 'donor' as user_type 
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
$donor_stmt->close();

// Fetch recipients
$recipient_sql = "SELECT recipient_id as id, full_name, email, 'recipient' as user_type 
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
$recipient_stmt->close();

// Get user statistics
$stats_sql = "SELECT 
    (SELECT COUNT(*) FROM admins) as total_admins,
    (SELECT COUNT(*) FROM donors) as total_donors,
    (SELECT COUNT(*) FROM recipients) as total_recipients";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();
$total_users = ($stats['total_admins'] + $stats['total_donors'] + $stats['total_recipients']);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
    <!-- [Keep all your CSS styles from original file] -->
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
                
                <!-- [Rest of your HTML - keep as is] -->
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
            </div>
        </div>
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
               
  
