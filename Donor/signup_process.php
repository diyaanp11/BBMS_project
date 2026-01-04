<?php
session_start();
include '../Logical_Database/connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $blood_type = trim($_POST['blood_type']);
    
    // Convert blood type to uppercase
    $blood_type = strtoupper($blood_type);
    
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
    if (!preg_match('/^(A|B|AB|O)[+-]$/', $blood_type)) {
        $errors[] = 'blood_type_invalid';
    }
    
    // Check if fields are empty
    if (empty($name) || empty($email) || empty($password) || empty($blood_type)) {
        $errors[] = 'empty_fields';
    }
    
    // If there are validation errors, redirect back
    if (!empty($errors)) {
        // Combine all errors into URL parameters
        $error_params = [];
        foreach ($errors as $error) {
            $error_params[] = "error[]=$error";
        }
        header("Location: signup.php?" . implode('&', $error_params));
        exit();
    }
    
    // Check if email exists
    $check_query = "SELECT donor_id FROM donors WHERE email = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        header("Location: signup.php?error=email_exists");
        exit();
    }
    
    // Hash password and insert
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $insert_query = "INSERT INTO donors (full_name, email, password, blood_type) VALUES (?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("ssss", $name, $email, $hashed_password, $blood_type);
    
    if ($insert_stmt->execute()) {
        // Get the newly created donor ID
        $donor_id = $insert_stmt->insert_id;
        
        // Set session variables
        $_SESSION['donor_id'] = $donor_id;
        $_SESSION['donor_name'] = $name;
        $_SESSION['donor_email'] = $email;
        $_SESSION['blood_type'] = $blood_type;
        $_SESSION['logged_in'] = true;
        
        header("Location: dashboard.php?success=1");
        exit();
    } else {
        header("Location: signup.php?error=signup_failed");
        exit();
    }
    
    $check_stmt->close();
    $insert_stmt->close();
    $conn->close();
} else {
    header("Location: signup.php");
    exit();
}
?>