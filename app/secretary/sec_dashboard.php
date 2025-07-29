<?php
session_start();
include '../admin/include/connect.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id']) || $_SESSION['role'] !== 'secretary') {
    header('location: ./../admin/index.php');
    exit();
}

// Handle AJAX requests for chart data
if (isset($_GET['chart'])) {
    $chartType = $_GET['chart'];

    switch ($chartType) {
        case 'bar':
            // Count number of patients per service
            $query = "
        SELECT s.service_name, COUNT(DISTINCT psr.patient_record_id) AS count 
        FROM services s
        LEFT JOIN patient_service_records psr ON s.service_id = psr.service_id
        GROUP BY s.service_id
    ";
            $result = mysqli_query($conn, $query);
            $data = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = ['service_name' => $row['service_name'], 'count' => $row['count']];
            }
            echo json_encode($data);
            exit();

        case 'doughnut':
            // Get pregnancy results from Pregnancy Test
            $pregnantTestQuery = "
        SELECT COUNT(DISTINCT psr.patient_record_id) AS count 
        FROM patient_service_records psr
        JOIN fields f ON psr.field_id = f.field_id
        WHERE f.service_id = 5 AND f.field_name = 'Result' AND psr.field_value = 'Positive'
    ";
            $nonPregnantTestQuery = "
        SELECT COUNT(DISTINCT psr.patient_record_id) AS count 
        FROM patient_service_records psr
        JOIN fields f ON psr.field_id = f.field_id
        WHERE f.service_id = 5 AND f.field_name = 'Result' AND psr.field_value = 'Negative'
    ";

            $pregnantTest = mysqli_fetch_assoc(mysqli_query($conn, $pregnantTestQuery))['count'];
            $nonPregnantTest = mysqli_fetch_assoc(mysqli_query($conn, $nonPregnantTestQuery))['count'];

            // Transvaginal Ultrasound - Pregnant?
            $transvaginalPregnantQuery = "
        SELECT COUNT(DISTINCT psr.patient_record_id) AS count 
        FROM patient_service_records psr
        JOIN fields f ON psr.field_id = f.field_id
        WHERE f.service_id = 1 AND f.field_name = 'Pregnant?' AND psr.field_value = 'Yes'
    ";
            $transvaginalNonPregnantQuery = "
        SELECT COUNT(DISTINCT psr.patient_record_id) AS count 
        FROM patient_service_records psr
        JOIN fields f ON psr.field_id = f.field_id
        WHERE f.service_id = 1 AND f.field_name = 'Pregnant?' AND psr.field_value = 'No'
    ";

            $transvaginalPregnant = mysqli_fetch_assoc(mysqli_query($conn, $transvaginalPregnantQuery))['count'];
            $transvaginalNonPregnant = mysqli_fetch_assoc(mysqli_query($conn, $transvaginalNonPregnantQuery))['count'];

            // BPS with NST - Fetal heartbeat > 0
            $bpsPregnantQuery = "
        SELECT COUNT(DISTINCT psr.patient_record_id) AS count 
        FROM patient_service_records psr
        JOIN fields f ON psr.field_id = f.field_id
        WHERE f.service_id = 3 AND f.field_name = 'Estimated Due Date' AND CAST(psr.field_value AS UNSIGNED) > 0
    ";

            $bpsPregnant = mysqli_fetch_assoc(mysqli_query($conn, $bpsPregnantQuery))['count'];

            // Combine totals
            $totalPregnant = $pregnantTest + $transvaginalPregnant + $bpsPregnant;
            $totalNonPregnant = $nonPregnantTest + $transvaginalNonPregnant;

            echo json_encode([
                ['label' => 'Pregnant', 'count' => $totalPregnant],
                ['label' => 'Non-Pregnant', 'count' => $totalNonPregnant]
            ]);
            exit();

        case 'birth':
            // Births from OB & Midwife deliveries
            $birthQuery = "
        SELECT MONTH(psr.field_value) AS birth_month, COUNT(DISTINCT psr.patient_record_id) AS count
        FROM patient_service_records psr
        JOIN fields f ON psr.field_id = f.field_id
        WHERE f.service_id IN (14, 15) AND f.field_name = 'Delivery Date'
        GROUP BY birth_month
    ";
            $result = mysqli_query($conn, $birthQuery);
            $data = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $monthName = date("M", mktime(0, 0, 0, $row['birth_month'], 1));
                $data[] = ['month' => $monthName, 'count' => $row['count']];
            }
            echo json_encode($data);
            exit();

        case 'preg_outcomes':
            // Count pregnancy outcomes from OB (14) and Midwife (15) services
            $outcomeQuery = "
        SELECT psr.field_value AS outcome, COUNT(DISTINCT psr.patient_record_id) AS count
        FROM patient_service_records psr
        JOIN fields f ON psr.field_id = f.field_id
        WHERE f.service_id IN (14, 15)
          AND f.field_name = 'Pregnancy Outcome'
        GROUP BY psr.field_value
    ";
            $result = mysqli_query($conn, $outcomeQuery);
            $data = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = ['label' => $row['outcome'], 'count' => $row['count']];
            }
            echo json_encode($data);
            exit();
    }
}

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            background-color: #b6d0e2;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .icon-container {
            position: absolute;
            left: 15px;
            font-size: 2rem;
            color: #007bff;
        }

        .card-title {
            margin: 0;
            font-size: 1.3rem;
            font-weight: bold;
        }

        .card-body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 10px;
        }

        .count {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .subtitle {
            font-size: 0.8rem;
            color: #666;
        }

        #outcomeChartContainer {
            margin-top: -20rem;
        }
    </style>
