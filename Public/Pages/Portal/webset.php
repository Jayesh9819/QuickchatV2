<!doctype html>
<html lang="en" dir="ltr">

<head>
    <?php
    include("./Public/Pages/Common/header.php");
    include "./Public/Pages/Common/auth_user.php";

    // Function to echo the script for toastr
    function echoToastScript($type, $message)
    {
        echo "<script type='text/javascript'>document.addEventListener('DOMContentLoaded', function() { toastr['$type']('$message'); });</script>";
    }


    if (isset($_SESSION['toast'])) {
        $toast = $_SESSION['toast'];
        echoToastScript($toast['type'], $toast['message']);
        unset($_SESSION['toast']); // Clear the toast message from session
    }

    if (session_status() !== PHP_SESSION_ACTIVE) session_start();

    // Display error message if available
    if (isset($_SESSION['login_error'])) {
        echo '<p class="error">' . $_SESSION['login_error'] . '</p>';
        unset($_SESSION['login_error']); // Clear the error message
    }
    include "./App/db/db_connect.php"; // Ensure this path is correct for your DB connection script

    // Function to save uploaded files
    function saveUploadedFile($fileInfo) {
        if ($fileInfo['error'] == UPLOAD_ERR_NO_FILE) {
            return null; // No file was uploaded, return null without error
        }
    
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/logo/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
    
        if ($fileInfo['error'] == UPLOAD_ERR_OK) {
            // Secure the file name
            $safeName = preg_replace('/[^a-zA-Z0-9-_\.]/', '', basename($fileInfo['name']));
            $filePath = $uploadDir . $safeName;
            
            if (move_uploaded_file($fileInfo['tmp_name'], $filePath)) {
                return '/uploads/logo/' . $safeName;  // Return the path relative to the document root
            } else {
                throw new Exception("Failed to move the uploaded file.");
            }
        } else {
            throw new Exception("Upload error: " . $fileInfo['error']);
        }
    }
    
    
    // Handling form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
      
    
        try {
            $settings = [
                'name' => $_POST['name'] ?? '',
                'slogan' => $_POST['slogan'] ?? '',
                'logo' => isset($_FILES['logo']) ? saveUploadedFile($_FILES['logo']) : null,
                'icon' => isset($_FILES['icon']) ? saveUploadedFile($_FILES['icon']) : null,
                'loader' => isset($_FILES['loader']) ? saveUploadedFile($_FILES['loader']) : null
            ];
    
            foreach ($settings as $key => $value) {
                if ($value !== null) { // Check if a new value has been provided
                    $sql = "UPDATE websetting SET value = ? WHERE name = ?";
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        throw new Exception("Prepare failed: " . $conn->error);
                    }
                    $stmt->bind_param("ss", $value, $key);
                    if (!$stmt->execute()) {
                        throw new Exception("Execute failed: " . $stmt->error);
                    }
                }
            }
    
            $stmt->close();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    
        // $conn->close();
    }
    
    
    // Fetch current settings
    $currentSettings = [];
    $sql = "SELECT * FROM websetting";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $currentSettings[$row['name']] = $row['value'];
        }
    }


    ?>

</head>

<body class="  ">
    <?php
    include("./Public/Pages/Common/sidebar.php");

    ?>

    <main class="main-content">
        <?php
        include("./Public/Pages/Common/main_content.php");
        ?>
        <div class="content-inner container-fluid pb-0" id="page_layout">
            <div class="container mt-5">
                <h2>Update Website Settings</h2>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $currentSettings['name'] ?? ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="logo">Logo:</label>
                        <input type="file" class="form-control" id="logo" name="logo">
                        <?php if (isset($currentSettings['logo'])) : ?>
                            <img src="<?php echo $currentSettings['logo']; ?>" alt="Current Logo" style="width: 100px; height: auto;">
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="slogan">Slogan:</label>
                        <input type="text" class="form-control" id="slogan" name="slogan" value="<?php echo $currentSettings['slogan'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="icon">Icon:</label>
                        <input type="file" class="form-control" id="icon" name="icon">
                        <?php if (isset($currentSettings['icon'])) : ?>
                            <img src="<?php echo $currentSettings['icon']; ?>" alt="Current Icon" style="width: 50px; height: auto;">
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="loader">Loader GIF:</label>
                        <input type="file" class="form-control" id="loader" name="loader">
                        <?php if (isset($currentSettings['loader'])) : ?>
                            <img src="<?php echo $currentSettings['loader']; ?>" alt="Current Loader" style="width: 100px; height: auto;">
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Settings</button>
                </form>
            </div>

            <!-- Bootstrap JavaScript -->
            <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


        </div>
    </main>
    <?php
    include("./Public/Pages/Common/theme_custom.php");
    ?>
    <?php
    include("./Public/Pages/Common/settings_link.php");

    ?>
    <?php
    include("./Public/Pages/Common/scripts.php");
    ?>

</body>

</html>