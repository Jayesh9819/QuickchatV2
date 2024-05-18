<!doctype html>
<html lang="en" dir="ltr">

<head>
    <?php
    // Start the session at the very beginning
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    include("./Public/Pages/Common/header.php");
    include("./Public/Pages/Common/auth_user.php");

    // Function to echo the script for toastr
    function echoToastScript($type, $message)
    {
        echo "<script type='text/javascript'>document.addEventListener('DOMContentLoaded', function() { toastr['$type']('$message'); });</script>";
    }

    // Check if there is a toast message in the session and display it
    if (isset($_SESSION['toast'])) {
        $toast = $_SESSION['toast'];
        echoToastScript($toast['type'], $toast['message']);
        unset($_SESSION['toast']); // Clear the toast message from session
    }

    // Display login error message if available
    if (isset($_SESSION['login_error'])) {
        echo '<p class="error">' . $_SESSION['login_error'] . '</p>';
        unset($_SESSION['login_error']); // Clear the error message
    }
    ?>
    <style>
    #page_layout {
        background-color: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-top: 20px;
    }
    #page_layout h1, #page_layout h2 {
        color: #333;
    }
    #page_layout p {
        color: #666;
        line-height: 1.6;
    }
    .content-inner {
        max-width: 800px;
        margin: auto;
    }
</style>

</head>

<body>
    <?php
    include("./Public/Pages/Common/sidebar.php");
    ?>

    <main class="main-content">
        <?php
        include("./Public/Pages/Common/main_content.php");
        ?>
        <div class="content-inner container-fluid pb-0" id="page_layout">
            <!-- Privacy Policy Content Starts Here -->
            <h1>Privacy Policy for QuickChat</h1>
            <p>Welcome to QuickChat, your go-to solution for seamless chatting and accounting. This policy outlines our practices regarding data collection, usage, and protection.</p>
            
            <h2>Information Collection</h2>
            <p>We collect personal data such as your name, email address, and contact details through registration. For accounting functionalities, we may also collect transactional data to provide services.</p>
            
            <h2>Use of Information</h2>
            <p>The information collected is used to deliver and enhance our services, including maintaining your account, providing customer support, and improving user experience.</p>
            
            <h2>Sharing of Information</h2>
            <p>Your data may be shared under limited circumstances, such as with service providers who assist us in our operations, or if required by law.</p>
            
            <h2>Data Security</h2>
            <p>We take precautions to protect your data, employing commercially acceptable means to prevent data theft, unauthorized access, and disclosure.</p>
            
            <h2>User Rights</h2>
            <p>You have rights to access, correct, or delete your personal information that we store, which can be done through your account settings or contacting support.</p>
            
            <h2>Changes to This Policy</h2>
            <p>We may update our Privacy Policy from time to time and will notify you of any changes by posting the new policy on this page.</p>
            
            <h2>Contact Us</h2>
            <p>If you have any questions about this Privacy Policy, please contact us.</p>
            <!-- Privacy Policy Content Ends Here -->
        </div>
    </main>
    
    <?php
    include("./Public/Pages/Common/theme_custom.php");
    include("./Public/Pages/Common/settings_link.php");
    include("./Public/Pages/Common/scripts.php");
    ?>
</body>

</html>
