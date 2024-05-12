<?php
// Include database configuration file
include_once '../App/db/db_connect.php';

$userId = $_GET['id'];  // Example user ID

// Fetch the user's token
$sql = "SELECT fcm_token FROM user_tokens WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($token);
$stmt->fetch();
$stmt->close();

// Send notification if token exists
if ($token) {
    sendFCMNotification($token, "Hello", "You have a new message!");
} else {
    echo "No token found.";
}

function sendFCMNotification($fcmToken, $title, $body) {
    $apiKey = 'AAAAfnk_oyY:APA91bE5TDkyJdwr1dTDtNmYAmeZ3-B6nlC_AwcRD3zgFQ4TcosDdq4JPCHFl_pd_CILt-x5H1Fh4NOgPkrVwgzF08wbkz1wZaCvWrui4qy528UVFVky02PRj6Bur5PnKflPbcdxwd63';
    $url = 'https://fcm.googleapis.com/fcm/send';
    $fields = [
        'to' => $fcmToken,
        'notification' => [
            'title' => $title,
            'body' => $body
        ],
        'priority' => 'high'
    ];
    $headers = [
        'Authorization: key=' . $apiKey,
        'Content-Type: application/json'
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}
?>
