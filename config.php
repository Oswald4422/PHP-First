<?php
$host = 'localhost';       // Database host
$dbname = 'MemoSync';      // Database name
$username = 'root';        // MySQL username
$password = '';            // MySQL password (empty in your case)

// Create a new MySQLi connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
