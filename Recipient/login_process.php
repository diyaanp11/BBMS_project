<?php
session_start();
include '../Logical_Database/connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    if (!empty($email) && !empty($password)) {
        $query = "SELECT * FROM recipients WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $recipient = $result->fetch_assoc(); 
            
         if (password_verify($password, $recipient['password'])) {
    $_SESSION['recipient_id'] = $recipient['id'];
    $_SESSION['recipient_email'] = $recipient['email'];
    $_SESSION['recipient_name'] = $recipient['full_name']; // FIXED
    $_SESSION['recipient_location'] = $recipient['location']; // Correct
    $_SESSION['recipient_loggedin'] = true;

    header("Location: dashboard.php");
    exit();
}
 else {
                header("Location: login.php?error=invalid_credentials");
                exit();
            }
        } else {
            header("Location: login.php?error=invalid_credentials");
            exit();
        }
    } else {
        header("Location: login.php?error=empty_fields");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>