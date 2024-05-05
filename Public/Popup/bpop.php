<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Set appropriate headers for SSE
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Access-Control-Allow-Origin: *'); // Enable CORS if needed

// Database connection
include '../../App/db/db_connect.php';

// Debugging: Log that the script has started
error_log("Started SSE script");

// Fetch transactions with created_at in the last minute
$role = $_SESSION['role'];
$branch = $_SESSION['branch1'];
$page = $_SESSION['page1'];
if ($role != 'User') {


    if ($role == 'Agent') {
        $pagesArray = explode(", ", $page);
        $quotedPages = [];
        foreach ($pagesArray as $pageName) {
            $quotedPages[] = "'" . mysqli_real_escape_string($conn, $pageName) . "'";
        }
        $whereClause = "page IN (" . implode(", ", $quotedPages) . ")";

        $sql = "SELECT username, redeem FROM transaction WHERE Redeem != 0 AND Redeem IS NOT NULL AND $whereClause AND approval_status=0  AND created_at >= NOW() - INTERVAL 1 MINUTE";
    } elseif ($role == 'Manager' || $role == 'Supervisor') {
        $sql = "SELECT username, redeem FROM transaction WHERE Redeem != 0 AND Redeem IS NOT NULL AND (redeem_status = 0 OR cashout_status = 0) AND branch='$branch' AND approval_status=1 AND updated_at >= NOW() - INTERVAL 1 MINUTE";
    } elseif ($role == 'Admin') {
        $sql = "SELECT username, redeem FROM transaction WHERE Redeem != 0 AND Redeem IS NOT NULL AND (redeem_status = 0 OR cashout_status = 0)  AND updated_at >= NOW() - INTERVAL 1 MINUTE";
    }
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Debugging: Log the number of rows fetched
        error_log("Fetched " . $result->num_rows . " rows");

        // Generate notification message for each transaction
        while ($row = $result->fetch_assoc()) {
            $username = $row['username'];
            $redeemAmount = $row['redeem'];
            $notificationMessage = "You have a new redeem request from $username for amount $redeemAmount";

            // Debugging: Log each notification being sent
            error_log("Sending notification: " . $notificationMessage);

            echo "data: " . json_encode($notificationMessage) . "\n\n";
            flush(); // Flush the output buffer to send the response immediately
            sleep(1); // Sleep for 1 second between events (adjust as needed)
        }
    } else {
        // Debugging: Log no transactions found
        error_log("No new transactions found");
    }
    $conn->close();
}
