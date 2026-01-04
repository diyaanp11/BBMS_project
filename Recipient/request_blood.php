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

// KEEP THIS - Fetch available blood types and quantities from inventory
$inventory_data = [];
$inventory_sql = "SELECT blood_type, quantity 
                  FROM blood_inventory 
                  WHERE quantity > 0";
$inventory_result = $conn->query($inventory_sql);

while ($row = $inventory_result->fetch_assoc()) {
    $inventory_data[$row['blood_type']] = $row['quantity'];
}

// Handle blood request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $blood_type = trim($_POST['blood_type']);
    $quantity = trim($_POST['quantity']);
    $urgency = trim($_POST['urgency']);
    $reason = trim($_POST['reason']);
    $hospital_name = trim($_POST['hospital_name']);
    $patient_name = trim($_POST['patient_name']);
    
    // Handle file upload (MANDATORY)
    $document_path = '';
    $file_error = '';
    
    if (isset($_FILES['medical_document']) && $_FILES['medical_document']['error'] == UPLOAD_ERR_OK) {
        // Create documents folder if it doesn't exist
        if (!is_dir('documents')) {
            mkdir('documents', 0777, true);
        }
        
        $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
        $file_extension = strtolower(pathinfo($_FILES['medical_document']['name'], PATHINFO_EXTENSION));
        $max_size = 50 * 1024 * 1024; // 50MB
        
        // Validate file type
        if (!in_array($file_extension, $allowed_extensions)) {
            $file_error = "Invalid file type. Only PDF, JPG, PNG, DOC, DOCX files are allowed.";
        } 
        // Validate file size
        elseif ($_FILES['medical_document']['size'] > $max_size) {
            $file_error = "File size exceeds 50MB limit.";
        }
        // Validate if file is actually uploaded
        elseif (!is_uploaded_file($_FILES['medical_document']['tmp_name'])) {
            $file_error = "File upload failed. Please try again.";
        }
        // Everything is valid
        else {
            $document_name = time() . '_' . uniqid() . '_' . $_FILES['medical_document']['name'];
            $document_path = 'documents/' . $document_name;
            
            // Move uploaded file
            if (!move_uploaded_file($_FILES['medical_document']['tmp_name'], $document_path)) {
                $file_error = "Error saving file. Please try again.";
            }
        }
    } else {
        // Check specific upload errors
        if (isset($_FILES['medical_document'])) {
            switch ($_FILES['medical_document']['error']) {
                case UPLOAD_ERR_NO_FILE:
                    $file_error = "Medical document is required. Please upload a file.";
                    break;
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $file_error = "File is too large. Maximum size is 50MB.";
                    break;
                default:
                    $file_error = "File upload error. Please try again.";
            }
        } else {
            $file_error = "Medical document is required.";
        }
    }
    
    // Validate all fields including file
    if (empty($file_error) && !empty($blood_type) && !empty($quantity) && !empty($urgency) && !empty($reason) && !empty($hospital_name) && !empty($patient_name)) {
        
        // Check if requested blood type is available in inventory
        $available_quantity = isset($inventory_data[$blood_type]) ? $inventory_data[$blood_type] : 0;
        
        if ($available_quantity == 0) {
            $error = "Sorry, $blood_type blood is currently out of stock.";
        } elseif ($quantity > $available_quantity) {
            $error = "Only $available_quantity units of $blood_type blood are available. Please reduce your request quantity.";
        } else {
           $sql = "INSERT INTO blood_requests (recipient_id, blood_type, quantity, urgency, reason, hospital_name, patient_name, document_path, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isssssss", $recipient_id, $blood_type, $quantity, $urgency, $reason, $hospital_name, $patient_name, $document_path);
            
            if ($stmt->execute()) {
                $success = "Blood request submitted successfully! Admin will review your request.";
            } else {
                $error = "Error submitting request. Please try again.";
            }
        }
    } else {
        $error = $file_error ?: "Please fill all required fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Blood - Blood Bank</title>
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
            max-width: 600px;
            margin: 0 auto;
        }
        
        .form-title {
            text-align: center;
            margin-bottom: 25px;
            color: #dc3545;
            font-size: 1.6em;
        }
        
        .form-group {
            margin-bottom: 18px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #333;
            font-weight: bold;
            font-size: 0.95em;
        }
        
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.95em;
        }
        
        .form-group textarea {
            height: 90px;
            resize: vertical;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .btn {
            background: #dc3545;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            width: auto;
            margin-top: 10px;
            display: block;
            margin-left: auto;
            margin-right: auto;
            min-width: 200px;
        }
        
        .btn:hover {
            background: #c0392b;
        }
        
        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            font-size: 0.9em;
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
        
        .section-title {
            color: #dc3545;
            margin: 25px 0 12px 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #dc3545;
            font-size: 1.1em;
        }
        
        .file-input {
            padding: 8px;
            background: #f8f9fa;
            border: 1px dashed #ddd;
            font-size: 0.9em;
        }
        
        small {
            font-size: 0.85em;
            color: #666;
            display: block;
            margin-top: 4px;
        }
        
        .quantity-option {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
        }
        
        .quantity-label {
            font-weight: bold;
        }
        
        .quantity-value {
            color: #28a745;
            font-weight: bold;
        }
        
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li class="active"><a href="request_blood.php">Request Blood</a></li>
                <li><a href="request_status.php">Request Status</a></li>
                <li><a href="my_profile.php">My Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="container">
                <div class="form-title">Request Blood</div>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <h3 class="section-title">Personal & Patient Details</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="patient_name">Patient Name *</label>
                            <input type="text" name="patient_name" id="patient_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="hospital_name">Hospital Name *</label>
                            <input type="text" name="hospital_name" id="hospital_name" required>
                        </div>
                    </div>
                    
                    <h3 class="section-title">Blood Requirements</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="blood_type">Blood Type Needed *</label>
                            <select name="blood_type" id="blood_type" required onchange="updateAvailableQuantity()">
                                <option value="">Select Blood Type</option>
                                <?php 
                                $blood_types = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                                foreach ($blood_types as $type): 
                                    $available = isset($inventory_data[$type]) ? $inventory_data[$type] : 0;
                                ?>
                                    <option value="<?php echo $type; ?>" 
                                            data-available="<?php echo $available; ?>"
                                            <?php echo $available == 0 ? 'disabled style="color: #ccc;"' : ''; ?>>
                                        <?php echo $type; ?> <?php echo $available == 0 ? '(Out of stock)' : '(' . $available . ' units available)'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="quantity">Quantity Needed (Units) *</label>
                            <select name="quantity" id="quantity" required>
                                <option value="">Select Quantity</option>
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?php echo $i; ?>" class="quantity-option">
                                        <span class="quantity-label"><?php echo $i; ?> unit<?php echo $i > 1 ? 's' : ''; ?></span>
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <small id="quantity-help">Select a blood type first to see available quantities</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="urgency">Urgency Level *</label>
                        <select name="urgency" id="urgency" required>
                            <option value="">Select Urgency</option>
                            <option value="Low">Low (Within 1 week)</option>
                            <option value="Medium">Medium (Within 3 days)</option>
                            <option value="High">High (Within 24 hours)</option>
                            <option value="Critical">Critical (Immediately)</option>
                        </select>
                    </div>
                    
                    <h3 class="section-title">Medical Information</h3>
                    <div class="form-group">
                        <label for="reason">Reason for Blood Request *</label>
                        <textarea name="reason" id="reason" required placeholder="Please describe why blood is needed, medical condition, surgery details, etc..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="medical_document">Medical Document *</label>
                        <input type="file" name="medical_document" id="medical_document" class="file-input" 
                               accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                        <small>Upload prescription, medical report, or hospital letter (PDF, JPG, PNG, DOC, DOCX) - <strong style="color: #dc3545;">Required</strong></small>
                    </div>
                    
                    <button type="submit" class="btn">Submit Blood Request</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const requestForm = document.querySelector('form[enctype="multipart/form-data"]');
            const bloodTypeSelect = document.getElementById('blood_type');
            const quantitySelect = document.getElementById('quantity');
            const quantityHelp = document.getElementById('quantity-help');
            
            // Function to update available quantity options
            window.updateAvailableQuantity = function() {
                const selectedOption = bloodTypeSelect.options[bloodTypeSelect.selectedIndex];
                const availableQuantity = selectedOption.getAttribute('data-available');
                
                // Clear current options except the first one
                while (quantitySelect.options.length > 1) {
                    quantitySelect.remove(1);
                }
                
                if (availableQuantity && availableQuantity > 0) {
                    quantityHelp.textContent = `Maximum ${availableQuantity} units available`;
                    quantityHelp.style.color = '#28a745';
                    
                    // Add available quantity options
                    const maxQuantity = Math.min(availableQuantity, 10);
                    for (let i = 1; i <= maxQuantity; i++) {
                        const option = document.createElement('option');
                        option.value = i;
                        option.textContent = `${i} unit${i > 1 ? 's' : ''}`;
                        quantitySelect.appendChild(option);
                    }
                } else {
                    quantityHelp.textContent = 'Selected blood type is out of stock';
                    quantityHelp.style.color = '#dc3545';
                }
            };
            
            requestForm.addEventListener('submit', function(e) {
                const patientName = document.getElementById('patient_name').value.trim();
                const hospitalName = document.getElementById('hospital_name').value.trim();
                const reason = document.getElementById('reason').value.trim();
                const bloodType = document.getElementById('blood_type').value;
                const quantity = document.getElementById('quantity').value;
                const selectedOption = bloodTypeSelect.options[bloodTypeSelect.selectedIndex];
                const availableQuantity = selectedOption.getAttribute('data-available');
                
                let isValid = true;
                let errorMessage = '';
                
                // Patient Name validation
                const nameRegex = /^[a-zA-Z\s]+$/;
                if (!nameRegex.test(patientName)) {
                    isValid = false;
                    errorMessage = 'Patient name should contain only letters (a-z, A-Z) and spaces.';
                }
                
                // Hospital Name validation
                const hospitalRegex = /^[a-zA-Z0-9\s\-,.()&]+$/;
                if (!hospitalRegex.test(hospitalName)) {
                    isValid = false;
                    errorMessage = 'Hospital name should contain only letters, numbers, spaces, and basic punctuation.';
                }
                
                // Reason validation
                if (reason.length < 10) {
                    isValid = false;
                    errorMessage = 'Please provide a detailed reason (at least 10 characters).';
                }
                
                // Blood type validation
                if (!bloodType) {
                    isValid = false;
                    errorMessage = 'Please select a blood type.';
                } else if (availableQuantity == 0) {
                    isValid = false;
                    errorMessage = 'Selected blood type is out of stock. Please choose another blood type.';
                }
                
                // Quantity validation
                if (!quantity) {
                    isValid = false;
                    errorMessage = 'Please select quantity.';
                } else if (parseInt(quantity) > parseInt(availableQuantity)) {
                    isValid = false;
                    errorMessage = `Only ${availableQuantity} units available for selected blood type.`;
                }
                
                // File validation - MANDATORY
                const fileInput = document.getElementById('medical_document');
                if (fileInput.files.length === 0) {
                    isValid = false;
                    errorMessage = 'Medical document is required. Please upload a file.';
                } else {
                    const file = fileInput.files[0];
                    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                    const maxSize = 50 * 1024 * 1024; // 50MB
                    
                    if (!allowedTypes.includes(file.type)) {
                        isValid = false;
                        errorMessage = 'Please upload only PDF, JPG, PNG, DOC, or DOCX files.';
                    }
                    
                    if (file.size > maxSize) {
                        isValid = false;
                        errorMessage = 'File size should be less than 50MB.';
                    }
                }
                
                if (!isValid) {
                    e.preventDefault();
                    alert(errorMessage);
                }
            });
            
            // Real-time validation for text inputs
            const patientNameInput = document.getElementById('patient_name');
            const hospitalNameInput = document.getElementById('hospital_name');
            
            patientNameInput.addEventListener('input', function() {
                const nameRegex = /^[a-zA-Z\s]*$/;
                if (!nameRegex.test(this.value)) {
                    this.style.borderColor = 'red';
                    showFieldError(this, 'Only letters and spaces allowed');
                } else {
                    this.style.borderColor = '';
                    clearFieldError(this);
                }
            });
            
            hospitalNameInput.addEventListener('input', function() {
                const hospitalRegex = /^[a-zA-Z0-9\s\-,.()&]*$/;
                if (!hospitalRegex.test(this.value)) {
                    this.style.borderColor = 'red';
                    showFieldError(this, 'Only letters, numbers, spaces, and basic punctuation allowed');
                } else {
                    this.style.borderColor = '';
                    clearFieldError(this);
                }
            });
            
            function showFieldError(input, message) {
                clearFieldError(input);
                const errorDiv = document.createElement('div');
                errorDiv.className = 'field-error';
                errorDiv.style.color = 'red';
                errorDiv.style.fontSize = '0.8em';
                errorDiv.style.marginTop = '5px';
                errorDiv.textContent = message;
                input.parentNode.appendChild(errorDiv);
            }
            
            function clearFieldError(input) {
                const existingError = input.parentNode.querySelector('.field-error');
                if (existingError) {
                    existingError.remove();
                }
            }
            
            // Initialize quantity options
            updateAvailableQuantity();
        });
    </script>
</body>
</html>