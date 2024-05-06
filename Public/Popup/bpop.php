<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Set appropriate headers for SSE
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Access-Control-Allow-Origin: *'); // Enable CORS if needed

require_once '../../App/db/db_connect.php';

function sendSSEData($message, $url, $color)
{
    $data = json_encode(['message' => $message, 'url' => $url, 'color' => $color]);
    echo "data: {$data}\n\n";
    flush(); // Ensure the data is sent in real time
}

if (empty($_SESSION['role']) || empty($_SESSION['user_id'])) {
    error_log("Session variables 'role' or 'user_id' not set");
    exit;
}

$role = $_SESSION['role'];
$userid = $_SESSION['user_id'];
$whereClause = '';

if ($role === 'Agent') {
    if (!empty($_SESSION['page1'])) {
        $pagesArray = explode(", ", $_SESSION['page1']);
        $quotedPages = array_map(function ($page) use ($conn) {
            return "'" . mysqli_real_escape_string($conn, $page) . "'";
        }, $pagesArray);

        $whereClause = "AND page IN (" . implode(", ", $quotedPages) . ")";
    }
    $sql = "SELECT username, redeem FROM transaction WHERE Redeem != 0 AND Redeem IS NOT NULL $whereClause AND approval_status = 0 AND created_at >= NOW() - INTERVAL 5 SECOND";
    $url = "./See_Redeem_Request"; // Example URL for viewing redeem requests
    $color = "red"; // High priority notifications in red
} elseif ($role === 'Manager' || $role === 'Supervisor') {
    $branch = $_SESSION['branch1'] ?? '';
    $sql = "SELECT username, redeem FROM transaction WHERE Redeem != 0 AND Redeem IS NOT NULL AND (redeem_status = 0 OR cashout_status = 0) AND branch = '$branch' AND approval_status = 1 AND updated_at >= NOW() - INTERVAL 5 SECOND";
    $url = "./See_Redeem_Request"; // Example URL for viewing redeem requests
    $color = "red"; // High priority notifications in red
} elseif ($role === 'Admin') {
    $sql = "SELECT username, redeem FROM transaction WHERE Redeem != 0 AND Redeem IS NOT NULL AND (redeem_status = 0 OR cashout_status = 0) AND updated_at >= NOW() - INTERVAL 5 SECOND";
    $url = "./See_Redeem_Request"; // Example URL for viewing redeem requests
    $color = "red"; // High priority notifications in red
}

if (isset($sql) && $result = $conn->query($sql)) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $notificationMessage = "You have a new redeem request from {$row['username']} for amount {$row['redeem']}";
            sendSSEData($notificationMessage, $url, $color);
        }
    }
} else {
    error_log("SQL error: " . $conn->error);
}
$sql = "SELECT * FROM chats WHERE opened = 0 AND to_id = $userid";
if ($result = $conn->query($sql)) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $notificationMessage = "You have a new message. Please check your inbox.";
            $url = "./Portal_Chats"; // Assuming there's a generic inbox URL
            $color = "green"; // Choosing green for new messages
            sendSSEData($notificationMessage, $url, $color);
        }
    }
} else {
    error_log("SQL error: " . $conn->error);
}
$sql = "SELECT * FROM transaction WHERE approval_status = 1 AND cashout_status = 1 AND redeem_status = 1 AND branch = '$branch' AND created_at >= NOW() - INTERVAL 5 SECOND";
if ($result = $conn->query($sql)) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $notificationMessage = "Transaction successfully done by {$row['username']} for amount {$row['redeem']}.";
            $approvedby = $row['approved_by'];
            $user=$row['username'];
            $userIDs = [];
            $stmt = $conn->prepare("SELECT id FROM user WHERE username = ?");
            $stmt->bind_param("s", $user);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($user = $result->fetch_assoc()) {
                $userIDs[] = $user['id']; // Add the requesting user's ID
            }
            $stmt->close();

            // Prepare and execute query for the agent who approved
            $stmt = $conn->prepare("SELECT id FROM user WHERE username = ?");
            $stmt->bind_param("s", $approvedBy);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($agent = $result->fetch_assoc()) {
                $userIDs[] = $agent['id']; // Add the approving agent's ID
            }
            $stmt->close();

            // Prepare and execute query for managers and supervisors in the branch
            // Assuming role is stored and can distinguish between Manager and Supervisor
            $stmt = $conn->prepare("SELECT id FROM user WHERE branch = ? AND (role = 'Manager' OR role = 'Supervisor')");
            $stmt->bind_param("s", $branch);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($managerOrSupervisor = $result->fetch_assoc()) {
                $userIDs[] = $managerOrSupervisor['id']; // Add each manager and supervisor's ID
            }
            $stmt->close();

            // Now $userIDs contains all the user IDs that need to receive the notification
            // You can proceed to create notifications for each of these IDs
            foreach ($userIDs as $id) {
                // Here you can insert the notification into your notification table
                $insertStmt = $conn->prepare("INSERT INTO notification (content, by_id, for_id, created_at) VALUES (?, ?, ?, NOW())");
                $insertStmt->bind_param("sii", $notificationMessage, $usernameId, $id); // Assuming $usernameId is the ID of the user who logged in or performed the action
                $insertStmt->execute();
                $insertStmt->close();
            }

            // Optional: Output or further processing
            echo "Notifications sent to all relevant users.";
            $url = "./Portal_Chats";  // Assuming there's a generic inbox URL
            $color = "green";  // Choosing green for notification about successful transactions
            sendSSEData($notificationMessage, $url, $color);
        }
    }
} else {
    error_log("SQL error: " . $conn->error);
}

$conn->close();
