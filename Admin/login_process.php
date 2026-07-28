<?php
session_start();
include '../Logical_Database/connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    if (!empty($email) && !empty($password)) {
        $query = "SELECT admin_id, username, email, password FROM admins WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $admin = $result->fetch_assoc();
            
            // Verify password using password_verify
            if (password_verify($password, $admin['password']) || $password === $admin['password']) {
                // If plaintext match worked but verify didn't, rehash the password
                if (!password_verify($password, $admin['password'])) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = $conn->prepare("UPDATE admins SET password = ? WHERE admin_id = ?");
                    $updateStmt->bind_param("si", $newHash, $admin['admin_id']);
                    $updateStmt->execute();
                    $updateStmt->close();
                }
                
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_name'] = $admin['username'];
                $_SESSION['admin_loggedin'] = true;
                
                header("Location: dashboard.php");
                exit();
            } else {
                header("Location: login.php?error=invalid_credentials");
                exit();
            }
        } else {
            header("Location: login.php?error=invalid_credentials");
            exit();
        }
        $stmt->close();
    } else {
        header("Location: login.php?error=empty_fields");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>
