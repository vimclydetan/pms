<?php
session_start();
include 'include/connect.php';

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $checkQuery = "SELECT id FROM secretary WHERE id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $delete_query = "DELETE FROM secretary WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("i", $id);

        if ($delete_stmt->execute()) {
            $_SESSION['msg'] = "Account deleted successfully!";
            $_SESSION['msg_type'] = 'success';
        } else {
            $_SESSION['msg'] = "Error deleting account.";
            $_SESSION['msg_type'] = 'danger';
        }

        header("Location: sec_list.php");
        exit();
    }

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
                    <h1 class="main-title">List of Secretary</h1>
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
                            <th>Role</th>
                            <th>Name</th>
                            <th>Creation Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                            $cnt = 1;
                            $query = "SELECT id, name AS sec_name, creationDate AS creationdate FROM secretary";

                            $sql = mysqli_query($conn, $query);

                            while ($row = mysqli_fetch_array($sql)) {
                        ?>
                        <tr>
                            <td class="center"><?php echo $cnt; ?>.</td>
                            <td>Secretary</td>
                            <td><?php echo $row['sec_name']; ?></td>
                            <td><?php echo $row['creationdate']; ?></td>
                            <td>
                                <div>
                                    <a  href="edit_sec.php?editid=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">Edit</a>

                                    <a href="?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this account?')">Delete</a>

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