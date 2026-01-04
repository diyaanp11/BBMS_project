<?php
session_start();
include '../Logical_Database/connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $location = trim($_POST['location']);
    
    // Server-side validation
    $errors = [];
    
    // Name validation
    if (!preg_match('/^[a-zA-Z\s]+$/', $name)) {
        $errors[] = 'name_invalid';
    }
    
    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'email_invalid';
    }
    
    // Location validation
    if (!preg_match('/^[a-zA-Z0-9\s\-,.()]+$/', $location)) {
        $errors[] = 'location_invalid';
    }
    
    // Check if fields are empty
    if (empty($name) || empty($email) || empty($password) || empty($location)) {
        $errors[] = 'empty_fields';
    }
    
    // If there are validation errors, redirect back
    if (!empty($errors)) {
        $error_params = [];
        foreach ($errors as $error) {
            $error_params[] = "error[]=$error";
        }
        header("Location: signup.php?" . implode('&', $error_params));
        exit();
    }
    
    if (!empty($name) && !empty($email) && !empty($password) && !empty($location)) {
        // Check if email exists
        $check_query = "SELECT recipient_id FROM recipients WHERE email = ?";
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
            // Get the newly created recipient ID
            $recipient_id = $insert_stmt->insert_id;
            
            // Set session variables
            $_SESSION['recipient_id'] = $recipient_id;
            $_SESSION['recipient_name'] = $name;
            $_SESSION['recipient_email'] = $email;
            $_SESSION['recipient_location'] = $location;
            $_SESSION['recipient_loggedin'] = true;
            
            header("Location: dashboard.php?success=1");
            exit();
        } else {
            header("Location: signup.php?error=signup_failed");
            exit();
        }
        
        $check_stmt->close();
        $insert_stmt->close();
    } else {
        header("Location: signup.php?error=empty_fields");
        exit();
    }
    $conn->close();
} else {
    header("Location: signup.php");
    exit();
}
?>