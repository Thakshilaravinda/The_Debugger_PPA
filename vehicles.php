<?php
$mysqli = new mysqli("localhost", "root", "", "PPA_transport");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$result = $mysqli->query("SELECT vehicle_id, registration_no, model, type, capacity FROM vehicles ORDER BY vehicle_id DESC");
$vehicles = [];
while ($row = $result->fetch_assoc()) {
    $vehicles[] = $row;
}
echo json_encode($vehicles);
$mysqli->close();
?>