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

    include './App/db/db_connect.php';

    // Handle Approve Action
    if (isset($_POST['approve_id'])) {
        $approve_id = intval($_POST['approve_id']);
        $sql = "UPDATE referrecord SET status = 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $approve_id);

        if ($stmt->execute()) {
            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Request approved successfully'];
        } else {
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Failed to approve request'];
        }

        $stmt->close();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }

    // Handle Reject Action
    if (isset($_POST['reject_id'])) {
        $reject_id = intval($_POST['reject_id']);
        $sql = "UPDATE referrecord SET status = 2 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $reject_id);

        if ($stmt->execute()) {
            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Request rejected successfully'];
        } else {
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Failed to reject request'];
        }

        $stmt->close();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
    ?>

    <?php
    $role = $_SESSION['role'];
    if (in_array($role, ['Agent', 'Supervisor', 'Manager', 'Admin'])) {
        // The user is a manager, let them stay on the page
        // You can continue to load the rest of the page here
    } else {
        // The user is not a manager, redirect them to the login page
        header('Location: ./Login_to_CustCount'); // Replace 'login.php' with the path to your login page
        exit(); // Prevent further execution of the script
    }
    ?>
</head>

<body class="  ">
    <!-- loader Start -->
    <?php
    // include("./Public/Pages/Common/loader.php");

    ?>
    <!-- loader END -->

    <!-- sidebar  -->
    <?php
    include("./Public/Pages/Common/sidebar.php");

    ?>

    <main class="main-content">
        <?php
        include("./Public/Pages/Common/main_content.php");
        ?>

        <div class="content-inner container-fluid pb-0" id="page_layout">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="box-header with-border">
                                <h3 class="box-title">See All the data</h3>
                                <h6 class="box-subtitle"></h6>
                            </div>
                        </div>
                        <?php

                        $branch = $_SESSION['branch1'];
                        $page = $_SESSION['page1'];

                        if ($role === 'Admin') {
                            $sql = "SELECT * FROM referrecord where type='Withdrawal' and status=0";
                            // No parameters needed for Admin
                        }

                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                        ?>
                            <div class="card-body">
                                <div class="custom-table-effect table-responsive border rounded">
                                    <table class="table mb-0" id="example">
                                        <thead>
                                            <tr class="bg-white">
                                                <th scope="col">Name</th>
                                                <th scope="col">Amount</th>
                                                <th scope="col">Action</th>
                                                <th scope="col">Created At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            while ($row = $result->fetch_assoc()) {
                                                echo "<tr>
                                                    <td>{$row['username']}</td>
                                                    <td>{$row['amount']}</td>
                                                    <td>
                                                        <form method='post' style='display:inline;'>
                                                            <input type='hidden' name='approve_id' value='{$row['id']}'>
                                                            <button class='btn btn-success' type='submit'>Approve</button>
                                                        </form>
                                                        <form method='post' style='display:inline;'>
                                                            <input type='hidden' name='reject_id' value='{$row['id']}'>
                                                            <button class='btn btn-danger' type='submit'>Reject</button>
                                                        </form>
                                                    </td>
                                                    <td>{$row['created_at']}</td>
                                                  </tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php
                        } else {
                            echo "0 results";
                        }

                        $conn->close();
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <?php
        include("./Public/Pages/Common/footer.php");
        ?>

    </main>

    <script>
        $(document).ready(function() {
            $('#example').DataTable({
                "order": [
                    [3, "desc"]
                ],
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'excel', 'pdf'
                ]
            });
        });
    </script>

    <?php
    include("./Public/Pages/Common/theme_custom.php");
    include("./Public/Pages/Common/settings_link.php");
    include("./Public/Pages/Common/scripts.php");
    ?>
</body>

</html>
