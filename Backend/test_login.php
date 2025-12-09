<?php
include '../Logical_Database/connection.php';

if ($conn) {
    echo "Database connected successfully!";
    
    // Test if tables exist
    $tables = ['admins', 'donors', 'recipients'];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "<br>Table '$table' exists";
        } else {
            echo "<br>Table '$table' missing";
        }
    }
} else {
    echo "Database connection failed!";
}
?>