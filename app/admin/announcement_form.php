<?php
session_start();
include 'include/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = $_POST['message'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $end_time = $_POST['end_time'];

    //Combine date and time into datetime
    $end_datetime = "$end_date $end_time";

    //Image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['image']['type'], $allowed_types)) {
            $filename = uniqid('announcement_', true) . '-' . basename($_FILES['image']['name']);
            $upload_dir = __DIR__ . '/uploads/announcements/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename);
            $image = 'app/admin/uploads/announcements/' . $filename;
        }
    }

    $stmt = $conn->prepare("INSERT INTO announcements (message, start_date, end_date, end_time, image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $message, $start_date, $end_date, $end_time, $image);

    if ($stmt->execute()) {
        $_SESSION['msg'] = "Announcement saved successfully!";
        $_SESSION['msg_type'] = 'success';
    } else {
        $_SESSION['msg'] = "Error saving announcement: " . $stmt->error . "";
        $_SESSION['msg_type'] = 'danger';
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
    <?php include('include/admin_header.php'); ?>

    <div class="main-content">
        <div class="wrap-content container" id="container">
            <section id="page-title">
                <div class="row">
                    <div>
                        <h1 class="main-title">Add Announcement</h1>
                    </div>
                </div>

            </section>

            <div class="container-fluid container-fullw">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row margin-top-30">
                            <div class="col-lg-12 col-md-12">
                                <div class="card shadow-lg">
                                    <div class="card-body">
                                        <form method="POST" enctype="multipart/form-data">
                                            <?php if (isset($_SESSION['msg'])): ?>
                                                <div class="alert <?php echo ($_SESSION['msg_type'] === 'success') ? 'alert-success' : 'alert-danger'; ?> alert-dismissible fade show" role="alert">
                                                    <?php echo htmlspecialchars($_SESSION['msg']); ?>
                                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                                </div>
                                                <?php unset($_SESSION['msg'], $_SESSION['msg_type']); ?>
                                            <?php endif; ?>
                                            
                                            <div class="mb-3">
                                                <label>Announcement Message</label>
                                                <textarea name="message" class="form-control" required></textarea>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label>Start Date</label>
                                                    <input type="date" name="start_date" class="form-control" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>End Date & Time</label>
                                                    <div class="row g-2">
                                                        <div class="col-md-6">
                                                            <input type="date" name="end_date" class="form-control" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <input type="time" name="end_time" class="form-control" required>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label>Upload Image (Optional)</label>
                                                <input type="file" name="image" class="form-control" accept="image/*">
                                            </div>
                                            <button class="btn custom-btn mt-3">Submit</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>