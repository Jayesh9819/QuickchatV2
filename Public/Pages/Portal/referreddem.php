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

                        include './App/db/db_connect.php';
                        $branch = $_SESSION['branch1'];
                        $page = $_SESSION['page1'];

                        if ($role === 'Admin') {
                            $sql = "SELECT * FROM referrecord where type='Withdrawal'";
                            // No parameters needed for Admin
                        }
                        // elseif ($role === 'Manager') {
                        //     $sql = "SELECT * FROM user WHERE Role IN ('Agent', 'User', 'Supervisor') AND branchname='$branch'";
                        //     $params = [];
                        // } elseif ($role === 'Supervisor') {
                        //     $sql = "SELECT * FROM user WHERE Role IN ('Agent', 'User') AND branchname='$branch' ";
                        //     $params = [];
                        // } elseif ($role === 'Agent') {
                        //     $page = $_SESSION['page1'];

                        //     $pagesArray = explode(", ", $page);
                        //     $quotedPages = [];
                        //     foreach ($pagesArray as $pageName) {
                        //         $quotedPages[] = "'" . mysqli_real_escape_string($conn, $pageName) . "'";
                        //     }
                        //     $whereClause = "pagename IN (" . implode(", ", $quotedPages) . ")";
                        //     $sql = "SELECT * FROM user WHERE Role = 'User' AND $whereClause";
                        //     $params = [];
                        // }


                        $result = $conn->query($sql);


                        if ($result->num_rows > 0) {
                        ?>
                            <div class="card-body">
                                <div class="custom-table-effect table-responsive  border rounded">
                                    <table class="table mb-0" id="example">
                                        <thead>
                                            <tr class="bg-white">
                                                <?php
                                                echo '<tr>
                                            <th scope="col">Name</th>
                                            <th scope="col">Amount</th>
                                            <th scope="col">Action</th>
                                            <th scope="col">Created At</th>
                                            </tr>';
                                                ?>
                                        </thead>
                                        <tbody>
                                            <?php
                                            while ($row = $result->fetch_assoc()) {

                                                echo "<tr>
                <td>{$row['username']}</td>
                                <td>{$row['amount']}</td>
                <td>{$row['username']}</td>
                                                    <td>
                                                        <button class='btn btn-success' onclick='approveRequest({$row['id']})'>Approve</button>
                                                        <button class='btn btn-danger' onclick='rejectRequest({$row['id']})'>Reject</button>
                                                    </td>

                <td>{$row['created_at']}</td>
              </tr>";
                                            }
                                            ?>
                                        </tbody>
                                    <?php

                                    // End table
                                    echo '</table>';
                                } else {
                                    echo "0 results";
                                }

                                // Close connection
                                $conn->close();
                                    ?>



                                </div>
                            </div>
                    </div>
                </div>

            </div>
        </div>

        <?
        include("./Public/Pages/Common/footer.php");

        ?>

    </main>
    <script>
        $(document).ready(function() {
            $('#example').DataTable({
                "order": [
                    [8, "desc"]
                ],
                dom: 'Bfrtip', // Add the Bfrtip option to enable buttons

                buttons: [
                    'copy', 'excel', 'pdf'
                ]
            });
        });
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