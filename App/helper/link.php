<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

function storeCurrentData()
{
    // Store the current URL if it's not the same as the last stored URL
    $currentUrl = $_SERVER['REQUEST_URI'];
    if (empty($_SESSION['visited_urls']) || end($_SESSION['visited_urls']) !== $currentUrl) {
        $_SESSION['visited_urls'][] = $currentUrl;
    }
    if (!empty($_POST)) {
        $_SESSION['post_data'] = $_POST;
    }
}

function clearStoredData()
{
    unset($_SESSION['previous_url']);
    unset($_SESSION['post_data']);
}

function navigateBack()
{
    // Remove the current URL from the session and redirect to the previous one
    array_pop($_SESSION['visited_urls']); // Remove the current page
    $previousUrl = end($_SESSION['visited_urls']) ?: './Portal'; // Get the last visited URL
    header('Location: ' . $previousUrl);
    exit;
}

// Decide when to store or clear the data based on a condition or specific page visit
if (isset($_GET['return']) && $_GET['return'] == '1') {
    clearStoredData();
    navigateBack();
} else {
    storeCurrentData();
}

// Uncomment to debug session content
 print_r($_SESSION);
?>
