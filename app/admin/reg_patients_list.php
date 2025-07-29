<?php
session_start();
include 'include/connect.php';

$lmt = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page > 1) ? ($page - 1) * $lmt : 0;

// Query
$query = "SELECT id, name, contact_no, email, creationdate FROM patient_account";

// Modify query to include LIMIT and OFFSET
$paged_query = $query . " LIMIT $start, $lmt";
$sql = mysqli_query($conn, $paged_query);

// Count total records
$total_result = mysqli_query($conn, $query);
$total_records = mysqli_num_rows($total_result);
$total_pages = ceil($total_records / $lmt);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PMS | Admin</title>
    <link rel="stylesheet" href="../vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php include('include/admin_header.php'); ?>
    <div class="main-content">
        <div class="wrap-content container" id="container">
            <section id="page-title">
                <div class="row">
                    <div>
                        <h1 class="main-title">Registered Patients Account</h1>
                    </div>
                </div>
            </section>

            <div class="row">
                <div class="col-md-12">
                    <h5 class="over-title">Manage</h5>

                    <form method="POST" action="patient_list.php" class="col-lg-6">
                        <div class="input-group mb-3">
                            <input type="text" name="search" class="form-control" placeholder="Search by Name"
                                value="<?php echo isset($_POST['search']) ? htmlspecialchars($_POST['search']) : ''; ?>">
                            <button class="btn btn-outline-primary" type="submit">Search</button>
                            <a href="patient_list.php" class="btn btn-danger">Reset</a>
                        </div>
                    </form>

                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th class="center">No.</th>
                                <th class="center">User Name</th>
                                <th class="center">Email</th>
                                <th class="center">Contact no.</th>
                                <th class="center">Creation Date</th>
                                <th class="center">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            $cnt = $start + 1;
                            while ($row = mysqli_fetch_array($sql)) {
                            ?>
                                <tr>
                                    <td class="center"><?php echo $cnt; ?>.</td>
                                    <td class="center"><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td class="center"><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td class="center"><?php echo htmlspecialchars($row['contact_no']); ?></td>
                                    <td class="center"><?php echo htmlspecialchars($row['creationdate']); ?></td>
                                    <td class="center">
                                        <div>
                                            <a class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                            $cnt++;
                            }   
                            ?>
                        </tbody>
                    </table>

                    <!--pagination-->
                    <nav>
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                                <a href="<?php if ($page > 1) echo "?page=" . ($page - 1);
                                            else echo "#"; ?>" class="page-link">Previous</a>
                            </li>

                            <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                                <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                                    <a href="?page=<?php echo $i; ?>" class="page-link"><?php echo $i; ?></a>
                                </li>
                            <?php } ?>

                            <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                                <a href="<?php if ($page < $total_pages) echo "?page=" . ($page + 1);
                                            else echo "#"; ?>" class="page-link">Next</a>
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