<?php
$mysqli = new mysqli("localhost", "root", "", "PPA_transport");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$registration_no = $_POST['registration_no'] ?? '';
$model = $_POST['model'] ?? '';
$type = $_POST['type'] ?? '';
$capacity = $_POST['capacity'] ?? '';

$stmt = $mysqli->prepare("INSERT INTO vehicles (registration_no, model, type, capacity, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("ssss", $registration_no, $model, $type, $capacity);
$stmt->execute();
$stmt->close();
$mysqli->close();
?>