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
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        foreach ($_POST as $key => $value) {
            $_SESSION[$key] = $value;
        }
    }

    // Store GET parameters in session
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        foreach ($_GET as $key => $value) {
            $_SESSION[$key] = $value;
        }
    }
    // $_SESSION['timezone'] = 'America/New_York';
    if (isset($_SESSION['timezone'])) {
        $selectedTimezone = $_SESSION['timezone'];
        // Set the default timezone to the selected timezone
        date_default_timezone_set($selectedTimezone);
    }

    if (!isset($_SESSION['time_filter']) || $_SESSION['time_filter'] == "") {
        $_SESSION['time_filter'] = "Custom";
    }
    // Assuming start_date and end_date are being sent to the server
    $start_date = $_SESSION['start_date'] ?? null;
    $end_date = $_SESSION['end_date'] ?? null;
    $selectedTimezone = $_SESSION['timezone'] ?? 'UTC';

    // Convert user input dates from the selected timezone to UTC (for example)
    function convertToUTC($dateStr, $timezoneStr)
    {
        $date = new DateTime($dateStr, new DateTimeZone($timezoneStr));
        $date->setTimezone(new DateTimeZone('UTC')); // Convert to UTC
        return $date->format('Y-m-d H:i:s'); // Return the formatted date
    }

    if ($start_date) {
        $start_date = convertToUTC($start_date, $selectedTimezone);
    }

    if ($end_date) {
        $end_date = convertToUTC($end_date, $selectedTimezone);
    }

    // Now, $start_date and $end_date are in UTC, ready to be used in your SQL query


    ?>
    <!-- css -->
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        .cashin {
            color: green;
        }

        .cashout {
            color: red;
        }
    </style>


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
            <br>

            <form method="GET" action="#">
                <input type="hidden" name="u" value="<?php echo isset($_SESSION['u']) ? htmlspecialchars($_SESSION['u']) : ''; ?>">
                <input type="hidden" name="r" value="<?php echo isset($_SESSION['r']) ? htmlspecialchars($_SESSION['r']) : ''; ?>">
                <input type="hidden" name="page" value="<?php echo isset($_SESSION['page']) ? htmlspecialchars($_SESSION['page']) : ''; ?>">
                <input type="hidden" name="branch" value="<?php echo isset($_SESSION['branch']) ? htmlspecialchars($_SESSION['branch']) : ''; ?>">

                <div class="form-row align-items-center">
                    <div class="col-auto">
                        <label for="start_date" class="col-form-label">Start Date and Time:</label>
                        <input type="datetime-local" class="form-control" id="start_date" name="start_date" value="<?php echo isset($_SESSION['start_date']) ? htmlspecialchars(str_replace(' ', 'T', $_SESSION['start_date'])) : ''; ?>">
                    </div>
                    <div class="col-auto">
                        <label for="end_date" class="col-form-label">End Date and Time:</label>
                        <input type="datetime-local" class="form-control" id="end_date" name="end_date" value="<?php echo isset($_SESSION['end_date']) ? htmlspecialchars(str_replace(' ', 'T', $_SESSION['end_date'])) : ''; ?>">
                    </div>
                    <div class="col-auto">
                        <label for="timezone" class="col-form-label">Select Timezone:</label>
                        <select name="timezone" id="timezone" class="form-control">
                            <?php
                            $timezones = [
                                'America/New_York',
                                'America/Chicago',
                                'America/Denver',
                                'America/Phoenix',
                                'America/Los_Angeles',
                                'America/Anchorage',
                                'Asia/Kolkata',
                                'Africa/Cairo',
                                'Africa/Accra',
                                'America/Honolulu'
                            ];
                            foreach ($timezones as $timezone) {
                                $selected = ($_SESSION['timezone'] ?? 'America/New_York') === $timezone ? ' selected' : '';
                                echo "<option value=\"$timezone\"$selected>$timezone</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label for="time_filter" class="col-form-label">Select Time Filter:</label>
                        <select name="time_filter" id="time_filter" class="form-control">
                            <option value="custom" <?php echo ($_SESSION['time_filter'] == 'custom') ? 'selected' : ''; ?>>ALL</option>
                            <option value="1" <?php echo ($_SESSION['time_filter'] == '1') ? 'selected' : ''; ?>>1 Hour</option>
                            <option value="2" <?php echo ($_SESSION['time_filter'] == '2') ? 'selected' : ''; ?>>2 Hours</option>
                            <option value="24" <?php echo ($_SESSION['time_filter'] == '24') ? 'selected' : ''; ?>>24 Hours</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </div>
            </form>


            <!-- /.box-header -->
            <div class="box-body">
                <!-- <div class="table-responsive"> -->
                <!-- <table id="example" class="table table-bordered table-hover display nowrap margin-top-10 w-p100">

                    </table> -->

                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">See All the data</h3>
                        <h6 class="box-subtitle">All The Records</h6>
                    </div>



                    <?php
                    include "./App/db/db_connect.php";
                    $role = $_SESSION['role'];
                    $bra = $_SESSION['branch1'];
                    $pag = $_SESSION['page1'];
                    if (isset($_SESSION['page']) && $_SESSION['page'] !== "") {
                        $u = $_SESSION['page'];
                        $sql = "SELECT * FROM transaction WHERE page='$u'";
                        $sumSql = "SELECT SUM(recharge) AS total_recharge, SUM(redeem) AS total_redeem, SUM(excess) AS total_excess, SUM(bonus) AS total_bonus, SUM(freepik) AS total_freepik FROM transaction WHERE page='$u'";
                        $_SESSION['page'] = '';
                        // unset($_SESSION['page']);
                    } elseif (isset($_SESSION['branch']) && $_SESSION['branch'] !== "") {
                        $u = $_SESSION['branch'];
                        $sql = "SELECT * FROM transaction WHERE branch='$u'";
                        $sumSql = "SELECT SUM(recharge) AS total_recharge, SUM(redeem) AS total_redeem, SUM(excess) AS total_excess, SUM(bonus) AS total_bonus, SUM(freepik) AS total_freepik FROM transaction WHERE branch='$u'";

                        unset($_SESSION['branch']);
                    } else {
                        unset($_SESSION['u'], $_SESSION['r'], $_SESSION['page'], $_SESSION['branch']);
                        $sql = "SELECT * FROM transaction WHERE 1=1";
                        $sumSql = "SELECT SUM(recharge) AS total_recharge, SUM(redeem) AS total_redeem, SUM(excess) AS total_excess, SUM(bonus) AS total_bonus, SUM(freepik) AS total_freepik FROM transaction WHERE 1=1";
                    }
                    $sql .= " AND redeem_status = 1 AND cashout_status = 1";
                    $sumSql .= " AND redeem_status = 1 AND cashout_status = 1";
                    if ($role == 'Manager' || $role == 'Supervisor') {
                        $sql .= " AND branch='$bra' ";
                        $sumSql .= " AND branch='$bra'";
                    } elseif ($role == 'Agent') {
                        $page = $_SESSION['page1'];
                        $pagesArray = explode(", ", $page);
                        $quotedPages = [];
                        foreach ($pagesArray as $pageName) {
                            $quotedPages[] = "'" . mysqli_real_escape_string($conn, $pageName) . "'";
                        }
                        $whereClause = "page IN (" . implode(", ", $quotedPages) . ")";

                        $sql .= " AND $whereClause ";
                        $sumSql .= " AND $whereClause";
                    }
                    if ($start_date && $end_date) {
                        $sql .= " AND created_at BETWEEN '$start_date' AND '$end_date'";
                        $sumSql .= " AND created_at BETWEEN '$start_date' AND '$end_date'";
                    }


                    // if (isset($_SESSION['start_date']) && isset($_SESSION['end_date']) && $_SESSION['start_date'] !== '' && $_SESSION['end_date'] !== '') {
                    //     // Both start and end dates are provided
                    //     // $start_date = $_SESSION['start_date'];
                    //     // $end_date = $_SESSION['end_date'];
                    //     // $sql .= " AND created_at BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'";
                    //     // $sumSql .= " AND created_at BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'";
                    // } elseif (isset($_SESSION['start_date']) && !isset($_SESSION['end_date']) && $_SESSION['start_date'] !== '') {
                    //     // Only start date is provided
                    //     $start_date = $_SESSION['start_date'];
                    //     $sql .= " AND created_at >= '$start_date 00:00:00'";
                    //     $sumSql .= " AND created_at >= '$start_date 00:00:00'";
                    // } elseif (isset($_SESSION['start_date']) && isset($_SESSION['end_date']) && $_SESSION['end_date'] !== '') {
                    //     // Only end date is provided
                    //     $end_date = $_SESSION['end_date'];
                    //     $sql .= " AND created_at <= '$end_date 23:59:59'";
                    //     $sumSql .= " AND created_at <= '$end_date 23:59:59'";
                    // } elseif (isset($_SESSION['start_date']) && isset($_SESSION['end_date']) && $_SESSION['start_date'] !== '' && $_SESSION['end_date'] === '') {
                    //     // Only start date is provided and end date is empty
                    //     $start_date = $_SESSION['start_date'];
                    //     $sql .= " AND created_at >= '$start_date 00:00:00'";
                    //     $sumSql .= " AND created_at >= '$start_date 00:00:00'";
                    // }
                    if (isset($_SESSION['time_filter']) && $_SESSION != "") {
                        $timeFilter = $_SESSION['time_filter'];
                        if ($timeFilter === '1') {
                            $sql .= " AND created_at >= NOW() - INTERVAL 1 HOUR";
                            $sumSql .= " AND created_at >= NOW() - INTERVAL 1 HOUR";
                        } elseif ($timeFilter === '2') {
                            $sql .= " AND created_at >= NOW() - INTERVAL 2 HOUR";
                            $sumSql .= " AND created_at >= NOW() - INTERVAL 2 HOUR";
                        } elseif ($timeFilter === '24') {
                            $sql .= " AND created_at >= NOW() - INTERVAL 24 HOUR";
                            $sumSql .= " AND created_at >= NOW() - INTERVAL 24 HOUR";
                        } elseif ($timeFilter === 'custom') {
                            // Handle custom time filter here
                            // Example: $customStartTime = $_GET['custom_start_time']; $customEndTime = $_GET['custom_end_time'];
                        }
                    }
                    // echo $sql;
                    $stmt = $conn->prepare($sql);
                    // $stmt->bind_param('s', $u);
                    $stmt->execute();

                    $result = $stmt->get_result();
                    $results = $result->fetch_all(MYSQLI_ASSOC);
                    $stmt = $conn->prepare($sumSql);
                    $stmt->execute();
                    $sumResult = $stmt->get_result();
                    $sums = $sumResult->fetch_assoc();

                    $stmt->close();
                    $conn->close();

                    if (empty($results)) {
                        echo "No records found";
                    } else {
                        usort($results, function ($a, $b) {
                            return strtotime($b['created_at']) - strtotime($a['created_at']);
                        });
                    ?>

                        <div class="table-responsive">

                            <table id="example" class="table table-bordered table-hover display nowrap margin-top-10 w-p100">
                                <thead>
                                    <tr>
                                        <th>Transaction Type</th>
                                        <th>Recharge</th>
                                        <th>Redeem</th>
                                        <th>Excess Amount</th>
                                        <th>Bonus Amount</th>
                                        <th>Free Play</th>
                                        <th>Platform Name</th>
                                        <th>Page Name</th>
                                        <th>CashApp Name</th>
                                        <th>Timestamp</th>
                                        <th>Username</th>
                                        <th>By</th>
                                        <th>Remark</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $row) :
                                        $createdAt = new DateTime($row['created_at'], new DateTimeZone('UTC'));
                                        $createdAt->setTimezone(new DateTimeZone($selectedTimezone));
                                        $createdAtFormatted = $createdAt->format('Y-m-d H:i:s');

                                    ?>

                                        <tr>
                                            <td class="<?= ($row['type'] === 'Debit') ? 'Debit' : 'Credit' ?>">
                                                <?= $row['type'] ?>
                                            </td>
                                            <td><?= $row['recharge'] ?></td>
                                            <td><?= $row['redeem'] ?></td>
                                            <td><?= $row['excess'] ?></td>
                                            <td><?= $row['bonus'] ?></td>
                                            <td><?= $row['freepik'] ?></td>

                                            <td><?= $row['platform'] ?></td>
                                            <td><?= $row['page'] ?></td>
                                            <td><?= $row['cashapp'] ?></td>

                                            <td><?= $createdAtFormatted ?></td>
                                            <td><?= $row['username'] ?></td>
                                            <td><?= $row['by_u'] ?></td>
                                            <td><?= $row['remark'] ?></td>

                                        </tr>
                                    <?php endforeach; ?>
                                    <?php
                                    if ($sums) {
                                        echo "<tfoot>";
                                        echo "<tr>";
                                        echo "<th colspan=''>Total:</th>";
                                        echo "<th>{$sums['total_recharge']}</th>";
                                        echo "<th>{$sums['total_redeem']}</th>";
                                        echo "<th>{$sums['total_excess']}</th>";
                                        echo "<th>{$sums['total_bonus']}</th>";
                                        echo "<th>{$sums['total_freepik']}</th>";
                                        echo "<th colspan='4'></th>"; // Adjust colspan based on number of remaining columns
                                        echo "</tr>";
                                        echo "</tfoot>";
                                    }
                                    ?>

                                </tbody>
                            </table>

                        <?php
                    }

                        ?>
                        </div>

                </div>
            </div>
        </div>

        <!-- echo -->

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

    <!-- Wrapper End-->
    <!-- Live Customizer start -->
    <!-- Setting offcanvas start here -->
    <?php
    include("./Public/Pages/Common/theme_custom.php");

    ?>

    <!-- Settings sidebar end here -->

    <?php
    include("./Public/Pages/Common/settings_link.php");

    ?>
    <!-- Live Customizer end -->

    <!-- Library Bundle Script -->
    <?php
    include("./Public/Pages/Common/scripts.php");

    ?>

</body>

</html>