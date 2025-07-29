<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PMS | Patient</title>
    <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .table thead tr td {
            border-top: none;
            border-bottom: 2px solid #dee2e6;
            background-color: var(--light-lavender);
            font-weight: 500;
            vertical-align: middle;
            padding: 1rem;
        }
    </style>
</head>
<body>
    <?php include 'include/header.php'; ?>
    <div class="main-content">
        <div class="wrap-content container" id="container">
            <section id="page-title">
                <div class="row">
                    <div>
                        <h1 class="main-title">My Medical Record</h1>
                    </div>
                </div>
            </section>

            <div class="container-fluid container-fullw">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-striped">
                            <thead>
                                <tr align="center">
                                    <td colspan="4" style="font-size:20px;">Patient Details</td>
                                </tr>
                            </thead>

                            <tr>
                                <th scope>Patient Name</th>
                                <td></td>
                                <th scope>Address</th>
                                <td></td>
                            </tr>

                            <tr>
                                <th scope>Contact No.</th>
                                <td></td>
                                <th scope>Email</th>
                                <td></td>
                            </tr>

                            <tr>
                                <th scope>Age</th>
                                <td></td>
                            </tr>
                        </table>

                        <table class="table table-striped">
                            <thead>
                                <tr align="center">
                                    <td colspan="4" style="font-size:20px;">General Consultation</td>
                                </tr>
                            </thead>

                            <tr>
                                <th scope>Date</th>
                                <td></td>
                                <th scope>Reason for Visit</th>
                                <td></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>