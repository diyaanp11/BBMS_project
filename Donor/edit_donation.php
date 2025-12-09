<?php
session_start();
include '../Logical_Database/connection.php';

// Check if donor is logged in
if (!isset($_SESSION['donor_id'])) {
    header("Location: login.php");
    exit();
}

$donor_id = $_SESSION['donor_id'];

// ========== DONATION COOLDOWN CHECK ==========
$min_days_between_donations = 56; // WHO recommends 56 days (8 weeks)

// Get last approved donation date (excluding current one being edited)
$sql_last_donation = "SELECT donation_date FROM blood_donations 
                      WHERE donor_id = ? AND status = 'Approved' AND id != ? 
                      ORDER BY donation_date DESC LIMIT 1";
// We'll prepare this later after we get the donation_id
// ========== END COOLDOWN CHECK ==========

$success = $error = "";

// Get donation ID from URL
$donation_id = $_GET['id'] ?? 0;

// Fetch existing donation data
$donation_data = null;
if ($donation_id) {
    $sql = "SELECT * FROM blood_donations WHERE id = ? AND donor_id = ? AND status = 'Pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $donation_id, $donor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $donation_data = $result->fetch_assoc();

    if (!$donation_data) {
        header("Location: donation_history.php");
        exit();
    }
    
    // Now check cooldown with current donation_id
    $stmt_last = $conn->prepare($sql_last_donation);
    $stmt_last->bind_param("ii", $donor_id, $donation_id);
    $stmt_last->execute();
    $last_donation_result = $stmt_last->get_result();
    $last_donation = $last_donation_result->fetch_assoc();
    
    $next_eligible_date = '';
    $days_remaining = 0;
    $is_eligible = true;
    
    if ($last_donation) {
        $last_date = new DateTime($last_donation['donation_date']);
        $today = new DateTime();
        $interval = $today->diff($last_date);
        $days_since = $interval->days;
        
        if ($days_since < $min_days_between_donations) {
            $days_remaining = $min_days_between_donations - $days_since;
            $next_eligible_date = date('Y-m-d', strtotime($last_donation['donation_date'] . " + $min_days_between_donations days"));
            $is_eligible = false;
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check eligibility before processing
    if (!$is_eligible) {
        echo json_encode(['success' => false, 'message' => "You are not eligible to donate yet. You can donate again after " . date('M d, Y', strtotime($next_eligible_date)) . " ($days_remaining days remaining)"]);
        exit();
    }
    
    $donation_date = trim($_POST['donation_date']);
    $quantity = trim($_POST['quantity']);

    // Health questionnaire
    $health_data = [
        'recent_sickness' => $_POST['recent_sickness'] ?? 'No',
        'medications' => $_POST['medications'] ?? 'No',
        'travel_history' => $_POST['travel_history'] ?? 'No',
        'high_risk_activity' => $_POST['high_risk_activity'] ?? 'No',
        'feel_well_today' => $_POST['feel_well_today'] ?? 'Yes'
    ];

    $health_questionnaire = json_encode($health_data);

    // Handle file upload
    $document_path = $donation_data['document_path'];
    $file_upload_error = '';

    if (isset($_FILES['medical_document']) && $_FILES['medical_document']['error'] == 0) {
        if (!is_dir('documents')) {
            mkdir('documents', 0777, true);
        }

        $allowed_types = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
        $file_extension = strtolower(pathinfo($_FILES['medical_document']['name'], PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_types)) {
            $file_upload_error = "Invalid file type.";
        } else {
            $document_name = time() . '_' . uniqid() . '_' . $_FILES['medical_document']['name'];
            $document_path = 'documents/' . $document_name;

            if (!move_uploaded_file($_FILES['medical_document']['tmp_name'], $document_path)) {
                $file_upload_error = "Error uploading medical document.";
            }
        }
    }

    // Validation
    if (empty($file_upload_error) && !empty($donation_date) && !empty($quantity)) {
        // Check if donor is eligible based on health questionnaire
        if (empty($health_data['feel_well_today']) || 
            empty($health_data['recent_sickness']) || 
            empty($health_data['medications']) || 
            empty($health_data['travel_history']) || 
            empty($health_data['high_risk_activity'])) {
            $response = ['success' => false, 'message' => "Please answer all health questionnaire questions."];
        }
        
        else if ($health_data['feel_well_today'] == 'No') {
            $response = ['success' => false, 'message' => "You must be feeling well to donate blood."];
        } elseif ($health_data['recent_sickness'] == 'Yes') {
            $response = ['success' => false, 'message' => "You cannot donate if you've been sick recently."];
        } else {
            $sql = "UPDATE blood_donations SET 
                    donation_date = ?, 
                    quantity = ?, 
                    health_questionnaire = ?, 
                    document_path = ?,
                    updated_at = NOW() 
                    WHERE id = ? AND donor_id = ? AND status = 'Pending'";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sissii", $donation_date, $quantity, $health_questionnaire, $document_path, $donation_id, $donor_id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $response = ['success' => true, 'message' => 'Donation request updated successfully!'];
            } else {
                $response = ['success' => false, 'message' => 'No changes detected or request already processed.'];
            }
        }
    } else {
        $response = ['success' => false, 'message' => $file_upload_error ?: 'Please fill all required fields.'];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Donation - Blood Bank</title>
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
        
        /* Eligibility Alert */
        .eligibility-alert {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="donate_blood.php">Donate Blood</a></li>
                <li class="active"><a href="donation_history.php">Donation History</a></li>
                <li><a href="my_profile.php">My Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="edit-panel">
                <h2 class="panel-title">Edit Donation Information</h2>
                
                <!-- Eligibility Alert -->
                <?php if (!$is_eligible): ?>
                    <div class="eligibility-alert">
                        <strong>⚠ Donation Eligibility Notice:</strong><br>
                        You cannot donate blood at this time. 
                        <strong>Next eligible donation date: <?php echo date('M d, Y', strtotime($next_eligible_date)); ?></strong><br>
                        <small>WHO recommends waiting <?php echo $min_days_between_donations; ?> days between blood donations.</small>
                    </div>
                <?php endif; ?>
                
                <!-- Donor Information (Read-only) -->
                <div class="form-group">
                    <div class="form-label">Donor Name</div>
                    <div style="padding: 12px 16px; background: #f8f9fa; border-radius: 8px; border: 2px solid #e9ecef;">
                        <?php echo $_SESSION['donor_name']; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="form-label">Blood Type</div>
                    <div style="padding: 12px 16px; background: #f8f9fa; border-radius: 8px; border: 2px solid #e9ecef;">
                        <?php echo $_SESSION['blood_type']; ?>
                    </div>
                </div>

                <!-- Donation Details -->
                <div class="form-group">
                    <label class="form-label">Preferred Donation Date *</label>
                    <input type="date" class="form-input" id="edit-donation-date" name="donation_date" 
                           value="<?php echo $donation_data['donation_date'] ?? ''; ?>" required 
                           min="<?php echo date('Y-m-d'); ?>" <?php echo !$is_eligible ? 'disabled' : ''; ?>>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Quantity to Donate (Units) *</label>
                    <select class="form-select" id="edit-quantity" name="quantity" required <?php echo !$is_eligible ? 'disabled' : ''; ?>>
                        <option value="">Select Quantity</option>
                        <option value="1" <?php echo ($donation_data['quantity'] ?? '') == '1' ? 'selected' : ''; ?>>1 Unit</option>
                        <option value="2" <?php echo ($donation_data['quantity'] ?? '') == '2' ? 'selected' : ''; ?>>2 Units</option>
                    </select>
                </div>

                <!-- Health Questionnaire -->
                <h3 class="section-title">Health Questionnaire *</h3>
                
                <div class="form-group">
                    <label class="form-label">Are you feeling well and healthy today? *</label>
                    <div>
                        <input type="radio" name="feel_well_today" value="Yes" <?php echo ($health_data['feel_well_today'] ?? 'Yes') == 'Yes' ? 'checked' : ''; ?> <?php echo !$is_eligible ? 'disabled' : ''; ?>> Yes
                        <input type="radio" name="feel_well_today" value="No" style="margin-left: 20px;" <?php echo ($health_data['feel_well_today'] ?? '') == 'No' ? 'checked' : ''; ?> <?php echo !$is_eligible ? 'disabled' : ''; ?>> No
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Have you been sick in the last 2 weeks? *</label>
                    <div>
                        <input type="radio" name="recent_sickness" value="No" <?php echo ($health_data['recent_sickness'] ?? 'No') == 'No' ? 'checked' : ''; ?> <?php echo !$is_eligible ? 'disabled' : ''; ?>> No
                        <input type="radio" name="recent_sickness" value="Yes" style="margin-left: 20px;" <?php echo ($health_data['recent_sickness'] ?? '') == 'Yes' ? 'checked' : ''; ?> <?php echo !$is_eligible ? 'disabled' : ''; ?>> Yes
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Are you currently taking any medications? *</label>
                    <div>
                        <input type="radio" name="medications" value="No" <?php echo ($health_data['medications'] ?? 'No') == 'No' ? 'checked' : ''; ?> <?php echo !$is_eligible ? 'disabled' : ''; ?>> No
                        <input type="radio" name="medications" value="Yes" style="margin-left: 20px;" <?php echo ($health_data['medications'] ?? '') == 'Yes' ? 'checked' : ''; ?> <?php echo !$is_eligible ? 'disabled' : ''; ?>> Yes
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Have you traveled outside the country in the last 3 months? *</label>
                    <div>
                        <input type="radio" name="travel_history" value="No" <?php echo ($health_data['travel_history'] ?? 'No') == 'No' ? 'checked' : ''; ?> <?php echo !$is_eligible ? 'disabled' : ''; ?>> No
                        <input type="radio" name="travel_history" value="Yes" style="margin-left: 20px;" <?php echo ($health_data['travel_history'] ?? '') == 'Yes' ? 'checked' : ''; ?> <?php echo !$is_eligible ? 'disabled' : ''; ?>> Yes
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Have you engaged in high-risk activities? *</label>
                    <div>
                        <input type="radio" name="high_risk_activity" value="No" <?php echo ($health_data['high_risk_activity'] ?? 'No') == 'No' ? 'checked' : ''; ?> <?php echo !$is_eligible ? 'disabled' : ''; ?>> No
                        <input type="radio" name="high_risk_activity" value="Yes" style="margin-left: 20px;" <?php echo ($health_data['high_risk_activity'] ?? '') == 'Yes' ? 'checked' : ''; ?> <?php echo !$is_eligible ? 'disabled' : ''; ?>> Yes
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Medical Document</label>
                    <?php if (!empty($donation_data['document_path'])): ?>
                        <div class="current-document">
                            <strong>Current Document:</strong> 
                            <a href="<?php echo $donation_data['document_path']; ?>" target="_blank" style="color: #007bff;">
                                View Current Medical Report
                            </a>
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-input file-input" name="medical_document" 
                           accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" <?php echo !$is_eligible ? 'disabled' : ''; ?>>
                    <small>Upload new medical report (optional - only if you want to change the current one)</small>
                </div>

                <div class="form-actions">
                    <button class="btn btn-cancel" id="cancel-btn">Cancel</button>
                    <button class="btn btn-save" id="save-btn" <?php echo !$is_eligible ? 'disabled' : ''; ?>>
                        <?php echo $is_eligible ? 'Save Changes' : 'Not Eligible'; ?>
                    </button>
                </div>
                
                <?php if (!$is_eligible): ?>
                    <div style="color: #721c24; font-weight: bold; text-align: center; margin-top: 10px;">
                        ⚠ Form disabled - You are not eligible to donate until <?php echo date('M d, Y', strtotime($next_eligible_date)); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="notification" id="notification"></div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const donationDateInput = document.getElementById('edit-donation-date');
    const medicalDocumentInput = document.querySelector('input[name="medical_document"]');
    const saveBtn = document.getElementById('save-btn');
    const cancelBtn = document.getElementById('cancel-btn');
    const notification = document.getElementById('notification');
    
    <?php if (!$is_eligible): ?>
        // Disable form styling
        if (saveBtn) {
            saveBtn.style.backgroundColor = '#6c757d';
            saveBtn.style.cursor = 'not-allowed';
        }
    <?php else: ?>
        // Set minimum date to today
        donationDateInput.min = new Date().toISOString().split('T')[0];

        // Real-time validation
        donationDateInput.addEventListener('change', function() {
            const today = new Date().toISOString().split('T')[0];
            if (this.value < today) {
                this.style.borderColor = 'red';
                showFieldError(this, 'Donation date cannot be in the past');
            } else {
                this.style.borderColor = '';
                clearFieldError(this);
            }
            validateForm();
        });
        
        medicalDocumentInput.addEventListener('change', function() {
            if (this.files.length > 0) {
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

        // Cancel button
        cancelBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')) {
                window.location.href = 'donation_history.php';
            }
        });

        // Save button
        saveBtn.addEventListener('click', function() {
            const donationDate = document.getElementById('edit-donation-date').value;
            const quantity = document.getElementById('edit-quantity').value;
            const feelWellToday = document.querySelector('input[name="feel_well_today"]:checked');
            const recentSickness = document.querySelector('input[name="recent_sickness"]:checked');
            const medications = document.querySelector('input[name="medications"]:checked');
            const travelHistory = document.querySelector('input[name="travel_history"]:checked');
            const highRiskActivity = document.querySelector('input[name="high_risk_activity"]:checked');
            const medicalDocument = document.querySelector('input[name="medical_document"]');

            // Final validation before submitting
            const today = new Date().toISOString().split('T')[0];
            if (donationDate < today) {
                showNotification('Donation date cannot be in the past', 'error');
                return;
            }

            if (!feelWellToday || !recentSickness || !medications || !travelHistory || !highRiskActivity) {
                showNotification('Please answer all health questionnaire questions', 'error');
                return;
            }

            saveBtn.textContent = 'Saving...';
            saveBtn.disabled = true;

            const formData = new FormData();
            formData.append('donation_date', donationDate);
            formData.append('quantity', quantity);
            formData.append('feel_well_today', feelWellToday ? feelWellToday.value : '');
            formData.append('recent_sickness', recentSickness ? recentSickness.value : '');
            formData.append('medications', medications ? medications.value : '');
            formData.append('travel_history', travelHistory ? travelHistory.value : '');
            formData.append('high_risk_activity', highRiskActivity ? highRiskActivity.value : '');
            
            if (medicalDocument.files[0]) {
                formData.append('medical_document', medicalDocument.files[0]);
            }

            fetch('edit_donation.php?id=<?php echo $donation_id; ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => {
                        window.location.href = 'donation_history.php?message=updated';
                    }, 2000);
                } else {
                    showNotification(data.message, 'error');
                    saveBtn.textContent = 'Save Changes';
                    saveBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error updating donation request. Please try again.', 'error');
                saveBtn.textContent = 'Save Changes';
                saveBtn.disabled = false;
            });
        });

        function validateForm() {
            const donationDate = donationDateInput.value;
            const today = new Date().toISOString().split('T')[0];
            
            const isDateValid = donationDate >= today;
            const isHealthQuestionsAnswered = 
                document.querySelector('input[name="feel_well_today"]:checked') &&
                document.querySelector('input[name="recent_sickness"]:checked') &&
                document.querySelector('input[name="medications"]:checked') &&
                document.querySelector('input[name="travel_history"]:checked') &&
                document.querySelector('input[name="high_risk_activity"]:checked');
            
            saveBtn.disabled = !(isDateValid && isHealthQuestionsAnswered);
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
    <?php endif; ?>

    // Notification function
    function showNotification(message, type = 'success') {
        notification.textContent = message;
        notification.className = 'notification ' + type + ' show';
        setTimeout(() => notification.classList.remove('show'), 3000);
    }
    
    // Initial validation
    <?php if ($is_eligible): ?>
        validateForm();
    <?php endif; ?>
});
</script>
</body>
</html>