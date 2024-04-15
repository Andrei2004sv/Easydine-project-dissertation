<?php
// Database connection settings
$host = 'localhost';
$username = 'anne';
$password = 'new_password';
$database = 'mysqli_connection';

// Create connection
$mysqli = new mysqli($host, $username, $password, $database);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
?>
