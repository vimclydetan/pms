<?php
session_start();
include 'admin/include/connect.php';

// Kinukuha yung details na galing sa registration form
$name = $_POST['name'];
$province = $_POST['province_name'];
$city = $_POST['city_name'];
$brgy = $_POST['barangay_name'];
$contact = $_POST['contact'];
$birthdate = $_POST['birthdate'];
$email = $_POST['email'];
$password = $_POST['password'];
$confirmPassword = $_POST['confirmPassword'];

if ($password !== $confirmPassword) { // Checking ng password at ng confirm password na in-input
    $_SESSION['error'] = 'Passwords do not match!';
    header('Location: ../index.php');
    exit;
}

// Magchecheck muna kung may existed na email sa database
$check = $conn->prepare("SELECT id FROM patient_account WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) { // If greater than 0, ibig sabihin may existing na
    $_SESSION['error'] = 'Email is already registered!';
    header('Location: ../index.php');
    exit;
}

// Calculate age server-side
$today = new DateTime();
$birthDateObj = new DateTime($birthdate);
$age = $today->diff($birthDateObj)->y;

// Hashing function 
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insertion ng bagong user
$stmt = $conn->prepare("INSERT INTO patient_account (name, province, city, barangay, contact_no, birthdate, age, email, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssssss", $name, $province, $city, $brgy, $contact, $birthdate, $age, $email, $hashedPassword);

if ($stmt->execute()) { // If successful and insertion, dito magdidirect yung user
    header('Location: dashboard.php');
    exit;
} else { // If failed, magreredirect parin sa index
    $_SESSION['error'] = 'Registration failed. Please try again.';
    header('Location: ../index.php');
    exit;
}
?>
