<?php
$host = "localhost";
$user = "root";
$password = "root";
$database = "rasheed";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

require_once __DIR__ . "/helpers.php";
