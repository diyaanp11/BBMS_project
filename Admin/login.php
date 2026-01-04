<?php session_start();?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - LifeBlood Donation</title>
    <link rel="stylesheet" href="../login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">LifeBlood Donation</div>
            <div class="tagline">Admin Portal</div>
        </header>
        
        <div class="auth-form">
            <div class="form-title">Admin Login</div>
            
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
            
            <form action="login_process.php" method="POST">
                <div class="form-group">
                    <label for="admin-email">Email Address</label>
                    <div class="input-with-icon">
                        <input type="email" id="admin-email" name="email" placeholder="admin@example.com" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="admin-password">Password</label>
                    <div class="input-with-icon">
                        <input type="password" id="admin-password" name="password" placeholder="Enter your password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('admin-password')">
                          
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="submit-btn">Login</button>
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