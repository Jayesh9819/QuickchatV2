<?php 
function sendNotification($title, $body, $userIdentity = null, $openLinkUrl = null, $notificationImage = null) {
    $url = 'https://appilix.com/api/push-notification';
    $postData = [
        'app_key' => 'uviwvup6hs71qc94nt2qx2xgeb1jazh9f0s3ndo5',
        'api_key' => 'ianx9ey7wl36usdzpfk4',
        'notification_title' => $title,
        'notification_body' => $body
    ];

    // // Add optional parameters if provided
    // if ($userIdentity) {
    //     $postData['user_identity'] = $userIdentity;
    // // }
    if ($openLinkUrl) {
        $postData['open_link_url'] = $openLinkUrl;
    }
    if ($notificationImage) {
        $postData['notification_image'] = $notificationImage;
    }

    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute the request
    $response = curl_exec($ch);
    curl_close($ch);

    // Handle the response
    if ($response === false) {
        echo "Failed to send notification.";
    } else {
        echo "Notification sent successfully. Response: " . $response;
    }
}

// Example usage
sendNotification('Hello!', 'This is your notification message.', 'specific_user_identity');
