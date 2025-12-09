<?php
session_start();
include '../Logical_Database/connection.php';

// Check if donor is logged in
if (!isset($_SESSION['donor_id'])) {
    header("Location: login.php");
    exit();
}

$donor_id = $_SESSION['donor_id'];
$success = $error = "";

// ========== DONATION COOLDOWN CHECK ==========
$min_days_between_donations = 56; // WHO recommends 56 days (8 weeks)

// Get last approved donation date
$sql_last_donation = "SELECT donation_date FROM blood_donations 
                      WHERE donor_id = ? AND status = 'Approved' 
                      ORDER BY donation_date DESC LIMIT 1";
$stmt_last = $conn->prepare($sql_last_donation);
$stmt_last->bind_param("i", $donor_id);
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
// ========== END COOLDOWN CHECK ==========

// Handle donation submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check eligibility before processing
    if (!$is_eligible) {
        $error = "You are not eligible to donate yet. You can donate again after " . 
                 date('M d, Y', strtotime($next_eligible_date)) . 
                 " ($days_remaining days remaining)";
    } else {
        $blood_type = trim($_POST['blood_type']);
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
        $document_path = '';
        if (isset($_FILES['medical_document']) && $_FILES['medical_document']['error'] == 0) {
            // Create documents folder if it doesn't exist
            if (!is_dir('documents')) {
                mkdir('documents', 0777, true);
            }
            
            // Validate file type
            $allowed_types = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
            $file_extension = strtolower(pathinfo($_FILES['medical_document']['name'], PATHINFO_EXTENSION));
            
            if (!in_array($file_extension, $allowed_types)) {
                $error = "Invalid file type. Please upload PDF, JPG, PNG, DOC, or DOCX files.";
            } else {
                $document_name = time() . '_' . uniqid() . '_' . $_FILES['medical_document']['name'];
                $document_path = 'documents/' . $document_name;
                
                // Move uploaded file
                if (!move_uploaded_file($_FILES['medical_document']['tmp_name'], $document_path)) {
                    $error = "Error uploading medical document. Please try again.";
                }
            }
        }
        
        // Validation for health questionnaire
        if (empty($error) && !empty($blood_type) && !empty($donation_date) && !empty($quantity)) {
            // Check if donor is eligible (basic checks)
            if (empty($health_data['feel_well_today']) || 
                empty($health_data['recent_sickness']) || 
                empty($health_data['medications']) || 
                empty($health_data['travel_history']) || 
                empty($health_data['high_risk_activity'])) {
                $error = "Please answer all health questionnaire questions.";
            }
            else if ($health_data['feel_well_today'] == 'No') {
                $error = "You must be feeling well to donate blood today.";
            } elseif ($health_data['recent_sickness'] == 'Yes') {
                $error = "You cannot donate if you've been sick recently.";
            } else {
                // Insert donation record
                $sql = "INSERT INTO blood_donations (donor_id, blood_type, donation_date, quantity, health_questionnaire, document_path, status) 
                        VALUES (?, ?, ?, ?, ?, ?, 'Pending')";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ississ", $donor_id, $blood_type, $donation_date, $quantity, $health_questionnaire, $document_path);
                
                if ($stmt->execute()) {
                    $success = "Donation request submitted successfully! Admin will review your request.";
                } else {
                    $error = "Error submitting donation request. Please try again.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood donation</title>
    <link rel="stylesheet" href="../profile.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="donate_blood.php">Donate Blood</a></li>
                <li><a href="donation_history.php">Donation History</a></li>
                <li><a href="my_profile.php">My Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="profile-container">
                <h1 class="page-title">Donate Blood</h1>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- ELIGIBILITY NOTICE -->
                <?php if (!$is_eligible): ?>
                    <div class="alert alert-error" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                        <strong>Donation Eligibility Notice:</strong><br>
                        You cannot donate blood at this time. 
                        <strong>Next eligible donation date: <?php echo date('M d, Y', strtotime($next_eligible_date)); ?></strong><br>
                        <small>WHO recommends waiting <?php echo $min_days_between_donations; ?> days between blood donations.</small>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                        <strong>You are eligible to donate blood!</strong><br>
                        <small>Last donation: <?php echo $last_donation ? date('M d, Y', strtotime($last_donation['donation_date'])) : 'No previous donations'; ?></small>
                    </div>
                <?php endif; ?>
                
                <div class="profile-section">
                    <h2 class="section-title">Donation Information</h2>
                    <form method="POST" enctype="multipart/form-data" id="donationForm">
                        <!-- Donor Information (Read-only) -->
                        <div class="profile-info">
                            <div class="info-group">
                                <div class="info-label">Donor Name</div>
                                <div class="info-value"><?php echo $_SESSION['donor_name']; ?></div>
                            </div>
                            
                            <div class="info-group">
                                <div class="info-label">Blood Type</div>
                                <div class="info-value"><?php echo $_SESSION['blood_type']; ?></div>
                                <input type="hidden" name="blood_type" value="<?php echo $_SESSION['blood_type']; ?>">
                            </div>
                        </div>

                        <!-- Donation Details -->
                        <div class="form-group">
                            <label for="donation_date">Preferred Donation Date *</label>
                            <input type="date" name="donation_date" id="donation_date" required 
                                   min="<?php echo date('Y-m-d'); ?>" <?php echo !$is_eligible ? 'disabled' : ''; ?>>
                        </div>
                        
                        <div class="form-group">
                            <label for="quantity">Quantity to Donate (Units) *</label>
                            <select name="quantity" id="quantity" required <?php echo !$is_eligible ? 'disabled' : ''; ?>>
                                <option value="">Select Quantity</option>
                                <option value="1">1 Unit</option>
                                <option value="2">2 Units</option>
                            </select>
                        </div>

                        <!-- Health Questionnaire -->
                        <h3 class="section-title">Health Questionnaire *</h3>
                        
                        <div class="form-group">
                            <label>Are you feeling well and healthy today? *</label>
                            <div>
                                <input type="radio" name="feel_well_today" value="Yes" checked <?php echo !$is_eligible ? 'disabled' : ''; ?>> Yes
                                <input type="radio" name="feel_well_today" value="No" <?php echo !$is_eligible ? 'disabled' : ''; ?>> No
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Have you been sick in the last 2 weeks? *</label>
                            <div>
                                <input type="radio" name="recent_sickness" value="No" checked <?php echo !$is_eligible ? 'disabled' : ''; ?>> No
                                <input type="radio" name="recent_sickness" value="Yes" <?php echo !$is_eligible ? 'disabled' : ''; ?>> Yes
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Are you currently taking any medications? *</label>
                            <div>
                                <input type="radio" name="medications" value="No" checked <?php echo !$is_eligible ? 'disabled' : ''; ?>> No
                                <input type="radio" name="medications" value="Yes" <?php echo !$is_eligible ? 'disabled' : ''; ?>> Yes
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Have you traveled outside the country in the last 3 months? *</label>
                            <div>
                                <input type="radio" name="travel_history" value="No" checked <?php echo !$is_eligible ? 'disabled' : ''; ?>> No
                                <input type="radio" name="travel_history" value="Yes" <?php echo !$is_eligible ? 'disabled' : ''; ?>> Yes
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Have you engaged in high-risk activities? *</label>
                            <div>
                                <input type="radio" name="high_risk_activity" value="No" checked <?php echo !$is_eligible ? 'disabled' : ''; ?>> No
                                <input type="radio" name="high_risk_activity" value="Yes" <?php echo !$is_eligible ? 'disabled' : ''; ?>> Yes
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="medical_document">Medical Document </label>
                            <input type="file" name="medical_document" id="medical_document" class="file-input" 
                                   accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" <?php echo !$is_eligible ? 'disabled' : ''; ?>>
                            <small>Upload medical report of blood test (PDF, JPG, PNG, DOC)</small>
                        </div>

                        <button type="submit" class="btn btn-primary" style="display: block; width: 100%; margin-top: 20px;" 
                                id="submitBtn" <?php echo !$is_eligible ? 'disabled' : ''; ?>>
                            <?php echo $is_eligible ? 'Submit Donation Request' : 'Not Eligible to Donate'; ?>
                        </button>
                        
                        <?php if (!$is_eligible): ?>
                            <div style="color: #721c24; font-weight: bold; text-align: center; margin-top: 10px;">
                                ⚠ Form disabled - You are not eligible to donate until <?php echo date('M d, Y', strtotime($next_eligible_date)); ?>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const donationForm = document.getElementById('donationForm');
            
            // Set minimum date to today
            document.getElementById('donation_date').min = new Date().toISOString().split('T')[0];
            
            <?php if ($is_eligible): ?>
            donationForm.addEventListener('submit', function(e) {
                const donationDate = document.getElementById('donation_date').value;
                const medicalDocument = document.getElementById('medical_document');
                
                let isValid = true;
                let errorMessage = '';
                
                // Date validation
                const today = new Date().toISOString().split('T')[0];
                if (donationDate < today) {
                    isValid = false;
                    errorMessage = 'Donation date cannot be in the past.';
                }
                
                // File validation
                if (medicalDocument.files.length > 0) {
                    const file = medicalDocument.files[0];
                    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                    const maxSize = 50 * 1024 * 1024; // 50MB
                    
                    if (!allowedTypes.includes(file.type)) {
                        isValid = false;
                        errorMessage = 'Please upload only PDF, JPG, PNG, DOC, or DOCX files.';
                    } else if (file.size > maxSize) {
                        isValid = false;
                        errorMessage = 'File size should be less than 50MB.';
                    }
                }
                
                // Health questionnaire validation
                const feelWellToday = document.querySelector('input[name="feel_well_today"]:checked');
                const recentSickness = document.querySelector('input[name="recent_sickness"]:checked');
                const medications = document.querySelector('input[name="medications"]:checked');
                const travelHistory = document.querySelector('input[name="travel_history"]:checked');
                const highRiskActivity = document.querySelector('input[name="high_risk_activity"]:checked');
                
                if (!feelWellToday || !recentSickness || !medications || !travelHistory || !highRiskActivity) {
                    isValid = false;
                    errorMessage = 'Please answer all health questionnaire questions.';
                }
                
                if (!isValid) {
                    e.preventDefault();
                    alert(errorMessage);
                }
            });
            
            // Real-time file validation
            const medicalDocumentInput = document.getElementById('medical_document');
            medicalDocumentInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    const file = this.files[0];
                    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                    const maxSize = 5 * 1024 * 1024;
                    
                    if (!allowedTypes.includes(file.type)) {
                        this.style.borderColor = 'red';
                    } else if (file.size > maxSize) {
                        this.style.borderColor = 'red';
                    } else {
                        this.style.borderColor = '';
                    }
                }
            });
            <?php else: ?>
            // Disable form styling
            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                submitBtn.style.backgroundColor = '#6c757d';
                submitBtn.style.cursor = 'not-allowed';
            }
            <?php endif; ?>
        });
    </script>
</body>
</html>