<?php
session_start();
include '../Logical_Database/connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $blood_type = trim($_POST['blood_type']); 
    
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
    
    // Blood type validation
    if (!preg_match('/^(A|B|AB|O)[+-]$/i', $blood_type)) {
        $errors[] = 'blood_type_invalid';
    }
    
    // Check if fields are empty
    if (empty($name) || empty($email) || empty($password) || empty($blood_type)) {
        $errors[] = 'empty_fields';
    }
    
    // If there are validation errors, redirect back
    if (!empty($errors)) {
        if (in_array('name_invalid', $errors)) {
            header("Location: signup.php?error=name_invalid");
        } elseif (in_array('email_invalid', $errors)) {
            header("Location: signup.php?error=email_invalid");
        } elseif (in_array('blood_type_invalid', $errors)) {
            header("Location: signup.php?error=blood_type_invalid");
        } else {
            header("Location: signup.php?error=empty_fields");
        }
        exit();
    }
    
    // If validation passes, continue with database operations
    if (!empty($name) && !empty($email) && !empty($password) && !empty($blood_type)) {
        // Check if email exists
        $check_query = "SELECT id FROM donors WHERE email = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            header("Location: signup.php?error=email_exists");
            exit();
        }
        
        // Hash password and insert
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $insert_query = "INSERT INTO donors (full_name, email, password, blood_type) VALUES (?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("ssss", $name, $email, $hashed_password, $blood_type);
        
        if ($insert_stmt->execute()) {
            header("Location: dashboard.php?success=1");
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