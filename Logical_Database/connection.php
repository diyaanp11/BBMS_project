<?php
// Database Configuration

$servername = getenv('DB_HOST') ?: "localhost";
$username   = getenv('DB_USER') ?: "root";
$password   = getenv('DB_PASS') ?: "";
$dbname     = getenv('DB_NAME') ?: "blood_bank_system";
$port       = getenv('DB_PORT') ?: 3306;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, (int)$port);

// Check connection
if ($conn->connect_error) {
    // Log error instead of showing to users in production
    error_log("Database connection failed: " . $conn->connect_error);
    die("Connection failed: " . ($conn->connect_error));
}

$conn->set_charset("utf8");
?>
