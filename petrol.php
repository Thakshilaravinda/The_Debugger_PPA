<?php
$mysqli = new mysqli("localhost", "root", "", "PPA_transport");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$result = $mysqli->query("SELECT bill_id, vehicle_id, driver_id, date, liters, amount FROM petrol_bills ORDER BY bill_id DESC");
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
echo json_encode($data);
$mysqli->close();
?>