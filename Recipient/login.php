<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipient Login - LifeBlood Donation</title>
    <link rel="stylesheet" href="../login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Recipient Login Form -->
    <div class="container">
        <header>
            <div class="logo">LifeBlood Donation</div>
            <div class="tagline">Recipient Portal</div>
        </header>
        
        <div id="recipient-login" class="auth-form">
            <?php if (isset($_GET['error'])): ?>
                <div class="error">
                    <?php 
                    if ($_GET['error'] == 'invalid_credentials') {
                        echo 'Invalid email or password';
                    } elseif ($_GET['error'] == 'empty_fields') {
                        echo 'Please fill all fields';
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="form-title">Recipient Login</div>
            
            <form action="login_process.php" method="POST">
                <div class="form-group">
                    <label for="recipient-login-email">Email Address</label>
                    <div class="input-with-icon">
                        <input type="email" id="recipient-login-email" name="email" placeholder="your.email@example.com" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="recipient-login-password">Password</label>
                    <div class="input-with-icon">
                        <input type="password" id="recipient-login-password" name="password" placeholder="Enter your password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('recipient-login-password')">
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="submit-btn">Login</button>
                
                <div class="switch-auth">
                    Don't have an account? <a href="signup.php">Register here</a>
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
            const toggleIcon = passwordField.nextElementSibling.querySelector('i');
            
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
    </script>
</body>
</html>