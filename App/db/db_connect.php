<?php
ob_start();

$servername = "localhost"; // or your server name
$username = "quickchat";
$password = "12345678";
$dbname = "quickchat";

// Create connectionhh
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    return $conn;
    // echo "success";
}
