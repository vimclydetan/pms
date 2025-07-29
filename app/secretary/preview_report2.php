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
    <?php include('include/header.php'); ?>
    <div class="main-content">
        <div class="wrap-content container" id="container">
            <section id="page-title">
                <div class="row">
                    <div class="col-md-6">
                        <h1 class="main-title">Patients Report</h1>
                    </div>
                    <div class="col-md-6 text-md-right text-center">
                        <label for="monthYearFilter">Filter by Month and Year:</label>
                        <div class="input-group d-inline-flex">
                            <select id="monthFilter" class="form-control mr-2" style="width: auto;">
                                <option value="">All Months</option>
                                <?php
                                $months = array(
                                    '01' => 'January',
                                    '02' => 'February',
                                    '03' => 'March',
                                    '04' => 'April',
                                    '05' => 'May',
                                    '06' => 'June',
                                    '07' => 'July',
                                    '08' => 'August',
                                    '09' => 'September',
                                    '10' => 'October',
                                    '11' => 'November',
                                    '12' => 'December'
                                );
                                foreach ($months as $key => $value) {
                                    echo "<option value=\"$key\">$value</option>";
                                }
                                ?>
                            </select>
                            <select id="yearFilter" class="form-control">
                                <option value="">All Years</option>
                                <?php
                                $currentYear = date('Y');
                                for ($i = $currentYear; $i >= $currentYear - 7; $i--) {
                                    echo "<option value=\"$i\">$i</option>";
                                }
                                ?>
                            </select>
                            <button id="submitFilter" class="btn" style="background-color:  #b6d0e2;">
                                <i class="fas fa-filter"></i> Apply Filter
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <section id="pdf-preview" style="margin-top: 20px;">
                <div class="row">
                    <div class="col-md-12">
                        <iframe id="pdfFrame" src="report2.php" type="application/pdf" width="100%" height="600px" frameborder="0">
                            This browser does not support PDFs. <a href="report1.php">Download the PDF</a>.
                        </iframe>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12 text-center">
                        <a id="downloadLink" href="report1.php" download="delivered_and_expectant.pdf" class="btn" style="background-color:  #b6d0e2;">
                            <i class="fas fa-download"></i> Download PDF
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        function reloadPDF() {
            var month = document.getElementById('monthFilter').value;
            var year = document.getElementById('yearFilter').value;
            var pdfFrame = document.getElementById('pdfFrame');
            var downloadLink = document.getElementById('downloadLink');

            var params = '';
            if (month || year) {
                params = '?';
                if (month) params += 'month=' + month + '&';
                if (year) params += 'year=' + year;
                params = params.replace(/\?&$/, '?').replace(/&$/, '');
            }

            var pdfSrc = 'report2.php' + params;
            pdfFrame.src = pdfSrc;
            downloadLink.href = pdfSrc;
        }

        // Attach click event to the Submit button
        document.getElementById('submitFilter').addEventListener('click', reloadPDF);
    </script>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>