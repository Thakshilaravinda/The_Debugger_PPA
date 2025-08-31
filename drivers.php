<?php
header("Content-Type: application/json");
$mysqli = new mysqli("localhost", "root", "", "PPA_transport");
if ($mysqli->connect_error) {
    echo json_encode(["error" => "Connection failed"]);
    exit;
}

if (isset($_GET['id'])) {
    // Return single driver
    $id = intval($_GET['id']);
    $stmt = $mysqli->prepare("SELECT driver_id, name, phone, license_no, address FROM drivers WHERE driver_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $driver = $result->fetch_assoc();
    echo $driver ? json_encode($driver) : json_encode(["error" => "Driver not found"]);
    $stmt->close();
} else {
    // Return all drivers
    $result = $mysqli->query("SELECT driver_id, name, phone, license_no, address FROM drivers ORDER BY driver_id DESC");
    $drivers = [];
    while ($row = $result->fetch_assoc())
        $drivers[] = $row;
    echo json_encode($drivers);
}

$mysqli->close();
?>