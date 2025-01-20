<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "./App/db/db_connect.php";

$userId = $_SESSION['user_id'];
$sharedDir = '/var/www/quickchat_bi_usr/data/www/uploads';

// Ensure directory exists
if (!is_dir($sharedDir)) {
    if (!mkdir($sharedDir, 0777, true)) {
        die('Failed to create directory...');
    }
}

// Check if directory is writable
if (!is_writable($sharedDir)) {
    die('Directory is not writable.');
}

$profilePicture = null;

// Handle file upload
if (!empty($_FILES['p']['name']) && $_FILES['p']['error'] === UPLOAD_ERR_OK) {
    $fileName = time() . '-' . basename($_FILES['p']['name']);
    $targetFilePath = $sharedDir . $fileName;

    if (move_uploaded_file($_FILES['p']['tmp_name'], $targetFilePath)) {
        $profilePicture = $fileName;
    } else {
        echo "Error uploading file.";
        var_dump($_FILES['p']);
        var_dump(error_get_last());
        exit;
    }
}

// Handle icon selection if no file is uploaded
if (empty($profilePicture) && !empty($_POST['icon'])) {
    $profilePicture = $_POST['icon'];  // Store the selected icon name
}

// Update the database with the new profile picture
if ($profilePicture) {
    $sql = "UPDATE user SET p_p = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$profilePicture, $userId]);

    // Update session
    $_SESSION['p_p'] = $profilePicture;
    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Profile picture updated successfully'];
} else {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'No profile picture selected'];
}

// Redirect to avoid form resubmission
exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Profile Picture</title>
</head>
<body>

<h2>Upload or Select a Profile Picture</h2>

<form action="" method="POST" enctype="multipart/form-data">
    <label for="p">Upload Profile Picture:</label>
    <input type="file" name="p" id="p"><br><br>

    <p>Select an Icon:</p>
    <label>
        <input type="radio" name="icon" value="icon1.png">
        <img src="icons/icon1.png" width="50" alt="Icon 1">
    </label>
    <label>
        <input type="radio" name="icon" value="icon2.png">
        <img src="icons/icon2.png" width="50" alt="Icon 2">
    </label>
    <label>
        <input type="radio" name="icon" value="icon3.png">
        <img src="icons/icon3.png" width="50" alt="Icon 3">
    </label>

    <br><br>
    <button type="submit">Save Profile Picture</button>
</form>

</body>
</html>
