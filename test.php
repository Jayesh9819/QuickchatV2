<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "./App/db/db_connect.php";

$userId = $_SESSION['user_id'];
$sharedDir = '/var/www/quickchat_bi_usr/data/www/uploads';

// Debug: Check directory existence and permissions
if (!is_dir($sharedDir)) {
    die("Error: Directory does not exist. Cannot continue.<br>");
}
if (!is_writable($sharedDir)) {
    die("Error: Directory is not writable. Check permissions.<br>");
}

echo "Directory exists and is writable.<br>";

$profilePicture = null;
if ($_FILES['p']['error'] === UPLOAD_ERR_OK) {
    // Generate a unique file name
    $fileName = time() . '-' . basename($_FILES['p']['name']);
    $targetFilePath = $sharedDir . $fileName;

    // Debugging
    echo "Attempting to move uploaded file to: $targetFilePath <br>";

    if (move_uploaded_file($_FILES['p']['tmp_name'], $targetFilePath)) {
        echo "File uploaded successfully.<br>";
        $profilePicture = $fileName;  // Store filename in database
    } else {
        echo "Error uploading file.<br>";
        var_dump($_FILES['p']);
        echo "<br>";
        var_dump(error_get_last());
    }
} else {
    echo "File upload error: " . $_FILES['p']['error'] . "<br>";
}

// Update the database with the new profile picture file name
if ($profilePicture) {
    $sql = "UPDATE user SET p_p = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$profilePicture, $userId]);

    // Update session
    $_SESSION['p_p'] = $profilePicture;

    // Success message
    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Profile picture updated successfully'];
    echo "Database updated successfully.<br>";
} else {
    echo "No file uploaded, database not updated.<br>";
}

?>
