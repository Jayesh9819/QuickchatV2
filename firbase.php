<?php
// Include the autoload script from Composer to load the Firebase SDK and other dependencies
require 'vendor/autoload.php';

// Import the necessary Firebase classes
use Kreait\Firebase\Factory;

// Create a new Factory instance for the Firebase SDK
$factory = new Factory();

// Load your service account details from the JSON file you downloaded from Firebase
$factory = $factory->withServiceAccount('key.json');

// Create an instance of the Messaging service
$messaging = $factory->createMessaging();

// Function to send a Firebase notification
function sendFirebaseNotification($token, $title, $body) {
    global $messaging;

    // Prepare the message with a notification payload
    $message = [
        'notification' => [
            'title' => $title,
            'body' => $body
        ],
        'token' => $token  // The device token to which the notification will be sent
    ];

    // Attempt to send the notification and handle any exceptions
    try {
        $messaging->send($message);
        echo "Notification sent successfully to {$token}";
    } catch (Exception $e) {
        echo "Error sending notification: " . $e->getMessage();
    }
}

// Example usage of the function
sendFirebaseNotification('user-specific-fcm-token', 'Hello User!', 'This is your notification body.');
