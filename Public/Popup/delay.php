<?php
// Start the session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

// Include database connection settings
require_once '../../App/db/db_connect.php';

// Prepare and execute a query to the database
$sql = "SELECT COUNT(*) AS unread_count FROM chats WHERE opened = 0 AND to_id = ? ";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Check for errors
if ($row >=0) {
    $data = [
        'message' => "You have {$row['unread_count']} new unread messages.",
        'color' => 'blue' // You can dynamically set this based on conditions
    ];
    echo json_encode($data);
} else {
    echo json_encode(['error' => $conn->error]);
}

// Close the connection
$stmt->close();
$conn->close();
?>
