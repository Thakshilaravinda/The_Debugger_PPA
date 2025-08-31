<?php
$mysqli = new mysqli("localhost", "root", "", "PPA_transport");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$id = $_POST['id'] ?? 0;
$stmt = $mysqli->prepare("DELETE FROM vehicle_services WHERE service_id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();
$mysqli->close();
?>