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
    <!-- Recipient Signup Form -->
<div class="container">
      <header>
        <div class="logo">LifeBlood Donation</div>
        <div class="tagline">Recipient Portal</div>
    </header>
    <div id="recipient-signup" class="auth-form">
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
        } elseif ($_GET['error'] == 'location_invalid') {
            echo 'Location should contain only letters, numbers, spaces, and basic punctuation.';
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
    <div class="form-title">Recipient Registration</div>
    <form action="signup_process.php" method="POST">
    <div class="form-group">
        <label for="recipient-name">Full Name</label>
        <input type="text" id="recipient-name" name="name" placeholder="Enter your full name" required>
    </div>
    <div class="form-group">
        <label for="recipient-email">Email Address</label>
        <input type="email" id="recipient-email" name="email" placeholder="your.email@example.com" required>
    </div>
    <div class="form-group">
        <label for="recipient-password">Password</label>
        <input type="password" id="recipient-password" name="password" placeholder="Create a password" required>
    </div>
    <div class="form-group">    
        <label for="recipient-location">Location</label>
        <input type="text" id="recipient-location" name="location" placeholder="e.g., City Hospital, New York" required>
    </div>
    <button type="submit" class="submit-btn">Register as Recipient</button>
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
        const name = document.getElementById('recipient-name').value.trim();
        const email = document.getElementById('recipient-email').value.trim();
        const location = document.getElementById('recipient-location').value.trim();
        
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
        
        // Location validation - letters, spaces, numbers, and common punctuation
        const locationRegex = /^[a-zA-Z0-9\s\-,.()]+$/;
        if (!locationRegex.test(location)) {
            isValid = false;
            errorMessage = 'Location should contain only letters, numbers, spaces, and basic punctuation.';
        }
        
        if (!isValid) {
            e.preventDefault();
            alert(errorMessage);
        }
    });
    
    // Real-time validation with user feedback
    const nameInput = document.getElementById('recipient-name');
    const locationInput = document.getElementById('recipient-location');
    
    nameInput.addEventListener('input', function() {
        const nameRegex = /^[a-zA-Z\s]*$/;
        if (!nameRegex.test(this.value)) {
            this.style.borderColor = 'red';
        } else {
            this.style.borderColor = '';
        }
    });
    
    locationInput.addEventListener('input', function() {
        const locationRegex = /^[a-zA-Z0-9\s\-,.()]*$/;
        if (!locationRegex.test(this.value)) {
            this.style.borderColor = 'red';
        } else {
            this.style.borderColor = '';
        }
    });
});
</script>
</body>
</html>