<?php
session_start();
include '../admin/include/connect.php'; // Include your database connection

$lmt = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page > 1) ? ($page - 1) * $lmt : 0;

$query = "SELECT a.id AS appointment_id, pa.name AS full_name, a.message, a.preferred_date, a.preferred_time, a.status FROM appointments a LEFT JOIN patient_account pa ON a.patient_account_id = pa.id WHERE a.status = 'completed'";

$total_query = $query;
$total_result = mysqli_query($conn, $total_query);
$total_records = mysqli_num_rows($total_result);
$total_pages = ceil($total_records / $lmt);

$query .= " LIMIT $start, $lmt";
$sql = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PMS | Appointment History</title>
    <link rel="stylesheet" href="../vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php include('include/header.php'); ?>
    <div class="main-content">
        <div class="wrap-conten container" id="container">
            <section id="page-title">
                <div class="row">
                    <div>
                        <h1 class="main-title">Appointment History</h1>
                    </div>
                </div>
            </section>

            <div class="row">
                <div class="col-md-12">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th class="center">No.</th>
                                <th>Name</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $cnt = $start + 1;

                            if (mysqli_num_rows($sql) > 0) {
                                while ($row = mysqli_fetch_array($sql)) {
                            ?>
                                    <tr>
                                        <td class="center"><?php echo $cnt; ?></td>
                                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['message']); ?></td>
                                        <td><?php echo htmlspecialchars($row['preferred_date']); ?></td>
                                        <td><?php echo htmlspecialchars(date("g:i A", strtotime($row['preferred_time']))); ?></td>
                                        <td><?php echo ucfirst($row['status']); ?></td>
                                    </tr>
                            <?php
                                    $cnt++;
                                }
                            } else {
                                echo '<tr><td colspan="6" class="text-center">No completed appointments found.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>

                    <nav>
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                                <a class="page-link" href="<?php if ($page > 1) echo "?page=" . ($page - 1);
                                                            else echo "#"; ?>">Previous</a>
                            </li>

                            <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                                <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php } ?>

                            <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                                <a class="page-link" href="<?php if ($page < $total_pages) echo "?page=" . ($page + 1);
                                                            else echo "#"; ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>