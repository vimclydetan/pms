<?php
session_start();
include 'include/connect.php';
if (!isset($conn)) {
    die("Database connection not found.");
}


if (isset($_POST['submit'])) {
    //Retrieves the username and password
    $username = $_POST['username'];
    $password = $_POST['password'];
    $user = null;
    $role = null;

    //Ichecheck yung user credentials sa tatlong tables. Kung mahanap yung matched sa table yun yung magiging role nya. For example, nagmatch yung credentials nya sa admin table, yung user role ay maseset into admin, same with the others.
    $stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $role = "admin";
    } else {
        $stmt = $conn->prepare("SELECT id, email, password FROM obgyn WHERE email = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            $role = "obgyn";
        } else {
            $stmt = $conn->prepare("SELECT id, email, password FROM midwife WHERE email = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                $role = "midwife";
            } else {
                $stmt = $conn->prepare("SELECT id, email, password FROM secretary WHERE email = ?");
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows == 1) {
                    $user = $result->fetch_assoc();
                    $role = "secretary";
                }
            }
        }
    }

    //Vineverify naman dito kung entered password ay match dun sa nakahashed na password sa database
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $role;

        //Logs user to the userlogs table
        $user_id = $user['id'];
        $insert_sql = "";

        switch ($role) {
            case 'obgyn':
                $insert_sql = "INSERT INTO user_logs (obgyn_id, role, username) VALUES (?, ?, ?)";
                $db_role = 'OBGYNE';
                break;
            case 'midwife':
                $insert_sql = "INSERT INTO user_logs (midwife_id, role, username) VALUES (?, ?, ?)";
                $db_role = 'Midwife';
                break;
            case 'secretary':
                $insert_sql = "INSERT INTO user_logs (secretary_id, role, username) VALUES (?, ?, ?)";
                $db_role = 'Secretary';
                break;
        }

        if (!empty($insert_sql)) {
            $stmt_log = $conn->prepare($insert_sql);
            if ($stmt_log) {
                $stmt_log->bind_param("iss", $user_id, $db_role, $username);
                $stmt_log->execute();
                //Get the last inserted ID
                $log_id = $stmt_log->insert_id;
                //Save it in session
                $_SESSION['log_id'] = $log_id;
                $stmt_log->close();
            }
        }

        //Magreredirect sa kani-kaniyang dedicated dashboards based sa roles nila.
        switch ($role) {
            case 'admin':
                header("location:admin_dashboard.php");
                break;
            case 'obgyn':
                header("location: ../obgyn/obgyn_dashboard.php");
                break;
            case 'midwife':
                header("location: ../midwife/midwife_dashboard.php");
                break;
            case 'secretary':
                header("location: ../secretary/sec_dashboard.php");
                break;
        }

        exit();
    } else {
        $_SESSION['errmsg'] = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Login | Patient Management System</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #003366, #005588);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
        }

        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .container {
            display: flex;
            width: 90%;
            max-width: 880px;
            height: 560px;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .side-image {
            flex: 1;
            background: url('https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80') center/cover no-repeat;
            position: relative;
            display: flex;
            align-items: flex-end;
            justify-content: flex-start;
        }

        .side-content {
            padding: 30px;
            color: white;
            background: rgba(0, 30, 60, 0.7);
            border-radius: 0 0 20px 0;
            max-width: 80%;
        }

        .side-content h3 {
            font-size: 24px;
            font-weight: 700;
        }

        .side-content p {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 6px;
        }

        .login-form {
            flex: 1;
            padding: 50px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 0 20px 20px 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid #0077cc;
            object-fit: cover;
        }

        .logo h2 {
            font-family: 'Poppins', sans-serif;
            font-size: 22px;
            font-weight: 700;
            color: #003b73;
            margin-top: 12px;
        }

        .tagline {
            text-align: center;
            color: #0056b3;
            font-weight: 500;
            font-size: 15px;
            margin-bottom: 25px;
        }

        .input-group {
            position: relative;
            margin-bottom: 22px;
        }

        .input-group input {
            width: 100%;
            padding: 14px 16px 14px 45px; /* space for icon on left */
            border: 1px solid #ccc;
            border-radius: 12px;
            font-size: 15px;
            transition: border 0.3s;
        }

        .input-group input:focus {
            border-color: #0077cc;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 119, 204, 0.1);
        }

        .input-group i.left {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #555;
            font-size: 16px;
        }

        /* Right icon (toggle) - hidden by default */
        .input-group i.right {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #777;
            cursor: pointer;
            font-size: 16px;
            display: none; /* Hidden by default */
        }

        .btn-login {
            background: #003b73;
            color: white;
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: #002a52;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }

        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                height: auto;
                border-radius: 16px;
                margin: 15px;
            }
            .side-image {
                display: none;
            }
            .login-form {
                padding: 30px;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Side Image -->
        <div class="side-image">
            <div class="side-content">
                <h3>Secure Access Portal</h3>
                <p>For authorized medical staff and administrators only.</p>
            </div>
        </div>

        <!-- Login Form -->
        <div class="login-form">
            <div class="logo">
                <img src="../../assets/images/weblogo.png" alt="Logo">
                <h2>Patient Management System</h2>
            </div>

            <p class="tagline">Admin & Staff Login</p>

            <?php if (!empty($errmsg)): ?>
                <div class="alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= htmlspecialchars($errmsg) ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="loginForm">
                <!-- Username -->
                <div class="input-group">
                    <i class="fas fa-user left"></i>
                    <input
                        type="text"
                        name="username"
                        placeholder="Enter username or email"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        required
                    >
                </div>

                <!-- Password with Toggle -->
                <div class="input-group">
                    <i class="fas fa-lock left"></i>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        placeholder="Enter your password"
                        required
                    >
                    <i class="fas fa-eye right" id="togglePassword"></i>
                </div>

                <!-- Submit Button -->
                <button type="submit" name="submit" class="btn-login">
                    <span id="btnText"><i class="fas fa-sign-in-alt me-1"></i> Log In</span>
                    <span id="loading" style="display:none;"><i class="fas fa-spinner fa-spin"></i> Signing in...</span>
                </button>
            </form>

            <div class="footer">
                &copy; <?= date('Y') ?> Patient Management System. All rights reserved.
            </div>
        </div>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');

        // Show/hide toggle icon based on input
        passwordInput.addEventListener('input', function () {
            if (this.value.length > 0) {
                togglePassword.style.display = 'inline'; // Show eye
            } else {
                togglePassword.style.display = 'none';  // Hide eye
                togglePassword.classList.remove('fa-eye-slash');
                togglePassword.classList.add('fa-eye'); // Reset icon
            }
        });

        // Toggle password visibility
        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Optional: Hide on page load if password is empty
        if (passwordInput.value.length === 0) {
            togglePassword.style.display = 'none';
        }

        // Loading state on submit
        const form = document.getElementById('loginForm');
        const btnText = document.getElementById('btnText');
        const loading = document.getElementById('loading');

        form.addEventListener('submit', function () {
            btnText.style.display = 'none';
            loading.style.display = 'inline';
        });
    </script>

</body>
</html>