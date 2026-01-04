<?php
session_start();
include '../Logical_Database/connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    if (!empty($email) && !empty($password)) {
        $query = "SELECT donor_id, full_name, email, password, blood_type FROM donors WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $donor = $result->fetch_assoc();
            
            if (password_verify($password, $donor['password'])) {
                $_SESSION['donor_id'] = $donor['donor_id']; // Changed from 'id' to 'donor_id'
                $_SESSION['donor_email'] = $donor['email'];
                $_SESSION['donor_name'] = $donor['full_name']; 
                $_SESSION['blood_type'] = $donor['blood_type']; 
                $_SESSION['logged_in'] = true;
                
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