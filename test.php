<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "./App/db/db_connect.php";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['profile_picture'])) {
    $userId = $_SESSION['user_id'];
    $sharedDir = '/var/www/quickchat/data/www/share/profile/';

    // Debugging: Check if the directory exists
    if (!is_dir($sharedDir)) {
        echo "Directory does not exist. Creating directory...";
        if (!mkdir($sharedDir, 0777, true)) {
            die('Failed to create directories...');
        }
    } else {
        echo "Directory exists.";
    }
    if (is_writable($sharedDir)) {
        echo "Directory is writable.<br>";
    } else {
        echo "Directory is not writable.<br>";
        exit;
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
            var_dump($_FILES['profile_picture']);
            echo "<br>";
            var_dump(error_get_last());
        }
    } else {
        echo "File upload error: " . $_FILES['profile_picture']['error'] . "<br>";
    }
    // Update the database with the new profile picture file name
    $sql = "UPDATE user SET p_p = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$profilePicture, $userId]);

    // Update the session with the new profile picture
    unset($_SESSION['p_p']);
    $_SESSION['p_p'] = $profilePicture;
    // Set a success message and redirect the user
    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Profile picture updated successfully'];
    header("Location: " . $_SERVER['PHP_SELF']);
}


?>
