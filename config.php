<?php
// Database configuration
$server   = "127.0.0.1:3306";
$username = "root";
$password = "Lensomnang@21";
$database = "mini-mart";

// Create connection
$conn = mysqli_connect($server, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
