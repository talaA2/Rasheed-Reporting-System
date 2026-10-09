<?php
require_once __DIR__ . "/session.php";
include "db.php";

$phone = trim($_POST['phoneNumber'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($phone) || empty($password)) {
    header("Location: login.php?error=Please fill all fields");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM user WHERE phoneNumber = ?");
$stmt->bind_param("s", $phone);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: login.php?error=Invalid phone number or password");
    exit();
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password'])) {
    header("Location: login.php?error=Invalid phone number or password");
    exit();
}

session_regenerate_id(true);   // new session ID after login
$_SESSION['userID'] = $user['userID'];
$_SESSION['role'] = $user['role'];

if ($user['role'] == 'admin') {
    header("Location: admin.php");
    exit();
} 
elseif ($user['role'] == 'resident') {
    header("Location: main.php");
    exit();
} 
else {
    header("Location: login.php?error=Invalid role");
    exit();
}
?>
