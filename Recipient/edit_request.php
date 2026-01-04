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

// Get request ID from URL
$request_id = $_GET['id'] ?? 0;

// Fetch existing request data
$request_data = null;
if ($request_id) {
    $sql = "SELECT * FROM blood_requests WHERE id = ? AND recipient_id = ? AND status = 'Pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $request_id, $recipient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $request_data = $result->fetch_assoc();
    
    if (!$request_data) {
        header("Location: request_status.php");
        exit();
    }
}

// Fetch CURRENT blood inventory for ALL blood types
$inventory_sql = "SELECT blood_type, quantity FROM blood_inventory ORDER BY 
                  CASE blood_type 
                      WHEN 'O-' THEN 1
                      WHEN 'O+' THEN 2
                      WHEN 'A-' THEN 3
                      WHEN 'A+' THEN 4
                      WHEN 'B-' THEN 5
                      WHEN 'B+' THEN 6
                      WHEN 'AB-' THEN 7
                      WHEN 'AB+' THEN 8
                      ELSE 9
                  END";
$inventory_result = $conn->query($inventory_sql);
$blood_inventory = [];
while ($row = $inventory_result->fetch_assoc()) {
    $blood_inventory[$row['blood_type']] = $row['quantity'];
}

// Get current inventory for the selected blood type
$current_blood_type = $request_data['blood_type'] ?? '';
$current_inventory = $blood_inventory[$current_blood_type] ?? 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_name = trim($_POST['patient_name']);
    $hospital = trim($_POST['hospital']);
    $blood_type = trim($_POST['blood_type']);
    $quantity = trim($_POST['quantity']);
    $urgency = trim($_POST['urgency']);
    $reason = trim($_POST['reason']);
    
    // Handle file upload - MANDATORY (keep existing if not changed, but validate if new one uploaded)
    $document_path = $request_data['document_path'];
    $file_upload_error = '';

    // Check if a new file is being uploaded
    if (isset($_FILES['medical_document']) && $_FILES['medical_document']['error'] == UPLOAD_ERR_OK) {
        if (!is_dir('documents')) {
            mkdir('documents', 0777, true);
        }
        
        $allowed_types = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
        $file_extension = strtolower(pathinfo($_FILES['medical_document']['name'], PATHINFO_EXTENSION));
        $max_size = 50 * 1024 * 1024; // 50MB
        
        if (!in_array($file_extension, $allowed_types)) {
            $file_upload_error = "Invalid file type. Please upload PDF, JPG, PNG, DOC, or DOCX files.";
        } elseif ($_FILES['medical_document']['size'] > $max_size) {
            $file_upload_error = "File size exceeds 50MB limit.";
        } else {
            // Generate unique filename
            $document_name = time() . '_' . uniqid() . '_' . $_FILES['medical_document']['name'];
            $document_path = 'documents/' . $document_name;
            
            // Delete old file if exists
            if (!empty($request_data['document_path']) && file_exists($request_data['document_path'])) {
                unlink($request_data['document_path']);
            }
            
            if (!move_uploaded_file($_FILES['medical_document']['tmp_name'], $document_path)) {
                $file_upload_error = "Error uploading medical document.";
            }
        }
    } elseif (isset($_FILES['medical_document']) && $_FILES['medical_document']['error'] == UPLOAD_ERR_NO_FILE) {
        // No new file uploaded, but we need to ensure there's already a document
        if (empty($document_path)) {
            $file_upload_error = "Medical document is required. Please upload a file.";
        }
    }

    if (empty($file_upload_error) && !empty($patient_name) && !empty($hospital) && !empty($blood_type) && !empty($quantity) && !empty($urgency) && !empty($reason)) {
        
        // CHECK CURRENT INVENTORY BEFORE UPDATING
        $check_sql = "SELECT quantity FROM blood_inventory WHERE blood_type = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $blood_type);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_row = $check_result->fetch_assoc()) {
            $available_quantity = $check_row['quantity'];
            
            if ($available_quantity < $quantity) {
                echo json_encode(['success' => false, 'message' => "Insufficient inventory! Only $available_quantity units of $blood_type available."]);
                exit();
            } else {
                $sql = "UPDATE blood_requests SET 
        patient_name = ?, 
        hospital_name = ?, 
        blood_type = ?, 
        quantity = ?, 
        urgency = ?, 
        reason = ?,
        document_path = ?,
        updated_at = NOW() 
        WHERE id = ? AND recipient_id = ? AND status = 'Pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssii", $patient_name, $hospital, $blood_type, $quantity, $urgency, $reason, $document_path, $request_id, $recipient_id);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Blood request updated successfully!']);
                    exit();
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error updating request. Please try again.']);
                    exit();
                }
            }
        } else {
            echo json_encode(['success' => false, 'message' => "Selected blood type '$blood_type' is not available in inventory."]);
            exit();
        }
    } else {
        echo json_encode(['success' => false, 'message' => $file_upload_error ?: 'Please fill all required fields.']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Blood Request</title>
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

        .edit-panel {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border: 1px solid #e9ecef;
            max-width: 600px;
            margin: 0 auto;
        }

        .panel-title {
            font-size: 20px;
            margin-bottom: 25px;
            color: #2c3e50;
            font-weight: 600;
            padding-bottom: 15px;
            border-bottom: 2px solid #e53935;
        }

        .form-group {
            margin-bottom: 25px;
            max-width: 500px;
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #495057;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 15px;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #e53935;
            background: white;
            box-shadow: 0 0 0 3px rgba(229, 57, 53, 0.1);
            transform: translateY(-1px);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
            line-height: 1.5;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #e9ecef;
        }

        .btn {
            padding: 10px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            min-width: 120px;
        }

        .btn-cancel {
            background-color: #6c757d;
            color: white;
        }

        .btn-save {
            background: linear-gradient(135deg, #4caf50, #45a049);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .btn-cancel:hover {
            background-color: #5a6268;
        }

        .btn-save:hover {
            background: linear-gradient(135deg, #45a049, #3d8b40);
        }

        /* Notification Style */
        .notification {
            position: fixed;
            top: 25px;
            right: 25px;
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            font-size: 14px;
            transform: translateX(150%);
            transition: transform 0.4s ease;
            z-index: 1000;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .notification.success {
            background: linear-gradient(135deg, #4caf50, #45a049);
        }

        .notification.error {
            background: linear-gradient(135deg, #f44336, #d32f2f);
        }

        .notification.show {
            transform: translateX(0);
        }

        .current-document {
            background: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            margin-top: 5px;
            font-size: 0.9em;
        }

        .file-input {
            margin-top: 10px;
        }
        
        .inventory-info {
            font-size: 0.85em;
            margin-top: 5px;
            padding: 8px;
            border-radius: 5px;
            background: #f8f9fa;
        }
        
        .inventory-good {
            color: #28a745;
            border-left: 3px solid #28a745;
        }
        
        .inventory-warning {
            color: #ffc107;
            border-left: 3px solid #ffc107;
        }
        
        .inventory-danger {
            color: #dc3545;
            border-left: 3px solid #dc3545;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="request_blood.php">Request Blood</a></li>
                <li class="active"><a href="request_status.php">Request Status</a></li>
                <li><a href="my_profile.php">My Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="edit-panel">
                <h2 class="panel-title">Edit Request Information</h2>
                
                <div class="form-group">
                    <label class="form-label">Patient Name:</label>
                    <input type="text" class="form-input" id="edit-patient-name" value="<?php echo htmlspecialchars($request_data['patient_name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Hospital:</label>
                    <input type="text" class="form-input" id="edit-hospital" value="<?php echo htmlspecialchars($request_data['hospital_name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Blood Type:</label>
                    <select class="form-select" id="edit-blood-type">
                        <option value="">Select Blood Type</option>
                        <?php
                        $all_blood_types = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                        $current_blood_type = $request_data['blood_type'] ?? '';
                        
                        foreach ($all_blood_types as $blood_type) {
                            $available = $blood_inventory[$blood_type] ?? 0;
                            $selected = ($blood_type == $current_blood_type) ? 'selected' : '';
                            $disabled = ($available <= 0 && $blood_type != $current_blood_type) ? 'disabled' : '';
                            
                            echo '<option value="' . $blood_type . '" ' . $selected . ' ' . $disabled . ' 
                                  data-quantity="' . $available . '">' . $blood_type . 
                                  ' (' . $available . ' units available)</option>';
                        }
                        ?>
                    </select>
                    <div id="blood-type-info" class="inventory-info">
                        <?php if ($current_inventory > 0): ?>
                            <span class="inventory-good">✓ Current: <?php echo $current_blood_type; ?> has <?php echo $current_inventory; ?> units</span>
                        <?php else: ?>
                            <span class="inventory-danger">✗ <?php echo $current_blood_type; ?> is currently out of stock</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Quantity (units):</label>
                    <select class="form-select" id="edit-quantity">
                        <option value="">Select Quantity</option>
                        <?php
                        $current_quantity = $request_data['quantity'] ?? 1;
                        $max_allowed = min($current_inventory, 10); // Max 10 units per request
                        
                        if ($max_allowed > 0) {
                            for ($i = 1; $i <= $max_allowed; $i++) {
                                $selected = ($i == $current_quantity) ? 'selected' : '';
                                echo '<option value="' . $i . '" ' . $selected . '>' . $i . ' unit' . ($i > 1 ? 's' : '') . '</option>';
                            }
                            
                            // If current quantity is more than allowed, show warning
                            if ($current_quantity > $max_allowed) {
                                echo '<option value="' . $current_quantity . '" selected style="color: #dc3545;">' . 
                                     $current_quantity . ' units (Currently unavailable)</option>';
                            }
                        } else {
                            echo '<option value="' . $current_quantity . '" selected style="color: #dc3545;">' . 
                                 $current_quantity . ' units (Currently unavailable)</option>';
                        }
                        ?>
                    </select>
                    <div id="quantity-info" class="inventory-info">
                        <?php if ($current_inventory > 0): ?>
                            <span class="inventory-good">✓ Can request up to <?php echo min($current_inventory, 10); ?> units</span>
                        <?php else: ?>
                            <span class="inventory-danger">✗ Currently out of stock</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Urgency Level:</label>
                    <select class="form-select" id="edit-urgency">
                        <option value="Low" <?php echo ($request_data['urgency'] ?? '') == 'Low' ? 'selected' : ''; ?>>Low</option>
                        <option value="Medium" <?php echo ($request_data['urgency'] ?? '') == 'Medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="High" <?php echo ($request_data['urgency'] ?? '') == 'High' ? 'selected' : ''; ?>>High</option>
                        <option value="Critical" <?php echo ($request_data['urgency'] ?? '') == 'Critical' ? 'selected' : ''; ?>>Critical</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Reason:</label>
                    <textarea class="form-textarea" id="edit-reason" rows="3"><?php echo htmlspecialchars($request_data['reason'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Medical Document</label>
                    <?php if (!empty($request_data['document_path'])): ?>
                        <div class="current-document">
                            <strong>Current Document:</strong> 
                            <a href="<?php echo $request_data['document_path']; ?>" target="_blank" style="color: #007bff;">
                                View Current Document
                            </a>
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-input file-input" id="edit-medical-document" 
                           accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                    <small><strong style="color: #dc3545;">Required:</strong> Upload new document or keep current one</small>
                </div>
                
                <div class="form-actions">
                    <button class="btn btn-cancel" id="cancel-btn">Cancel</button>
                    <button class="btn btn-save" id="save-btn">Save Changes</button>
                </div>
                
                <!-- Current Inventory Display -->
                <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 8px; border: 1px solid #e9ecef;">
                    <h4 style="color: #495057; margin-bottom: 10px;">Current Blood Inventory</h4>
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;">
                        <?php foreach ($blood_inventory as $type => $qty): 
                            $status_class = $qty > 5 ? 'inventory-good' : ($qty > 0 ? 'inventory-warning' : 'inventory-danger');
                        ?>
                            <div class="<?php echo $status_class; ?>" style="padding: 6px; text-align: center; font-size: 0.9em;">
                                <strong><?php echo $type; ?></strong><br>
                                <?php echo $qty; ?> units
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="notification" id="notification"></div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const patientNameInput = document.getElementById('edit-patient-name');
        const hospitalInput = document.getElementById('edit-hospital');
        const reasonInput = document.getElementById('edit-reason');
        const bloodTypeSelect = document.getElementById('edit-blood-type');
        const quantitySelect = document.getElementById('edit-quantity');
        const saveBtn = document.getElementById('save-btn');
        const cancelBtn = document.getElementById('cancel-btn');
        const notification = document.getElementById('notification');
        const medicalDocumentInput = document.getElementById('edit-medical-document');
        const bloodTypeInfo = document.getElementById('blood-type-info');
        const quantityInfo = document.getElementById('quantity-info');

        // Store blood inventory from PHP
        const bloodInventory = <?php echo json_encode($blood_inventory); ?>;
        const currentBloodType = "<?php echo $current_blood_type; ?>";
        const currentQuantity = <?php echo $request_data['quantity'] ?? 1; ?>;
        const currentInventory = <?php echo $current_inventory; ?>;

        // Function to update quantity options based on selected blood type
        function updateQuantityOptions(bloodType) {
            const availableQty = bloodInventory[bloodType] || 0;
            const maxAllowed = Math.min(availableQty, 10);
            
            // Clear existing options except the first one
            while (quantitySelect.options.length > 1) {
                quantitySelect.remove(1);
            }
            
            // Update info display
            if (availableQty > 0) {
                bloodTypeInfo.innerHTML = `<span class="inventory-good">✓ ${bloodType} has ${availableQty} units available</span>`;
                
                // Add quantity options
                for (let i = 1; i <= maxAllowed; i++) {
                    const option = document.createElement('option');
                    option.value = i;
                    option.textContent = i + ' unit' + (i > 1 ? 's' : '');
                    
                    // Select if it matches current quantity
                    if (i == currentQuantity && bloodType == currentBloodType) {
                        option.selected = true;
                    }
                    
                    quantitySelect.appendChild(option);
                }
                
                quantityInfo.innerHTML = `<span class="inventory-good">✓ Can request up to ${maxAllowed} units</span>`;
                
                // If current quantity exists but exceeds max, add it as disabled option
                if (currentQuantity > maxAllowed && bloodType == currentBloodType) {
                    const warningOption = document.createElement('option');
                    warningOption.value = currentQuantity;
                    warningOption.textContent = currentQuantity + ' units (Currently unavailable)';
                    warningOption.selected = true;
                    warningOption.style.color = '#dc3545';
                    quantitySelect.appendChild(warningOption);
                    
                    quantityInfo.innerHTML = `<span class="inventory-danger">✗ Only ${availableQty} units available (requested ${currentQuantity})</span>`;
                }
            } else {
                bloodTypeInfo.innerHTML = `<span class="inventory-danger">✗ ${bloodType} is out of stock</span>`;
                quantityInfo.innerHTML = `<span class="inventory-danger">✗ Currently unavailable</span>`;
                
                // If this is the current blood type, show current quantity as disabled
                if (bloodType == currentBloodType) {
                    const warningOption = document.createElement('option');
                    warningOption.value = currentQuantity;
                    warningOption.textContent = currentQuantity + ' units (Out of stock)';
                    warningOption.selected = true;
                    warningOption.disabled = true;
                    warningOption.style.color = '#dc3545';
                    quantitySelect.appendChild(warningOption);
                } else {
                    const noStockOption = document.createElement('option');
                    noStockOption.value = "";
                    noStockOption.textContent = "Out of stock";
                    noStockOption.disabled = true;
                    quantitySelect.appendChild(noStockOption);
                }
            }
        }

        // Initialize with current blood type
        updateQuantityOptions(currentBloodType);

        // Update quantity options when blood type changes
        bloodTypeSelect.addEventListener('change', function() {
            updateQuantityOptions(this.value);
            validateForm();
        });

        // Real-time validation
        patientNameInput.addEventListener('input', function() {
            const nameRegex = /^[a-zA-Z\s]*$/;
            if (!nameRegex.test(this.value)) {
                this.style.borderColor = 'red';
                showFieldError(this, 'Only letters and spaces allowed');
            } else {
                this.style.borderColor = '';
                clearFieldError(this);
            }
            validateForm();
        });
        
        hospitalInput.addEventListener('input', function() {
            const hospitalRegex = /^[a-zA-Z0-9\s\-,.()&]*$/;
            if (!hospitalRegex.test(this.value)) {
                this.style.borderColor = 'red';
                showFieldError(this, 'Only letters, numbers, spaces, and basic punctuation allowed');
            } else {
                this.style.borderColor = '';
                clearFieldError(this);
            }
            validateForm();
        });
        
        reasonInput.addEventListener('input', function() {
            if (this.value.length < 10) {
                this.style.borderColor = 'red';
                showFieldError(this, 'Reason should be at least 10 characters');
            } else {
                this.style.borderColor = '';
                clearFieldError(this);
            }
            validateForm();
        });
        
        // File validation - MANDATORY
        medicalDocumentInput.addEventListener('change', function() {
            if (this.files.length === 0) {
                this.style.borderColor = 'red';
                showFieldError(this, 'Medical document is required. Please upload a file.');
            } else {
                const file = this.files[0];
                const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                const maxSize = 50 * 1024 * 1024; // 50MB
                
                if (!allowedTypes.includes(file.type)) {
                    this.style.borderColor = 'red';
                    showFieldError(this, 'Please upload only PDF, JPG, PNG, DOC, or DOCX files');
                } else if (file.size > maxSize) {
                    this.style.borderColor = 'red';
                    showFieldError(this, 'File size should be less than 50MB');
                } else {
                    this.style.borderColor = '';
                    clearFieldError(this);
                }
            }
            validateForm();
        });

        // Update validation function
        function validateForm() {
            const patientName = patientNameInput.value.trim();
            const hospital = hospitalInput.value.trim();
            const reason = reasonInput.value.trim();
            const hasFile = medicalDocumentInput.files.length > 0;
            const bloodType = bloodTypeSelect.value;
            const quantity = quantitySelect.value;
            
            const nameRegex = /^[a-zA-Z\s]+$/;
            const hospitalRegex = /^[a-zA-Z0-9\s\-,.()&]+$/;
            
            const isPatientNameValid = nameRegex.test(patientName);
            const isHospitalValid = hospitalRegex.test(hospital);
            const isReasonValid = reason.length >= 10;
            const isFileValid = hasFile;
            const isBloodTypeValid = bloodType !== '';
            const isQuantityValid = quantity !== '';
            
            // Check if selected quantity exceeds available inventory
            let isQuantityAvailable = true;
            if (bloodType && quantity) {
                const availableQty = bloodInventory[bloodType] || 0;
                if (parseInt(quantity) > availableQty) {
                    isQuantityAvailable = false;
                    quantityInfo.innerHTML = `<span class="inventory-danger">✗ Only ${availableQty} units available</span>`;
                }
            }
            
            saveBtn.disabled = !(isPatientNameValid && isHospitalValid && isReasonValid && 
                                isFileValid && isBloodTypeValid && isQuantityValid && isQuantityAvailable);
        }
        
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

        // Cancel button
        cancelBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')) {
                window.location.href = 'request_status.php';
            }
        });

        // Save button
        saveBtn.addEventListener('click', function() {
            const patientName = document.getElementById('edit-patient-name').value;
            const hospital = document.getElementById('edit-hospital').value;
            const bloodType = document.getElementById('edit-blood-type').value;
            const quantity = document.getElementById('edit-quantity').value;
            const urgency = document.getElementById('edit-urgency').value;
            const reason = document.getElementById('edit-reason').value;
            const medicalDocument = document.getElementById('edit-medical-document');

            // Final validation before submitting
            const nameRegex = /^[a-zA-Z\s]+$/;
            const hospitalRegex = /^[a-zA-Z0-9\s\-,.()&]+$/;
            
            if (!nameRegex.test(patientName)) {
                showNotification('Please enter a valid patient name (letters and spaces only)', 'error');
                return;
            }
            
            if (!hospitalRegex.test(hospital)) {
                showNotification('Please enter a valid hospital name', 'error');
                return;
            }
            
            if (reason.length < 10) {
                showNotification('Please provide a detailed reason (at least 10 characters)', 'error');
                return;
            }
            
            if (!bloodType) {
                showNotification('Please select a blood type', 'error');
                return;
            }
            
            if (!quantity) {
                showNotification('Please select quantity', 'error');
                return;
            }
            
            // Check inventory one more time before submitting
            const availableQty = bloodInventory[bloodType] || 0;
            if (parseInt(quantity) > availableQty) {
                showNotification(`Only ${availableQty} units of ${bloodType} available. Please select less quantity.`, 'error');
                return;
            }

            saveBtn.textContent = 'Saving...';
            saveBtn.disabled = true;

            const formData = new FormData();
            formData.append('patient_name', patientName);
            formData.append('hospital', hospital);
            formData.append('blood_type', bloodType);
            formData.append('quantity', quantity);
            formData.append('urgency', urgency);
            formData.append('reason', reason);
            
            if (medicalDocument.files[0]) {
                formData.append('medical_document', medicalDocument.files[0]);
            }

            fetch('edit_request.php?id=<?php echo $request_id; ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => {
                        window.location.href = 'request_status.php?message=updated';
                    }, 2000);
                } else {
                    showNotification(data.message, 'error');
                    saveBtn.textContent = 'Save Changes';
                    saveBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error updating request. Please try again.', 'error');
                saveBtn.textContent = 'Save Changes';
                saveBtn.disabled = false;
            });
        });

        // Notification function
        function showNotification(message, type = 'success') {
            notification.textContent = message;
            notification.className = 'notification ' + type + ' show';
            setTimeout(() => notification.classList.remove('show'), 3000);
        }
        
        // Initial validation
        validateForm();
    });
    </script>
</body>
</html>