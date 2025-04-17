<?php
// config.php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');      // Change if you use different username
define('DB_PASSWORD', '8036');          // Empty if no password, or your actual password
define('DB_NAME', 'hardware_store');

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Problem: Missing session start
// Solution: Add if using sessions
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}