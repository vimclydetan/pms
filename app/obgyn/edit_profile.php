<?php
session_start();
include '../admin/include/connect.php';

$obgyn_id = $_SESSION['user_id'];

if (isset($_POST['submit_info'])) {
    //Fetch data from the form
    $docname = $_POST['docname'];
    $province = $_POST['province'];
    $city = $_POST['city'];
    $barangay = $_POST['barangay'];
    $contact = $_POST['doccontact'];
    $email = $_POST['docemail'];
    $updationDate = date("Y-m-d H:i:s");

    //Query for updating
    $stmt = $conn->prepare("UPDATE obgyn SET name=?, province=?, city=?, barangay=?, contactno=?, email=?, updationDate=? WHERE id=?");
    $stmt->bind_param("sssssi", $docname, $province, $city, $barangay, $contact, $email, $updationDate, $obgyn_id);

    if ($stmt->execute()) {
        $msg_info = "Information updated successfully!";
    } else {
        $msg_info = "Failed to update information.";
    }
    $stmt->close();
}

if (isset($_POST['submit_pass'])) {
    //Retrieves the current, new, and confirm password from the form
    $current_pass = $_POST['current_pass'];
    $new_pass = $_POST['new_pass'];
    $confirm_pass = $_POST['confirm_pass'];

    //This ensures the passwords fields match
    if ($new_pass !== $confirm_pass) {
        $msg_pass = "New password and confirm password do not match!";
    } else {
        $stmt = $conn->prepare("SELECT password FROM obgyn WHERE id = ?");
        $stmt->bind_param("i", $obgyn_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($current_pass, $user['password'])) {
            $hashed_new_pass = password_hash($new_pass, PASSWORD_BCRYPT);
            $updationDate = date("Y-m-d H:i:s");

            $update_stmt = $conn->prepare("UPDATE obgyn SET password=?, updationDate=? WHERE id=?");
            $update_stmt->bind_param("ssi", $hashed_new_pass, $updationDate, $obgyn_id);

            if ($update_stmt->execute()) {
                $msg_pass = "Password updated successfully!";
            } else {
                $msg_pass = "Failed to update password.";
            }
            $update_stmt->close();
        } else {
            $msg_pass = "Current password is incorrect!";
        }
        $stmt->close();
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
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php include('include/header.php'); ?>
    <div class="main-content">
        <div class="wrap-content container" id="container">
            <section id="page-title">
                <div class="row">
                    <div>
                        <h1 class="main-title">Edit OBGYN Informations</h1>
                    </div>
                </div>
            </section>

            <div class="container-fluid container-fullw">
                <div class="row">
                    <div class="col-md-6">
                        <div class="row margin-top-30">
                            <div class="col-lg-12 col-md-12">
                                <div class="card shadow-lg">
                                    <div class="card-body">
                                        <h5 class="card-title">Basic Information</h5>
                                        <form role="form" name="update_information" method="post">
                                            <?php if (isset($msg_info)) { ?>
                                                <div class="alert alert-danger"><?php echo $msg_info; ?></div>
                                            <?php } ?>

                                            <?php
                                            $ret = mysqli_query($conn, "SELECT name, province, city, barangay, contactno, email FROM obgyn WHERE id = '$obgyn_id'");
                                            $obgyn = mysqli_fetch_assoc($ret);
                                            ?>

                                            <div class="form-group mb-3">
                                                <label for="docname">Doctor name</label>
                                                <input type="text" name="docname" class="form-control" placeholder="Surname, First Name MI." value="<?php echo htmlentities($obgyn['name']); ?>">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Province*</label>
                                                <select id="province" class="form-control" disabled>
                                                    <option value="LGN">Laguna</option>
                                                </select>
                                            </div>

                                            <!-- City -->
                                            <div class="mb-3">
                                                <label class="form-label">City/Municipality*</label>
                                                <select id="city" class="form-control">
                                                    <option value="<?= htmlspecialchars($obgyn['city']) ?>" selected>
                                                        <?= htmlspecialchars($obgyn['city']) ?>
                                                    </option>
                                                </select>
                                            </div>

                                            <!-- Barangay -->
                                            <div class="mb-3">
                                                <label class="form-label">Barangay*</label>
                                                <select id="barangay" class="form-control">
                                                    <option value="<?= htmlspecialchars($obgyn['barangay']) ?>" selected>
                                                        <?= htmlspecialchars($obgyn['barangay']) ?>
                                                    </option>
                                                </select>
                                            </div>

                                            <!-- Hidden Fields -->
                                            <input type="hidden" name="province" id="province_name" value="<?= htmlspecialchars($obgyn['province']) ?>">
                                            <input type="hidden" name="city" id="city_name" value="<?= htmlspecialchars($obgyn['city']) ?>">
                                            <input type="hidden" name="barangay" id="barangay_name" value="<?= htmlspecialchars($obgyn['barangay']) ?>">

                                            <div class="form-group mb-3">
                                                <label for="contactno">Contact no.</label>
                                                <input type="text" name="doccontact" class="form-control" placeholder="Enter Doctor Contact No." value="<?php echo htmlentities($obgyn['contactno']); ?>">
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="email">Email</label>
                                                <input type="email" id="docemail" name="docemail" class="form-control" placeholder="Enter Doctor Email" value="<?php echo htmlentities($obgyn['email']); ?>">
                                            </div>


                                            <button type="submit" name="submit_info" id="submit" class="btn custom-btn mb-3">
                                                Update Information
                                            </button>
                                        </form>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row margin-top-30">
                            <div class="col-lg-12 col-md-12">
                                <div class="card shadow-lg">
                                    <div class="card-body">
                                        <h5 class="card-title">Change Password</h5>
                                        <form role="form" name="update_pass" method="post">
                                            <?php if (isset($msg_pass)) { ?>
                                                <div class="alert alert-danger"><?php echo $msg_pass; ?></div>
                                            <?php } ?>

                                            <div class="form-group mb-3">
                                                <label for="current_pass">Current Password</label>
                                                <input type="password" name="current_pass" class="form-control" required>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="new_pass">New Password</label>
                                                <input type="password" name="new_pass" class="form-control" required>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="confirm_pass">Confirm Password</label>
                                                <input type="password" name="confirm_pass" class="form-control" required>
                                            </div>


                                            <button type="submit" name="submit_pass" id="submit" class="btn custom-btn mb-3">
                                                Update Password
                                            </button>
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
    <script>
        // Prefill city and barangay after page load
        window.addEventListener('DOMContentLoaded', () => {
            const savedCityName = "<?= htmlspecialchars($patient['city']) ?>";
            const savedBrgyName = "<?= htmlspecialchars($patient['barangay']) ?>";

            document.getElementById('city_name').value = savedCityName;
            document.getElementById('barangay_name').value = savedBrgyName;

            // Optional: Trigger JS to reload cities/barangays dynamically
            // This depends on how your main.js loads options
        });

        // Update hidden address fields on submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const province = document.getElementById('province_name').options[document.getElementById('province').selectedIndex].text;
            const city = document.getElementById('city_name').options[document.getElementById('city').selectedIndex].text;
            const barangay = document.getElementById('barangay_name').options[document.getElementById('barangay').selectedIndex].text;

            document.getElementById('province_name').value = province;
            document.getElementById('city_name').value = city;
            document.getElementById('barangay_name').value = barangay;
        });
    </script>
</body>

</html>