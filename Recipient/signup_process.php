<?php
session_start();
include '../Logical_Database/connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $location = trim($_POST['location']); 
    
    if (!empty($name) && !empty($email) && !empty($password) && !empty($location)) {
        // Check if email exists
        $check_query = "SELECT id FROM recipients WHERE email = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            header("Location: signup.php?error=email_exists");
            exit();
        }
        
        // Hash password and insert
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $insert_query = "INSERT INTO recipients (full_name, email, password, location) VALUES (?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("ssss", $name, $email, $hashed_password, $location);
        
        if ($insert_stmt->execute()) {
            header("Location: login.php?success=1");
            exit();
        } else {
            header("Location: signup.php?error=signup_failed");
            exit();
        }
    } else {
        header("Location: signup.php?error=empty_fields");
        exit();
    }
} else {
    header("Location: signup.php");
    exit();
}
?>