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
    include "./Public/Pages/Portal/Components/formcomp.php";
    $role = $_SESSION['role'];
    $gbranch = $_SESSION['branch1'];
    $username = $_SESSION['username'];
    $page = [];
    $page[] = 'ALL';
    $pageopt = []; // Array to hold page options

    if ($role == 'Admin') {
        $resultPage = $conn->query("SELECT branch.name AS bname, page.name FROM branch JOIN page ON page.bid = branch.bid WHERE page.status = 1");
    } else if ($role == 'Agent') {
        $resultPage = $conn->query("SELECT pagename  as name from user where username='$username'");
    } else {
        $resultPage = $conn->query("SELECT branch.name AS bname, page.name FROM branch JOIN page ON page.bid = branch.bid WHERE branch.name = '$gbranch' AND page.status = 1");
    }
    if ($resultPage->num_rows > 0) {
        while ($row = $resultPage->fetch_assoc()) {
            $page[] = $row;
            $pageopt[] = htmlspecialchars($row['name']); // Add each page name to the array
        }
    }
    $branchOpt = [];
    $branchOpt[] = 'ALL';
    $resultBranch = $conn->query("Select name from branch where status=1 ");
    if ($resultBranch->num_rows > 0) {
        while ($row = $resultBranch->fetch_assoc()) {
            $branchOpt[] = htmlspecialchars($row['name']);
        }
    }

    // print_r($page);
    // print_r($pageopt);
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        include './App/db/db_connect.php';
        $title = mysqli_real_escape_string($conn, $_POST['tittle']);
        $content = mysqli_real_escape_string($conn, $_POST['content']);
        $branch = mysqli_real_escape_string($conn, $_POST['branch']);
        $selectedPages = isset($_POST['selectedPages']) ? $_POST['selectedPages'] : [];
        // Check if $selectedPages is not an array
        if (!is_array($selectedPages)) {
            // Convert it to an array with the single element
            $selectedPages = [$selectedPages];
        }
        $serialized = serialize($selectedPages);
        $array = unserialize($serialized);
        $page = implode(", ", $array);    // echo $string;
        $by = $_SESSION['username'];

        // Handle file upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $fileName = mysqli_real_escape_string($conn, $_FILES['image']['name']);
            $fileTmpName = $_FILES['image']['tmp_name'];
            $fileType = $_FILES['image']['type'];
            $fileSize = $_FILES['image']['size'];

            // Define your target directory and file path
            $targetDir = "uploads/";
            $targetFile = $targetDir . basename($fileName);

            // Move the file to your target directory
            if (move_uploaded_file($fileTmpName, $targetFile)) {
                // File is successfully uploaded
                // Insert form data and file name into the database
                $sql = "INSERT INTO offers (name, content, image,by_u,page,branch) VALUES ('$title', '$content', '$fileName','$by','$page','$branch')";

                if (mysqli_query($conn, $sql)) {
                    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Details submitted successfully!'];
                } else {
                    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Error: ' . mysqli_error($conn)];
                }
            } else {
                // Handle error
                $_SESSION['toast'] = ['type' => 'error', 'message' => 'There was an error uploading your file.'];
            }
        } else {
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'No file was uploaded or there was an upload error.'];
        }

        // Redirect to avoid form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }



    ?>
    <style>
        .card-img-top {
            width: 100%;
            /* makes image responsive */
            height: 15vw;
            /* you can set it to a fixed height if you prefer */
            object-fit: contain;
            /* ensures the whole image fits within the box */
            background-color: #FFF;
            /* or any color that matches your design */
        }

        .content-collapse {
            max-height: 4.5em;
            /* Adjust as necessary for the number of lines you want to show */
            overflow: hidden;
            transition: max-height 0.5s ease;
        }

        .content-collapse:hover {
            max-height: none;
            /* When hovered, show all content */
        }
    </style>

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
                <h2 class="mb-3">Submit Your Details</h2>
                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="titleInput" class="form-label">Title</label>
                        <input type="text" name="tittle" class="form-control" id="titleInput" placeholder="Enter title">
                    </div>
                    <div class="mb-3">
                        <label for="contentTextarea" class="form-label">Content</label>
                        <textarea class="form-control" name="content" id="contentTextarea" rows="4" placeholder="Enter content"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="formFile" class="form-label">Upload Image</label>
                        <input class="form-control" name="image" type="file" id="formFile">
                    </div>
                    <?php
                    if ($role == 'Admin') {
                        echo select("Branch name", "branch", "branch", $branchOpt);
                        echo '<div id="checkboxContainer"></div>';
                        echo generateDynamicCheckboxScript('branch', 'checkboxContainer', $page, '');
                    } else {
                        echo generateCheckboxes($pageopt, 'selectedPages');
                    }
                    ?>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
            <?php
            include './App/db/db_connect.php';

            // Assuming $conn is your database connection
            $query = "SELECT * FROM offers";
            $result = mysqli_query($conn, $query);

            if (mysqli_num_rows($result) > 0) {
                echo '<div class="row">'; // Start the Bootstrap row
                while ($row = mysqli_fetch_assoc($result)) {
                    $title = htmlspecialchars($row["name"]); // Escape special characters to prevent XSS
                    $content = htmlspecialchars($row["content"]);
                    $image = htmlspecialchars($row["image"]);
                    $id = htmlspecialchars($row["id"]);
                    $page = htmlspecialchars($row["page"]);
                    $branch = htmlspecialchars($row["branch"]);


                    $imagePath = "../uploads/" . $image; // Adjust the path as needed

                    // Display the data in a Bootstrap card
                    echo "
                    <div class='col-md-4'>
                        <div class='card'>
                            <div class='delete-button-container position-absolute top-0 end-0 p-2'>
                                <button class='btn btn-danger btn-sm' onclick='delete1(\"$id\", \"offers\", \"id\")'>Delete</button>
                            </div>
                            <img src='{$imagePath}' class='card-img-top' alt='{$title}'>
                            <div class='card-body'>
                                <h5 class='card-title'>{$title}</h5>

                                <div class='content-collapse'>
                                    <p class='card-text'>{$content}</p>
                                </div>
                                <button class='btn btn-link' onclick='expandText(this)'>More</button>
                            </div>
                        </div>
                    </div>
                    ";

                    echo "
                    <script>
                    function expandText(button) {
                        var content = button.previousElementSibling;
                        if (button.innerText === 'More') {
                            content.style.maxHeight = 'none';
                            button.innerText = 'Less';
                        } else {
                            content.style.maxHeight = '4.5em';
                            button.innerText = 'More';
                        }
                    }
                    </script>
                    ";
                }
                echo '</div>'; // End the Bootstrap row
            } else {
                echo "No results found.";
            }
            ?>



        </div>
    </main>
    <script>
        function delete1(product_id, table, field) {
            if (confirm("Are you sure you want to Delete")) {
                const xhr = new XMLHttpRequest();
                xhr.open("POST", "../App/Logic/commonf.php?action=delete", true);

                // Set the Content-Type header
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                // Include additional parameters in the data sent to the server
                const data = "id=" + product_id + "&table=" + table + "&field=" + field;

                // Log the data being sent
                console.log("Data sent to server:", data);

                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        console.log("XHR status:", xhr.status);

                        if (xhr.status === 200) {
                            console.log("Response received:", xhr.responseText);

                            try {
                                const response = JSON.parse(xhr.responseText);

                                if (response) {
                                    console.log("Parsed JSON response:", response);

                                    if (response.success) {
                                        alert("Done successfully!");
                                        location.reload();
                                    } else {
                                        alert("Error : " + response.message);
                                    }
                                } else {
                                    console.error("Invalid JSON response:", xhr.responseText);
                                    alert("Invalid JSON response from the server.");
                                }
                            } catch (error) {
                                console.error("Error parsing JSON:", error);
                                alert("Error parsing JSON response from the server.");
                            }
                        } else {
                            console.error("HTTP request failed:", xhr.statusText);
                            alert("Error: " + xhr.statusText);
                        }
                    }
                };

                // Log any network errors
                xhr.onerror = function() {
                    console.error("Network error occurred.");
                    alert("Network error occurred. Please try again.");
                };

                // Send the request
                xhr.send(data);
            }
        }
    </script>
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