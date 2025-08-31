<?php
$mysqli = new mysqli("localhost", "root", "", "PPA_transport");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$name = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';
$license_no = $_POST['license_no'] ?? '';
$address = $_POST['address'] ?? '';

$stmt = $mysqli->prepare("INSERT INTO drivers (name, phone, license_no, address, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("ssss", $name, $phone, $license_no, $address);
$stmt->execute();
$stmt->close();
$mysqli->close();
?>