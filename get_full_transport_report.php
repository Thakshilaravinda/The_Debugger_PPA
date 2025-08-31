<?php
header('Content-Type: application/json');

// DB connection
$conn = new mysqli('localhost', 'root', '', 'PPA_transport');
if ($conn->connect_error) {
    echo json_encode(['error' => "Database connection failed"]);
    exit;
}

// Get filters safely
$year = isset($_GET['year']) ? intval($_GET['year']) : null;
$month = isset($_GET['month']) ? intval($_GET['month']) : null;

// Helper function for WHERE clause
function addDateFilter($field, $year, $month)
{
    $clauses = [];
    if ($year)
        $clauses[] = "YEAR($field) = $year";
    if ($month)
        $clauses[] = "MONTH($field) = $month";
    if (count($clauses) > 0)
        return "WHERE " . implode(" AND ", $clauses);
    return "";
}

// --- DRIVERS ---
$drivers = [];
$res = $conn->query("SELECT * FROM drivers " . addDateFilter('created_at', $year, $month) . " ORDER BY created_at DESC");
if ($res) {
    while ($row = $res->fetch_assoc())
        $drivers[] = $row;
}

// --- VEHICLES ---
$vehicles = [];
$res = $conn->query("SELECT * FROM vehicles " . addDateFilter('created_at', $year, $month) . " ORDER BY created_at DESC");
if ($res) {
    while ($row = $res->fetch_assoc())
        $vehicles[] = $row;
}

// --- PETROL BILLS ---
$petrol = [];
$query = "SELECT p.bill_id, p.date, p.liters, p.amount, d.name as driver_name, v.registration_no as vehicle_reg 
          FROM petrol_bills p 
          JOIN drivers d ON p.driver_id=d.driver_id 
          JOIN vehicles v ON p.vehicle_id=v.vehicle_id 
          " . addDateFilter('p.date', $year, $month) . " 
          ORDER BY p.date DESC";
$res = $conn->query($query);
if ($res) {
    while ($row = $res->fetch_assoc())
        $petrol[] = $row;
}

// --- VEHICLE SERVICES ---
$services = [];
$query = "SELECT s.service_id, s.service_type, s.service_date, s.cost, s.notes, v.registration_no as vehicle_reg 
          FROM vehicle_services s 
          JOIN vehicles v ON s.vehicle_id=v.vehicle_id 
          " . addDateFilter('s.service_date', $year, $month) . " 
          ORDER BY s.service_date DESC";
$res = $conn->query($query);
if ($res) {
    while ($row = $res->fetch_assoc())
        $services[] = $row;
}

// Return JSON
echo json_encode([
    'drivers' => $drivers,
    'vehicles' => $vehicles,
    'petrol_bills' => $petrol,
    'vehicle_services' => $services
]);
?>