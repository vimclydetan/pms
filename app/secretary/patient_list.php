<?php
session_start();
include '../admin/include/connect.php';

$lmt = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page > 1) ? ($page - 1) * $lmt : 0;

// Base query without LIMIT for counting total records
$total_query = "SELECT id, pat_id, patient_name, contact_no, creationdate FROM patient_record WHERE (obgyn_id IS NOT NULL OR midwife_id IS NOT NULL) ORDER BY id DESC";

// Handle search
if (isset($_POST['search']) && !empty(trim($_POST['search']))) {
    $search_term = trim($_POST['search']);
    $total_query .= " AND (patient_name LIKE '%$search_term%')";
}

// Modify query to include LIMIT and OFFSET
$paged_query = $total_query . " LIMIT $start, $lmt";
$sql = mysqli_query($conn, $paged_query);
// Count total records

$total_result = mysqli_query($conn, $total_query);
$total_records = mysqli_num_rows($total_result);
$total_pages = ceil($total_records / $lmt);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PMS | Secretary</title>
    <link rel="stylesheet" href="../vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php include('include/header.php') ?>
    <div class="main-content">
        <div class="wrap-content container" id="container">
            <section id="page-title">
                <div class="row">
                    <div>
                        <h1 class="main-title">List of Patients</h1>
                    </div>
                </div>
            </section>

            <div class="row">
                <div class="col-md-12">
                    <h5 class="over-title">Manage</h5>
                    <?php if (isset($_SESSION['msg'])): ?>
                        <div class="alert <?php
                                            echo ($_SESSION['msg_type'] === 'success') ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($_SESSION['msg']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php
                        unset($_SESSION['msg'], $_SESSION['msg_type']);
                        ?>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <form method="POST" action="patient_list.php" class="col-lg-6">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Search by Name"
                                    value="<?php echo isset($_POST['search']) ? htmlspecialchars($_POST['search']) : ''; ?>">
                                <button class="btn btn-outline-primary" type="submit">Search</button>
                                <a href="patient_list.php" class="btn btn-danger">Reset</a>
                            </div>
                        </form>

                        <a href="add_patient.php" class="btn ms-3" style="background-color: #e2b6d0; color: white;">Add New Patient</a>
                    </div>

                    <table class="table table-striped" id="sample-table-1">
                        <thead>
                            <tr>
                                <th class="center">No.</th>
                                <th class="center">Patient ID</th>
                                <th class="center">Patient Name</th>
                                <th class="center">Contact no.</th>
                                <th class="center">Creation Date</th>
                                <th class="center">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            $cnt = $start + 1; // Adjusted counter based on current page

                            while ($row = mysqli_fetch_array($sql)) {
                            ?>
                                <tr>
                                    <td class="center"><?php echo $cnt; ?>.</td>
                                    <td class="center"><?php echo htmlspecialchars($row['pat_id']); ?></td>
                                    <td class="center"><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                    <td class="center"><?php echo htmlspecialchars($row['contact_no']); ?></td>
                                    <td class="center"><?php echo htmlspecialchars($row['creationdate']); ?></td>
                                    <td class="center">
                                        <div>
                                            <a href="edit_patient.php?editid=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">Edit</a>

                                            <a href="view_patient.php?viewid=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                                $cnt++;
                            } ?>
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