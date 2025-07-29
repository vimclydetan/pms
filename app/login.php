<?php
session_start();
include 'admin/include/connect.php';

// Nireretrieve yung email and password na galing sa login form
$email = $_POST['email'];
$password = $_POST['password'];

// Prepared SQL statement para ma-prevent sa SQL injection
$stmt = $conn->prepare("SELECT id, name, email, password FROM patient_account WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) { // Ichecheck kung may user na ganito doon sa database
    $user = $result->fetch_assoc(); // Saka naman dito kukunin yung details ng user

    // Verification ng nakahashed na password
    if (password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['name'] = $user['name']; 
        header('Location: dashboard.php'); // Kapag tama ang password, dito magreredirect ang user
        exit;
    } else {
        $_SESSION['error'] = 'Invalid email or password.';
        header('Location: ../index.php'); // Kapag mali ang password, babalik lang sa login page
        exit;
    }
} else {
    $_SESSION['error'] = 'Invalid email or password.';
    header('Location: ../index.php');
    exit;
}
?>
