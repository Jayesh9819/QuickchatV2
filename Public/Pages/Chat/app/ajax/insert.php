<?php

session_start();
require '../db.conn.php'; // Ensures the database connection file is included

function linkify($text) {
    $urlPattern = '/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|]/i';
    return preg_replace($urlPattern, '<a href="$0" target="_blank">$0</a>', $text);
}

if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

if (isset($_POST['message'], $_POST['to_id'])) {
    $message = htmlspecialchars($_POST['message']);
    $to_id = $_POST['to_id'];
    $reply_id = $_POST['reply_to_id'] ?? null;

    $from_id = $_SESSION['user_id'];
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';

    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to create upload directory']);
        exit;
    }

    $attachmentPath = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $fileName = time() . '-' . basename($_FILES['attachment']['name']);
        $targetFilePath = $uploadDir . $fileName;
        if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $targetFilePath)) {
            echo json_encode(['status' => 'error', 'message' => 'Error uploading file']);
            exit;
        }
        $attachmentPath = $fileName;
    }

    $sql = "INSERT INTO chats (from_id, to_id, message, attachment, reply_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt->execute([$from_id, $to_id, $message, $attachmentPath, $reply_id])) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to insert message']);
        exit;
    }

    // Generate the HTML content for the chat message
    $messageHtml = '<div class="message sent" style="text-align: right; padding-right: 21px;">';
    $messageHtml .= '<div class="message-box" style="display: inline-block; background-color: #dcf8c6; padding: 10px; border-radius: 10px; margin: 5px;">';
    $messageHtml .= '<p style="margin: 0;">' . linkify($message);
    if ($attachmentPath) {
        $messageHtml .= '<img src="../uploads/' . htmlspecialchars($attachmentPath) . '" alt="Attachment" style="max-width:100%;display:block;">';
    }
    $messageHtml .= '</p>';
    $messageHtml .= '<small style="display: block; color: #666; font-size: smaller;">' . date("h:i:s a") . '</small>';
    $messageHtml .= '</div>';
    $messageHtml .= '</div>';

    // Return JSON including the generated HTML
    echo json_encode(['status' => 'success', 'message' => 'Message sent successfully', 'html' => $messageHtml]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Required fields are missing']);
    exit;
}

?>
