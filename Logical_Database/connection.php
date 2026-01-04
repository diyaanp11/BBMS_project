<?php
$servername = "localhost";
$username = "root";
$password = ""; // XAMPP default is empty
$dbname = "blood_bank_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
else{
    //  echo "Connected successfully";
}
$conn->set_charset("utf8");
?>