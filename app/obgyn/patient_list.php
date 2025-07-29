<?php
session_start();
include '../admin/include/connect.php';

$lmt = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start = ($page > 1) ? ($page - 1) * $lmt : 0;

$query = "SELECT id, pat_id, patient_name, contact_no, creationdate, updationdate FROM patient_record WHERE obgyn_id IS NOT NULL ORDER BY id DESC";

// Handle search
if (isset($_POST['search']) && !empty(trim($_POST['search']))) {
    $search_term = trim($_POST['search']);
    $query .= " AND (patient_name LIKE '%$search_term%')";
}

$total_query = $query;
$total_result = mysqli_query($conn, $total_query);
$total_records = mysqli_num_rows($total_result);
$total_pages = ceil($total_records / $lmt);

// Modify query to include LIMIT and OFFSET
$query .= " LIMIT $start, $lmt";
$sql = mysqli_query($conn, $query);

//Handle Delete Request
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']); //Ensure the ID is an integer

    //Check if the patient exists
    $checkQuery = "SELECT id FROM patient_record WHERE id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    //The patient will be deleted in the 3 tables due to foreign key constraints
    if ($result->num_rows > 0) {
        $delete_consultation_query = "DELETE FROM gen_consultation WHERE patient_record_id = ?";
        $delete_consultation_stmt = $conn->prepare($delete_consultation_query);
        $delete_consultation_stmt->bind_param("i", $id);
        $delete_consultation_stmt->execute();

        $delete_patient_query = "DELETE FROM patient_record WHERE id = ?";
        $delete_patient_stmt = $conn->prepare($delete_patient_query);
        $delete_patient_stmt->bind_param("i", $id);

        $delete_service_query = "DELETE FROM patient_service_records WHERE patient_record_id = ?";
        $delete_service_stmt = $conn->prepare($delete_service_query);
        $delete_service_stmt->bind_param("i", $id);
        $delete_service_stmt->execute();

        if ($delete_patient_stmt->execute()) {
            $_SESSION['msg'] = "Patient deleted successfully!";
            $_SESSION['msg_type'] = 'success';
        } else {
            $_SESSION['msg'] = "Error deleting patient.";
            $_SESSION['msg_type'] = 'danger';
        }

        header("Location: patient_list.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PMS | OBGYN</title>
    <link rel="stylesheet" href="../vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../vendor/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php include 'include/header.php' ?>
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
                        <div class="alert <?php echo ($_SESSION['msg_type'] === 'success') ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($_SESSION['msg']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['msg'], $_SESSION['msg_type']); ?>
                    <?php endif; ?>

                    <form method="POST" action="patient_list.php" class="col-lg-6">
                        <div class="input-group mb-3">
                            <input type="text" name="search" class="form-control" placeholder="Search by Name or Contact Number"
                                value="<?php echo isset($_POST['search']) ? htmlspecialchars($_POST['search']) : ''; ?>">
                            <button class="btn btn-outline-primary" type="submit">Search</button>
                            <a href="patient_list.php" class="btn btn-danger">Reset</a>
                        </div>
                    </form>

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
                            $cnt = $start + 1;

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
                                            <a href="view_patient.php?viewid=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">View</a>

                                            <a href="?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this patient?')">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                                $cnt++;
                            } ?>
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