<?php
// Include database configuration file
// include '../App/db/db_connect.php';

// Function to send FCM notification
// Function to send FCM notification
function sendFCMNotification($userId, $title, $body)
{
    echo "Executing function...\n";
    echo "User ID: $userId\n";
    echo "Title: $title\n";
    echo "Body: $body\n";

    // Include database connection
    include '../App/db/db_connect.php';

    // Fetch FCM token from database
    $sql = "SELECT fcm_token FROM user_tokens WHERE user_id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Database error: " . $conn->error);
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($token);
    $stmt->fetch();
    $stmt->close();

    // Debugging: Check if token is retrieved
    if (!$token) {
        die("No FCM token found for user ID: $userId");
    }

    echo "FCM Token: $token\n";

    // Prepare FCM request
    $apiKey = 'AAAAfnk_oyY:APA91bE5TDkyJdwr1dTDtNmYAmeZ3-B6nlC_AwcRD3zgFQ4TcosDdq4JPCHFl_pd_CILt-x5H1Fh4NOgPkrVwgzF08wbkz1wZaCvWrui4qy528UVFVky02PRj6Bur5PnKflPbcdxwd63';
    $url = 'https://fcm.googleapis.com/fcm/send';
    $fields = [
        'to' => $token,
        'notification' => [
            'title' => $title,
            'body' => $body,
            'channel_id' => 'high_importance_channel',  // This should match the channel ID in Flutter
            'sound' => 'default'
        ],
        'priority' => 'high'
    ];
    $headers = [
        'Authorization: key=' . $apiKey,
        'Content-Type: application/json'
    ];

    // Debug: Print payload
    echo "Sending request to FCM...\n";
    echo "Payload: " . json_encode($fields) . "\n";

    // cURL Request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        echo "cURL Error: " . curl_error($ch) . "\n";
    }

    curl_close($ch);

    echo "FCM Response: $result\n";

    return $result;
}

// Test function
// echo sendFCMNotification(20, "Hello", "This is a test notification.");