</head>

<body>
    <?php include('include/header.php'); ?>
    <div class="main-content">
        <div class="wrap-content container" id="container">
            <section id="page-title">
                <div class="row">
                    <div>
                        <h1 class="main-title">Secretary Dashboard</h1>
                    </div>
                </div>
            </section>

            <div class="container-fluid container-fullw">
                <div class="row card-row">
                    <div class="col-sm-4">
                        <a href="patient_list.php" style="text-decoration: none; color: inherit;">
                            <div class="card">
                                <div class="card-header mb-3 align-items-center justify-content-between d-flex">
                                    <span class="icon-container">
                                        <i class="fas fa-users fa-1x fa-inverse"></i>
                                    </span>
                                    <h3 class="card-title text-center flex-grow-1">Patients</h3>
                                </div>
                                <div class="card-body">
                                    <h2 class="count">
                                        <?php
                                        $result = mysqli_query($conn, "SELECT COUNT(*) AS total_patients FROM patient_record");
                                        $data = mysqli_fetch_assoc($result);
                                        $total_patients = $data['total_patients'];
                                        ?>
                                        <?php echo htmlentities($total_patients); ?>
                                    </h2>
                                    <p class="cubtitle">Total Patients</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-sm-4">
                        <a href="pending_appointments.php" style="text-decoration: none; color: inherit;">
                            <div class="card">
                                <div class="card-header mb-3 align-items-center justify-content-between d-flex">
                                    <span class="icon-container">
                                        <i class="fas fa-calendar-check fa-1x fa-inverse"></i>
                                    </span>
                                    <h3 class="card-title text-center flex-grow-1">Appointments</h3>
                                </div>
                                <div class="card-body">
                                    <h2 class="count">
                                        <?php
                                        $result = mysqli_query($conn, "SELECT COUNT(*) AS total_pending FROM appointments WHERE status = 'pending'");
                                        $data = mysqli_fetch_assoc($result);
                                        $total_appointment = $data['total_pending'];
                                        ?>
                                        <?php echo htmlentities($total_appointment); ?>
                                    </h2>
                                    <p class="subtitle">New Appointments</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <div class="container-fluid container-fullw">
                <div class="text-center mb-3">
                    <select id="chartSelector" onchange="toggleChart()" class="form-select w-25 mx-auto">
                        <option value="bar">Patients per services</option>
                        <option value="doughnut">Pregnant and non-pregnant</option>
                        <option value="birth">Births</option>
                        <option value="preg_outcomes">Pregnancy Outcomes</option>
                    </select>
                </div>

                <div id="birthChartContainer" class="d-flex justify-content-center mt-3" style="display: none;">
                    <canvas id="birthChartCanvas" width="80" height="40"></canvas>
                </div>

                <div id="barChartContainer" class="d-flex justify-content-center">
                    <canvas id="barChartCanvas" width="100" height="50"></canvas>
                </div>

                <div id="doughnutChartContainer" class="d-flex justify-content-center" style="display: none;">
                    <canvas id="doughnutChartCanvas" width="300" height="300"></canvas>
                </div>

                <div id="outcomeChartContainer" class="d-flex justify-content-center" style="display: none;">
                    <canvas id="outcomeChartCanvas" width="300" height="300"></canvas>
                </div>

            </div>
        </div>
    </div>

    <div id="fixed-clock" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000; background: #b6d0e2; padding: 10px 15px; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); font-size: 14px; border: 1px solid #ddd;">
        Current Time: <span id="realTime"><?= date("F j, Y, g:i A") ?></span>
    </div>

    <script>
        let currentChart = null;

        function fetchDataAndRenderChart(url, renderFunction) {
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (currentChart) {
                        currentChart.destroy();
                    }
                    renderFunction(data);
                })
                .catch(error => console.error('Error fetching chart data:', error));
        }

        function renderBarChart(data) {
            const ctx = document.getElementById('barChartCanvas').getContext('2d');
            const labels = data.map(item => item.service_name);
            const counts = data.map(item => parseInt(item.count));

            currentChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Patients per Service',
                        data: counts,
                        backgroundColor: '#FFB6B9',
                        borderColor: '#FFB6B9',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function renderDoughnutChart(data) {
            const ctx = document.getElementById('doughnutChartCanvas').getContext('2d');
            const labels = data.map(item => item.label);
            const counts = data.map(item => parseInt(item.count));

            currentChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Pregnancy Status',
                        data: counts,
                        backgroundColor: ['#FF69B4', '#1E90FF'],
                        borderColor: ['#FF1493', '#104E8B'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.chart._metasets[context.datasetIndex].total;
                                    const value = context.parsed;
                                    const percent = ((value / total) * 100).toFixed(1);
                                    return `${context.label}: ${value} (${percent}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        function renderBirthChart(data) {
            const ctx = document.getElementById('birthChartCanvas').getContext('2d');
            const labels = data.map(item => item.month);
            const counts = data.map(item => parseInt(item.count));

            currentChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Births per Month',
                        data: counts,
                        backgroundColor: '#FFC4E1',
                        borderColor: '#FFC4E1',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function renderOutcomeChart(data) {
            const ctx = document.getElementById('outcomeChartCanvas').getContext('2d');
            const labels = data.map(item => item.label);
            const counts = data.map(item => parseInt(item.count));
            currentChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Pregnancy Outcomes',
                        data: counts,
                        backgroundColor: ['#98FB98', '#FFB6C1', '#FFA07A'],
                        borderColor: ['#006400', '#FF1493', '#FF4500'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.chart._metasets[context.datasetIndex].total;
                                    const value = context.parsed;
                                    const percent = ((value / total) * 100).toFixed(1);
                                    return `${context.label}: ${value} (${percent}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        function toggleChart() {
            const selected = document.getElementById('chartSelector').value;

            if (currentChart) {
                currentChart.destroy();
                currentChart = null;
            }

            document.getElementById('barChartContainer').style.display = 'none';
            document.getElementById('doughnutChartContainer').style.display = 'none';
            document.getElementById('birthChartContainer').style.display = 'none';
            document.getElementById('outcomeChartContainer').style.display = 'none';

            if (selected === 'bar') {
                document.getElementById('barChartContainer').style.display = 'flex';
                fetchDataAndRenderChart('?chart=bar', renderBarChart);
            } else if (selected === 'doughnut') {
                document.getElementById('doughnutChartContainer').style.display = 'flex';
                fetchDataAndRenderChart('?chart=doughnut', renderDoughnutChart);
            } else if (selected === 'birth') {
                document.getElementById('birthChartContainer').style.display = 'flex';
                fetchDataAndRenderChart('?chart=birth', renderBirthChart);
            } else if (selected === 'preg_outcomes') {
                document.getElementById('outcomeChartContainer').style.display = 'flex';
                fetchDataAndRenderChart('?chart=preg_outcomes', renderOutcomeChart);
            }
        }

        window.onload = function() {
            fetchDataAndRenderChart('?chart=bar', renderBarChart); // Show bar chart by default
        };

        function updateClock() {
            const now = new Date();
            const options = {
                month: 'long',
                day: 'numeric',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            };
            const formattedTime = now.toLocaleDateString('en-US', options);
            document.getElementById('realTime').textContent = formattedTime;
        }

        // Update every second
        setInterval(updateClock, 1000);
        updateClock(); // Initial call
    </script>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>