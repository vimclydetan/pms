<?php
session_start();
include 'include/connect.php';
//Only authorized user o yung admin lang yung makakaaccess ng page na 'to, otherwise ireredirect ang user sa index url if nagtrue lahat ng logics dito.
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('location: index.php');
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
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #4a90e2;
            --primary-dark: #2c5cc8;
            --text-light: #f8f9fa;
            --text-dark: #2d3748;
            --bg-light: #f5f7fa;
            --border-color: rgba(255, 255, 255, 0.2);
            --hover-bg: rgba(255, 255, 255, 0.12);
        }

        body {
            background-color: var(--bg-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: margin-left 0.3s ease;
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow-x: hidden;
        }

        /* ===== NAVBAR ===== */
        .top-navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            height: 60px;
            display: flex;
            align-items: center;
            padding: 0 20px;
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            z-index: 1030;
            transition: left 0.3s ease;
        }

        .navbar-brand {
            font-weight: 600;
            color: var(--primary-dark);
            font-size: 1.3rem;
        }

        .navbar-brand i {
            margin-right: 8px;
            color: var(--primary-color);
        }

        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--primary-color);
            cursor: pointer;
            padding: 5px;
            margin-right: 15px;
        }

        .navbar-user {
            margin-left: auto;
            display: flex;
            align-items: center;
        }

        .navbar-user img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .user-name {
            font-weight: 500;
            color: var(--text-dark);
            font-size: 0.95rem;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            position: fixed;
            top: 60px;
            left: 0;
            width: var(--sidebar-width);
            height: calc(100vh - 60px);
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: var(--text-light);
            padding-top: 20px;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            z-index: 1035;
            overflow-y: auto;
        }

        .sidebar-logo {
            text-align: center;
            padding: 15px 20px;
            font-weight: 700;
            font-size: 1.4rem;
            color: white;
            letter-spacing: -0.5px;
            margin-bottom: 15px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            padding: 0;
            margin: 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 14px 20px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: var(--hover-bg);
            color: white;
            border-left-color: white;
        }

        .sidebar-menu i {
            margin-right: 12px;
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
        }

        /* Submenu */
        .submenu {
            margin-left: 24px;
            padding-left: 6px;
            border-left: 1px solid rgba(255, 255, 255, 0.1);
        }

        .submenu a {
            font-size: 0.9rem;
            padding: 10px 15px;
        }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
            min-height: 100vh;
            padding-top: 60px;
        }

        /* Responsive: Sidebar Collapse */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .main-content, .top-navbar {
                margin-left: 0;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }
        }

        .main-title {
            font-size: 2rem;
            font-weight: 600;
            color: #2d3748;
            text-align: center;
            margin-bottom: 2rem;
            letter-spacing: -0.5px;
        }

        .card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            border-radius: 16px;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.15);
            background: rgba(255, 255, 255, 0.95);
        }

        .card-header {
            background: linear-gradient(135deg, #4a90e2, #2c5cc8);
            color: white;
            padding: 16px;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-container {
            position: absolute;
            left: 16px;
            font-size: 1.8rem;
            color: white;
            opacity: 0.9;
        }

        .card-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: white;
            text-align: center;
            flex-grow: 1;
        }

        .card-body {
            padding: 20px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 120px;
        }

        .count {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d3748;
            line-height: 1;
            margin-bottom: 8px;
        }

        .subtitle {
            font-size: 0.95rem;
            color: #718096;
            font-weight: 500;
        }

        /* Optional: subtle gradient underline for dashboard title */
        #page-title h1 {
            position: relative;
            display: inline-block;
        }

        #page-title h1::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 60%;
            height: 3px;
            background: linear-gradient(90deg, #4a90e2, #79b5e9);
            border-radius: 2px;
        }

        .card-row {
            gap: 1.5rem;
        }

        .col-sm-4 {
            padding: 0 10px;
        }

        a {
            text-decoration: none;
            color: inherit;
        }
    </style>
</head>
<body>
    <?php include('include/admin_header.php'); ?>
    
    <div class="main-content">
        <div class="wrap-content container" id="container">
            <section id="page-title">
                <div class="row">
                    <div class="col-12">
                        <h1 class="main-title">Admin Dashboard</h1>
                    </div>
                </div>
            </section>

            <div class="container-fluid container-fullw">
                <div class="row card-row d-flex">

                    <!-- Manage Doctors -->
                    <div class="col-sm-4 mb-4">
                        <a href="doctor_list.php">
                            <div class="card">
                                <div class="card-header">
                                    <span class="icon-container">
                                        <i class="fas fa-user-md"></i>
                                    </span>
                                    <h3 class="card-title">Manage Doctors</h3>
                                </div>
                                <div class="card-body">
                                    <h2 class="count">
                                        <?php 
                                            $result = mysqli_query($conn, "SELECT 
                                                (SELECT COUNT(*) FROM midwife) + (SELECT COUNT(*) FROM obgyn) AS total_doctors");
                                            $data = mysqli_fetch_assoc($result);
                                            $total_doctors = $data['total_doctors'];
                                            echo htmlentities($total_doctors);
                                        ?>
                                    </h2>
                                    <p class="subtitle">Total Doctors</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Secretary -->
                    <div class="col-sm-4 mb-4">
                        <a href="sec_list.php">
                            <div class="card">
                                <div class="card-header">
                                    <span class="icon-container">
                                        <i class="fas fa-user-tie"></i>
                                    </span>
                                    <h3 class="card-title">Secretary</h3>
                                </div>
                                <div class="card-body">
                                    <h2 class="count">
                                        <?php 
                                            $result = mysqli_query($conn, "SELECT COUNT(*) AS total_secretary FROM secretary");
                                            $data = mysqli_fetch_assoc($result);
                                            $total_sec = $data['total_secretary']; 
                                            echo htmlentities($total_sec);
                                        ?>
                                    </h2>
                                    <p class="subtitle">Total Secretaries</p>
                                        <!-- Fixed: subtitle now matches content -->
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Registered Patients -->
                    <div class="col-sm-4 mb-4">
                        <a href="reg_patients_list.php">
                            <div class="card">
                                <div class="card-header">
                                    <span class="icon-container">
                                        <i class="fas fa-users"></i>
                                    </span>
                                    <h3 class="card-title">Registered Patients</h3>
                                </div>
                                <div class="card-body">
                                    <h2 class="count">1</h2>
                                    <p class="subtitle">Total Patients Registered</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>