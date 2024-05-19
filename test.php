<?php
ob_start();
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Assuming you have your database connection in $conn
// $conn = new mysqli($servername, $username, $password, $dbname);
include './App/db/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $sharedDir = '/var/www/quickchat/data/www/share/profile/';

    // Check if the directory exists
    if (!is_dir($sharedDir)) {
        echo "Directory does not exist. Creating directory...<br>";
        if (!mkdir($sharedDir, 0777, true)) {
            die('Failed to create directories...');
        } else {
            echo "Directory created successfully.<br>";
        }
    } else {
        echo "Directory exists.<br>";
    }

    $profilePicture = null;
    if ($_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        // Generate a unique file name
        $fileName = time() . '-' . basename($_FILES['profile_picture']['name']);
        $targetFilePath = $sharedDir . $fileName;

        // Check file upload status
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFilePath)) {
            echo "File uploaded successfully.<br>";
        } else {
            echo "Error uploading file.<br>";
        }
    } else {
        echo "File upload error: " . $_FILES['profile_picture']['error'] . "<br>";
    }
} else {
    echo "No file uploaded.<br>";
}



print_r($_SESSION);
?>
