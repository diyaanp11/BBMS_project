<?php 
session_start();
// Remove any existing donor session if coming to signup
if (isset($_SESSION['donor_id'])) {
    unset($_SESSION['donor_id']);
    unset($_SESSION['donor_name']);
    unset($_SESSION['logged_in']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Bank System - Donor Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../login.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">LifeBlood Donation</div>
            <div class="tagline">Donor Portal</div>
        </header>
        
        <div id="donor-signup" class="auth-form">
            <?php 
            // Display error messages
            if (isset($_GET['error'])) {
                echo '<div class="error">';
                
                if (is_array($_GET['error'])) {
                    // Handle multiple errors
                    $error_messages = [];
                    foreach ($_GET['error'] as $error) {
                        switch($error) {
                            case 'email_exists':
                                $error_messages[] = 'Email already exists. Please login instead.';
                                break;
                            case 'empty_fields':
                                $error_messages[] = 'Please fill all fields.';
                                break;
                            case 'name_invalid':
                                $error_messages[] = 'Name should contain only letters (a-z, A-Z) and spaces.';
                                break;
                            case 'email_invalid':
                                $error_messages[] = 'Please enter a valid email address.';
                                break;
                            case 'blood_type_invalid':
                                $error_messages[] = 'Please enter a valid blood type (e.g., O+, AB-, A+, etc.)';
                                break;
                            case 'signup_failed':
                                $error_messages[] = 'Registration failed. Please try again.';
                                break;
                        }
                    }
                    echo implode('<br>', array_unique($error_messages));
                } else {
                    // Handle single error (for backward compatibility)
                    switch($_GET['error']) {
                        case 'email_exists':
                            echo 'Email already exists. Please login instead.';
                            break;
                        case 'empty_fields':
                            echo 'Please fill all fields.';
                            break;
                        case 'name_invalid':
                            echo 'Name should contain only letters (a-z, A-Z) and spaces.';
                            break;
                        case 'email_invalid':
                            echo 'Please enter a valid email address.';
                            break;
                        case 'blood_type_invalid':
                            echo 'Please enter a valid blood type (e.g., O+, AB-, A+, etc.)';
                            break;
                        case 'signup_failed':
                            echo 'Registration failed. Please try again.';
                            break;
                        default:
                            echo 'An error occurred. Please try again.';
                    }
                }
                echo '</div>';
            }
            
            // Display success message
            if (isset($_GET['success'])) {
                echo '<div class="success">';
                echo 'Registration successful! Redirecting to dashboard...';
                echo '</div>';
                echo '<script>
                    setTimeout(function() {
                        window.location.href = "dashboard.php";
                    }, 2000);
                </script>';
            }
            ?>
            
            <div class="form-title">Donor Registration</div>
            
            <form action="signup_process.php" method="POST" id="signupForm">
                <div class="form-group">
                    <label for="donor-name">Full Name</label>
                    <div class="input-with-icon">
                        <input type="text" id="donor-name" name="name" placeholder="Enter your full name" required
                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="donor-email">Email Address</label>
                    <div class="input-with-icon">
                        <input type="email" id="donor-email" name="email" placeholder="your.email@example.com" required
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="donor-password">Password</label>
                    <div class="input-with-icon">
                        <input type="password" id="donor-password" name="password" placeholder="Create a password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('donor-password')">
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="donor-blood-type">Blood Type</label>
                    <div class="input-with-icon">
                        <input type="text" id="donor-blood-type" name="blood_type" placeholder="e.g., O+, AB-" required
                               value="<?php echo isset($_POST['blood_type']) ? htmlspecialchars($_POST['blood_type']) : ''; ?>">
                    </div>
                </div>
                
                <button type="submit" class="submit-btn">Register as Donor</button>
                
                <div class="switch-auth">
                    Already have an account? <a href="login.php">Login here</a>
                </div>
            </form>
            
            <div class="switch-auth">
                <a href="../Frontend/home.php">Back to Home</a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(passwordId) {
            const passwordField = document.getElementById(passwordId);
            const toggleIcon = passwordField.parentElement.querySelector('.password-toggle i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const signupForm = document.getElementById('signupForm');
            const bloodTypeInput = document.getElementById('donor-blood-type');
            
            // Auto-format blood type to uppercase
            bloodTypeInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
            
            // Real-time validation
            const nameInput = document.getElementById('donor-name');
            const emailInput = document.getElementById('donor-email');
            
            nameInput.addEventListener('input', function() {
                const nameRegex = /^[a-zA-Z\s]*$/;
                const errorDisplay = this.parentElement.parentElement.querySelector('.hint');
                
                if (!nameRegex.test(this.value)) {
                    this.style.borderColor = 'red';
                    if (errorDisplay) {
                        errorDisplay.style.color = 'red';
                        errorDisplay.textContent = 'Invalid character detected!';
                    }
                } else {
                    this.style.borderColor = '';
                    if (errorDisplay) {
                        errorDisplay.style.color = '';
                        errorDisplay.textContent = 'Only letters and spaces allowed';
                    }
                }
            });
            
            bloodTypeInput.addEventListener('input', function() {
                const bloodTypeRegex = /^(A|B|AB|O)[+-]?$/i;
                const errorDisplay = this.parentElement.parentElement.querySelector('.hint');
                
                if (!bloodTypeRegex.test(this.value)) {
                    this.style.borderColor = 'red';
                    if (errorDisplay) {
                        errorDisplay.style.color = 'red';
                        errorDisplay.textContent = 'Invalid blood type format!';
                    }
                } else {
                    this.style.borderColor = '';
                    if (errorDisplay) {
                        errorDisplay.style.color = '';
                        errorDisplay.textContent = 'Valid types: A+, A-, B+, B-, AB+, AB-, O+, O-';
                    }
                }
            });
            
            // Form submission validation
            signupForm.addEventListener('submit', function(e) {
                const name = document.getElementById('donor-name').value.trim();
                const email = document.getElementById('donor-email').value.trim();
                const password = document.getElementById('donor-password').value.trim();
                const bloodType = document.getElementById('donor-blood-type').value.trim();
                
                let isValid = true;
                let errorMessages = [];
                
                // Name validation
                const nameRegex = /^[a-zA-Z\s]+$/;
                if (!nameRegex.test(name)) {
                    isValid = false;
                    errorMessages.push('Name should contain only letters (a-z, A-Z) and spaces.');
                }
                
                // Email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    isValid = false;
                    errorMessages.push('Please enter a valid email address.');
                }
                
                // Password validation
                if (password.length < 6) {
                    isValid = false;
                    errorMessages.push('Password should be at least 6 characters long.');
                }
                
                // Blood type validation
                const bloodTypeRegex = /^(A|B|AB|O)[+-]$/i;
                if (!bloodTypeRegex.test(bloodType)) {
                    isValid = false;
                    errorMessages.push('Please enter a valid blood type (e.g., O+, AB-, A+, B-, etc.)');
                }
                
                if (!isValid) {
                    e.preventDefault();
                    alert(errorMessages.join('\n'));
                }
            });
        });
    </script>
</body>
</html>