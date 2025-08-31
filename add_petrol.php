<?php
header('Content-Type: application/json');

// Database connection
$mysqli = new mysqli("localhost", "root", "", "PPA_transport", 3307);
if ($mysqli->connect_error) {
    echo json_encode(["status" => "error", "error" => "Connection failed: " . $mysqli->connect_error]);
    exit;
}

// Collect and validate POST data
$vehicle_id = isset($_POST['vehicle_id']) ? intval($_POST['vehicle_id']) : 0;
$driver_id = isset($_POST['driver_id']) ? intval($_POST['driver_id']) : 0;
$date = $_POST['date'] ?? '';
$liters = isset($_POST['liters']) ? floatval($_POST['liters']) : 0;
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

// Basic validation
if ($vehicle_id <= 0 || $driver_id <= 0 || empty($date) || $liters <= 0 || $amount <= 0) {
    echo json_encode(["status" => "error", "error" => "Invalid input data"]);
    exit;
}

// Prepare statement
$stmt = $mysqli->prepare("
    INSERT INTO petrol_bills (vehicle_id, driver_id, date, liters, amount, created_at)
    VALUES (?, ?, ?, ?, ?, NOW())
");

if (!$stmt) {
    echo json_encode(["status" => "error", "error" => $mysqli->error]);
    exit;
}

// Bind parameters: i=int, i=int, s=string, d=double, d=double
$stmt->bind_param("iisdd", $vehicle_id, $driver_id, $date, $liters, $amount);

// Execute
if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "error" => $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>