<?php
ob_start();
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Assuming you have your database connection in $conn
// $conn = new mysqli($servername, $username, $password, $dbname);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $sharedDir = '/var/www/quickchat/data/www/share/profile/';

    // Debugging: Check if the directory exists
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

        // Debugging: Check file upload status
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFilePath)) {
            echo "File uploaded successfully.<br>";
            $profilePicture = $fileName;
        } else {
            echo "Error uploading file.<br>";
            exit;
        }
    } else {
        echo "File upload error: " . $_FILES['profile_picture']['error'] . "<br>";
    }

    // Update the database with the new profile picture file name
    $sql = "UPDATE user SET p_p = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Database error: " . $conn->error);
    }
    $stmt->bind_param('si', $profilePicture, $userId);

    if ($stmt->execute()) {
        echo "Database updated successfully.<br>";
    } else {
        echo "Database error: " . $stmt->error . "<br>";
    }
    $stmt->close();

    // Update the session with the new profile picture
    unset($_SESSION['p_p']);
    $_SESSION['p_p'] = $profilePicture;

    // Set a success message and redirect the user
    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Profile picture updated successfully'];
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

print_r($_SESSION);
?>
