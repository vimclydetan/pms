<?php
session_start();
include 'include/connect.php';
// DELETE LOGIC
if (isset($_GET['id']) && isset($_GET['table'])) {
    $id = intval($_GET['id']); 
    $table = $_GET['table'];

    // Validate allowed table names
    if (in_array($table, ['midwife', 'obgyn'])) {
        $stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $_SESSION['msg'] = "Doctor deleted successfully!";
        } else {
            $_SESSION['msg'] = "Error deleting doctor: " . $conn->error;
        }
        $stmt->close();
    } else {
        $_SESSION['msg'] = "Invalid specialization!";
    }

    header("Location: doctor_list.php");
    exit();
}
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
    <?php include('include/admin_header.php');?>

    <div class="main-content">
        <div class="wrap-content container" id="container">
            <!-- Page Title -->
            <section id="page-title">
                <div class="row">
                    <div>
                        <h1 class="main-title">List of Doctors</h1>
                    </div>
                </div>
            </section>

            <div class="row">
                <div class="col-md-12">
                    <h5 class="over-title">Manage</h5>
                    <p class="text-danger"><?= isset($_SESSION['msg']) ? htmlspecialchars($_SESSION['msg']) : ''; ?>
                    <?php unset($_SESSION['msg']); ?>
                    </p>	

                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th class="center">No.</th>
                                <th>Specialization</th>
                                <th>Doctor Name</th>
                                <th>Creation Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                                $cnt = 1;
                                $query = "SELECT 'Midwife' AS specialization, id, name AS doctorname, creationDate AS creationdate FROM midwife UNION ALL
                                            SELECT 'OB-GYNE' AS specialization, id, name AS doctorname, creationDate AS creationdate FROM obgyn";

                                $sql = mysqli_query($conn, $query);

                                while ($row = mysqli_fetch_array($sql)) {
                            ?>
                            <tr>
                                <td class="center"><?php echo $cnt; ?>.</td>
                                <td><?php echo $row['specialization']; ?></td>
                                <td><?php echo $row['doctorname']; ?></td>
                                <td><?php echo $row['creationdate']; ?></td>
                                <td>
                                    <div>
                                        <a class="btn btn-primary btn-sm" href="edit_doctor.php?editid=<?php echo $row['id']; ?>">Edit</a>

                                        <a class="btn btn-danger btn-sm" href="doctor_list.php?id=<?= $row['id']; ?>&table=<?= ($row['specialization'] == 'Midwife' ? 'midwife' : 'obgyn'); ?>" onclick="return confirm('Are you sure you want to delete this doctor?')">Delete</a>

                                    </div>
                                </td>

                            </tr>
                            <?php 
                                $cnt = $cnt + 1;
                            }?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>