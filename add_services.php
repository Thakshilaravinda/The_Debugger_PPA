<?php
header('Content-Type: application/json');

// Database connection
$mysqli = new mysqli("localhost", "root", "", "PPA_transport");
if ($mysqli->connect_error) {
    echo json_encode(["status" => "error", "error" => "Connection failed: " . $mysqli->connect_error]);
    exit;
}

// Collect and validate POST data
$vehicle_id = isset($_POST['vehicle_id']) ? intval($_POST['vehicle_id']) : 0;
$service_type = $_POST['service_type'] ?? '';
$service_date = $_POST['service_date'] ?? '';
$cost = isset($_POST['cost']) ? floatval($_POST['cost']) : 0;
$notes = $_POST['notes'] ?? '';

// Basic validation
if ($vehicle_id <= 0 || empty($service_type) || empty($service_date) || $cost <= 0) {
    echo json_encode(["status" => "error", "error" => "Invalid input data"]);
    exit;
}

// Prepare statement
$stmt = $mysqli->prepare("
    INSERT INTO vehicle_services (vehicle_id, service_type, service_date, cost, notes, created_at)
    VALUES (?, ?, ?, ?, ?, NOW())
");

if (!$stmt) {
    echo json_encode(["status" => "error", "error" => $mysqli->error]);
    exit;
}

// Bind parameters: i=int, s=string, s=string, d=double, s=string
$stmt->bind_param("issds", $vehicle_id, $service_type, $service_date, $cost, $notes);

// Execute
if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "error" => $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>