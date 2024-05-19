<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
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

        $profilePicture = null;
        if ($_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            // Generate a unique file name
            $fileName = time() . '-' . basename($_FILES['profile_picture']['name']);
            $targetFilePath = $sharedDir . $fileName;

            // Debugging: Check file upload status
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFilePath)) {
                echo "File uploaded successfully.";
                $profilePicture = $fileName;
            } else {
                echo "Error uploading file.";
                exit;
            }
        } else {
            echo "File upload error: " . $_FILES['profile_picture']['error'];
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
        exit;
        header("Location: " . $_SERVER['PHP_SELF']);
    }
    print_r($_SESSION);