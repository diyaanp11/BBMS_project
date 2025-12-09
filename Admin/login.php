<?php session_start();?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Bank System</title>
</head>
<style>
     * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            width: 100%;
            max-width: 500px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            min-height: 600px;
        }
        
        header {
            background: #dc3545;
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .logo {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .tagline {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .content {
            display: flex;
            flex: 1;
        }
        
        
        .right-panel {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .form-title {
            font-size: 24px;
            margin-bottom: 30px;
            color: #343a40;
            text-align: center;
        }
         .error {
            color: #dc3545;
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            background: #ffeaea;
            border-radius: 5px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            border-color: #dc3545;
            outline: none;
        }
        
        .submit-btn {
            width: 100%;
            padding: 15px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .submit-btn:hover {
            background: #c82333;
        }
        
        .switch-auth {
            text-align: center;
            margin-top: 20px;
            color: #6c757d;
        }
        
        .switch-auth a {
            color: #dc3545;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
        }

        .switch-auth a:hover {
            text-decoration: underline;
        }
        
 
</style>
<body>
    <div class="container">
    <header>
        <div class="logo">LifeBlood Donation</div>
        <div class="tagline">Admin Portal</div>
    </header>
    <div class="content">
        <div class="right-panel">
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
                <input type="email" id="admin-email" name="email" placeholder="admin@example.com" required>
            </div>
            <div class="form-group">
                <label for="admin-password">Password</label>
                <input type="password" id="admin-password" name="password" placeholder="Enter your password" required>
            </div>
                <button type="submit" class="submit-btn">Login</button>
        </form>
            <div class="switch-auth">
                <a href="../Frontend/home.php">Back to Home</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>