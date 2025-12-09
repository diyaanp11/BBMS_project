<?php
session_start();
include '../Logical_Database/connection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$success = $error = "";

// Handle inventory update - ADD blood
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_inventory'])) {
    $blood_type = $_POST['blood_type'];
    $add_quantity = (int)$_POST['add_quantity'];
    
    if ($add_quantity > 0) {
        $sql = "UPDATE blood_inventory SET quantity = quantity + ? WHERE blood_type = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $add_quantity, $blood_type);
        
        if ($stmt->execute()) {
            $success = "Added $add_quantity units of $blood_type blood to inventory!";
        } else {
            $error = "Error adding blood to inventory.";
        }
    } else {
        $error = "Quantity must be greater than 0.";
    }
}

// Handle inventory update - REMOVE blood  
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_inventory'])) {
    $blood_type = $_POST['blood_type'];
    $remove_quantity = (int)$_POST['remove_quantity'];
    
    // Check current quantity
    $check_sql = "SELECT quantity FROM blood_inventory WHERE blood_type = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $blood_type);
    $check_stmt->execute();
    $current_quantity = $check_stmt->get_result()->fetch_assoc()['quantity'];
    
    if ($remove_quantity > 0) {
        if ($remove_quantity <= $current_quantity) {
            // Remove specified amount
            $sql = "UPDATE blood_inventory SET quantity = quantity - ? WHERE blood_type = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $remove_quantity, $blood_type);
            
            if ($stmt->execute()) {
                $success = "Removed $remove_quantity units of $blood_type blood from inventory!";
            } else {
                $error = "Error removing blood from inventory.";
            }
        } else {
            // Show error if trying to remove more than available
            $error = "Cannot remove $remove_quantity units. Only $current_quantity units of $blood_type blood available in inventory.";
        }
    } else {
        $error = "Quantity must be greater than 0.";
    }
}

// Handle manual quantity set
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['set_inventory'])) {
    $blood_type = $_POST['blood_type'];
    $new_quantity = (int)$_POST['set_quantity'];
    
    if ($new_quantity >= 0) {
        $sql = "UPDATE blood_inventory SET quantity = ? WHERE blood_type = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $new_quantity, $blood_type);
        
        if ($stmt->execute()) {
            $success = "Set $blood_type blood quantity to $new_quantity units!";
        } else {
            $error = "Error updating inventory.";
        }
    } else {
        $error = "Quantity cannot be negative.";
    }
}

// Fetch current inventory
$inventory_sql = "SELECT * FROM blood_inventory ORDER BY 
                  CASE blood_type 
                    WHEN 'O+' THEN 1
                    WHEN 'O-' THEN 2  
                    WHEN 'A+' THEN 3
                    WHEN 'A-' THEN 4
                    WHEN 'B+' THEN 5
                    WHEN 'B-' THEN 6
                    WHEN 'AB+' THEN 7
                    WHEN 'AB-' THEN 8
                  END";
