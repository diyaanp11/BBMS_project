<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Bank System</title>
    <link rel="stylesheet" href="../login.css">
</head>
<body>
      <!-- Donor Signup Form -->
<div class="container">
    <header>
        <div class="logo">LifeBlood Donation</div>
        <div class="tagline">Donor Portal</div>
    </header>
    <div id="donor-signup" class="auth-form">
     <?php if (isset($_GET['error'])): ?>
    <div class="error">
        <?php 
        if ($_GET['error'] == 'email_exists') {
            echo 'Email already exists. Please login instead.';
        } elseif ($_GET['error'] == 'empty_fields') {
            echo 'Please fill all fields';
        } elseif ($_GET['error'] == 'name_invalid') {
            echo 'Name should contain only letters (a-z, A-Z) and spaces.';
        } elseif ($_GET['error'] == 'email_invalid') {
            echo 'Please enter a valid email address.';
        } elseif ($_GET['error'] == 'blood_type_invalid') {
            echo 'Please enter a valid blood type (e.g., O+, AB-, A+, etc.)';
        } elseif ($_GET['error'] == 'signup_failed') {
            echo 'Registration failed. Please try again.';
        }
        ?>
    </div>
<?php endif; ?>
    <?php if (isset($_GET['success'])): ?>
        <div class="success">
            Registration successful! Please login.
        </div>
    <?php endif; ?>
    <div class="form-title">Donor Registration</div>
    <form action="signup_process.php" method="POST">
        <div class="form-group">
            <label for="donor-name">Full Name</label>
            <input type="text" id="donor-name" name="name" placeholder="Enter your full name" required>
        </div>
        <div class="form-group">
            <label for="donor-email">Email Address</label>
            <input type="email" id="donor-email" name="email" placeholder="your.email@example.com" required>
        </div>
        <div class="form-group">
            <label for="donor-password">Password</label>
            <input type="password" id="donor-password" name="password" placeholder="Create a password" required>
        </div>
        <div class="form-group">
            <label for="donor-blood-type">Blood Type</label>
            <input type="text" id="donor-blood-type" name="blood_type" placeholder="e.g., O+, AB-" required>
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
document.addEventListener('DOMContentLoaded', function() {
    const signupForm = document.querySelector('form[action="signup_process.php"]');
    
    signupForm.addEventListener('submit', function(e) {
        const name = document.getElementById('donor-name').value.trim();
        const email = document.getElementById('donor-email').value.trim();
        const bloodType = document.getElementById('donor-blood-type').value.trim();
        
        let isValid = true;
        let errorMessage = '';
        
        // Name validation - only letters and spaces
        const nameRegex = /^[a-zA-Z\s]+$/;
        if (!nameRegex.test(name)) {
            isValid = false;
            errorMessage = 'Name should contain only letters (a-z, A-Z) and spaces.';
        }
        
        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            isValid = false;
            errorMessage = 'Please enter a valid email address.';
        }
        
        // Blood type validation
        const bloodTypeRegex = /^(A|B|AB|O)[+-]$/i;
        if (!bloodTypeRegex.test(bloodType)) {
            isValid = false;
            errorMessage = 'Please enter a valid blood type (e.g., O+, AB-, A+, B-, etc.)';
        }
        
        if (!isValid) {
            e.preventDefault();
            alert(errorMessage);
        }
    });
    
    // Real-time validation with user feedback
    const nameInput = document.getElementById('donor-name');
    const bloodTypeInput = document.getElementById('donor-blood-type');
    
    nameInput.addEventListener('input', function() {
        const nameRegex = /^[a-zA-Z\s]*$/;
        if (!nameRegex.test(this.value)) {
            this.style.borderColor = 'red';
        } else {
            this.style.borderColor = '';
        }
    });
    
    bloodTypeInput.addEventListener('input', function() {
        const bloodTypeRegex = /^(A|B|AB|O)[+-]$/i;
        if (!bloodTypeRegex.test(this.value)) {
            this.style.borderColor = 'red';
        } else {
            this.style.borderColor = '';
        }
    });
});
</script>
</body>
</html>