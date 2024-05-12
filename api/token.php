<?php
// Include database configuration file
include_once '../App/db/db_connect.php';

// Get user_id and token from POST data
$userId = $_POST['user_id'];
$token = $_POST['token'];

// SQL to insert or update the token
$sql = "INSERT INTO user_tokens (user_id, fcm_token) VALUES (?, ?) ON DUPLICATE KEY UPDATE fcm_token = VALUES(fcm_token)";

// Prepare and bind parameters
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $userId, $token);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "Token stored successfully.";
} else {
    echo "Error storing token.";
}

$stmt->close();
$conn->close();
?>
