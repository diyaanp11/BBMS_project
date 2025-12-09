<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
     <link rel="stylesheet" href="../login.css">
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
        <input type="email" id="recipient-login-email" name="email" placeholder="your.email@example.com" required>
    </div>
    <div class="form-group">
        <label for="recipient-login-password">Password</label>
        <input type="password" id="recipient-login-password" name="password" placeholder="Enter your password" required>
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
</body>
</html>