$inventory_result = $conn->query($inventory_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Inventory - Admin</title>
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
        
        /* Main Content */
        .main-content {
            flex: 1;
            padding: 30px;
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .page-title {
            text-align: center;
            margin-bottom: 30px;
            color: #dc3545;
            font-size: 1.8em;
        }
        
        /* Inventory Grid */
        .inventory-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .blood-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            background: #fafafa;
            transition: all 0.3s ease;
        }
        
        .blood-card.low {
            border-left: 4px solid #dc3545;
            background: #f8d7da;
        }
        
        .blood-card.medium {
            border-left: 4px solid #ffc107;
            background: #fff3cd;
        }
        
        .blood-card.high {
            border-left: 4px solid #28a745;
            background: #d4edda;
        }
        
        .blood-type {
            font-size: 1.5em;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .blood-quantity {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .quantity-low { color: #dc3545; }
        .quantity-medium { color: #ffc107; }
        .quantity-high { color: #28a745; }
        
        .unit {
            font-size: 0.8em;
            color: #666;
        }
        
        /* Update Forms */
        .update-forms {
            display: grid;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .update-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 10px;
            align-items: end;
        }
        
        .form-input, .form-select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-warning:hover {
            background: #e0a800;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 30px;
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

        /* New Notification Styles */
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
            animation: progress 30s linear;
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
                <li><a href="manage_donations.php">Manage Donations</a></li>
                <li><a href="manage_requests.php">Manage Requests</a></li>
                <li class="active"><a href="blood_inventory.php">Blood Inventory</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="container">
                <h1 class="page-title">Blood Inventory Management</h1>
                
                <!-- Blood Inventory Grid -->
                <div class="inventory-grid">
                    <?php while ($row = $inventory_result->fetch_assoc()): 
                        $quantity = $row['quantity'];
                        if ($quantity == 0) {
                            $status = 'low';
                            $quantity_class = 'quantity-low';
                        } elseif ($quantity <= 5) {
                            $status = 'medium'; 
                            $quantity_class = 'quantity-medium';
                        } else {
                            $status = 'high';
                            $quantity_class = 'quantity-high';
                        }
                    ?>
                    <div class="blood-card <?php echo $status; ?>">
                        <div class="blood-type"><?php echo $row['blood_type']; ?></div>
                        <div class="blood-quantity <?php echo $quantity_class; ?>">
                            <?php echo $quantity; ?> <span class="unit">units</span>
                        </div>
                        <div class="blood-status">
                            <?php if ($quantity == 0): ?>
                                <span style="color: #dc3545;"> Out of Stock</span>
                            <?php elseif ($quantity <= 2): ?>
                                <span style="color: #dc3545;"> Very Low</span>
                            <?php elseif ($quantity <= 5): ?>
                                <span style="color: #ffc107;">Low Stock</span>
                            <?php else: ?>
                                <span style="color: #28a745;"> In Stock</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                
                <!-- Update Inventory Forms -->
                <div class="update-forms">
                    <!-- Add Blood Form -->
                    <div class="update-form">
                        <h3 style="margin-bottom: 20px; color: #333;"> Add Blood to Inventory</h3>
                        <form method="POST">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="blood_type">Blood Type</label>
                                    <select class="form-select" name="blood_type" required>
                                        <option value="">Select Blood Type</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="add_quantity">Quantity to Add</label>
                                    <input type="number" class="form-input" name="add_quantity" min="1" max="100" required>
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" name="add_inventory" class="btn btn-success">
                                         Add Blood
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Remove Blood Form -->
                    <div class="update-form">
                        <h3 style="margin-bottom: 20px; color: #333;">Remove Blood from Inventory</h3>
                        <form method="POST">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="blood_type">Blood Type</label>
                                    <select class="form-select" name="blood_type" required>
                                        <option value="">Select Blood Type</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="remove_quantity">Quantity to Remove</label>
                                    <input type="number" class="form-input" name="remove_quantity" min="1" max="100" required>
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" name="remove_inventory" class="btn btn-danger">
                                        Remove Blood
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Set Quantity Form -->
                    <div class="update-form">
                        <h3 style="margin-bottom: 20px; color: #333;">Set Exact Quantity</h3>
                        <form method="POST">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="blood_type">Blood Type</label>
                                    <select class="form-select" name="blood_type" required>
                                        <option value="">Select Blood Type</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="set_quantity">Exact Quantity</label>
                                    <input type="number" class="form-input" name="set_quantity" min="0" max="100" required>
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" name="set_inventory" class="btn btn-warning">
                                        Set Quantity
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <?php
                // Calculate stats
                $stats_sql = "SELECT 
                    SUM(quantity) as total_units,
                    COUNT(CASE WHEN quantity = 0 THEN 1 END) as out_of_stock,
                    COUNT(CASE WHEN quantity > 0 AND quantity <= 5 THEN 1 END) as low_stock
                    FROM blood_inventory";
                $stats_result = $conn->query($stats_sql);
                $stats = $stats_result->fetch_assoc();
                ?>
                
                <div class="stats">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['total_units'] ?? 0; ?></div>
                        <div class="stat-label">Total Blood Units</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['out_of_stock'] ?? 0; ?></div>
                        <div class="stat-label">Out of Stock Types</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['low_stock'] ?? 0; ?></div>
                        <div class="stat-label">Low Stock Types</div>
                    </div>
                </div>
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
        // Auto-hide notifications after 30 seconds
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
                }, 30000); // 30 seconds
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