<?php
require_once __DIR__ . "/session.php";
include "db.php";

$firstName = trim($_POST['firstName'] ?? '');
$lastName = trim($_POST['lastName'] ?? '');
$phone = trim($_POST['phoneNumber'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

if (empty($firstName) || empty($lastName) || empty($phone) || empty($password)) {
    header("Location: register.php?error=Please fill all fields");
    exit();
}

if (!preg_match("/^05[0-9]{8}$/", $phone)) {
    header("Location: register.php?error=Invalid phone number format");
    exit();
}

$hasUppercase = preg_match('@[A-Z]@', $password);
$hasNumber = preg_match('@[0-9]@', $password);

if (strlen($password) < 8 || !$hasUppercase || !$hasNumber) {
    header("Location: register.php?error=Password must be 8 chars, include capital letter and number");
    exit();
}

if ($password !== $confirmPassword) {
    header("Location: register.php?error=Passwords do not match");
    exit();
}

$checkStmt = $conn->prepare("SELECT userID FROM user WHERE phoneNumber = ?");
$checkStmt->bind_param("s", $phone);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows > 0) {
    header("Location: register.php?error=Phone already exists");
    exit();
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$userStmt = $conn->prepare("INSERT INTO user (firstName, lastName, phoneNumber, password, role)
                            VALUES (?, ?, ?, ?, 'resident')");
$userStmt->bind_param("ssss", $firstName, $lastName, $phone, $hashedPassword);

if ($userStmt->execute()) {

    $userID = $conn->insert_id;

    $residentStmt = $conn->prepare("INSERT INTO resident (residentID, points) VALUES (?, 0)");
    $residentStmt->bind_param("i", $userID);
    $residentStmt->execute();

    session_regenerate_id(true);   // new session ID after sign-up
    $_SESSION['userID'] = $userID;
    $_SESSION['role'] = "resident";

    header("Location: main.php");
    exit();

} else {
    header("Location: register.php?error=Something went wrong");
}
?>