<?php
/**
 * Main Transport API Handler
 * Handles all transport operations through a single endpoint
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../database.php';

$database = new Database();
$conn = $database->getConnection();

// Create tables if they don't exist
createTables($conn);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'drivers':
            handleDrivers($conn, $method);
            break;
        case 'vehicles':
            handleVehicles($conn, $method);
            break;
        case 'routes':
            handleRoutes($conn, $method);
            break;
        case 'fuel':
            handleFuel($conn, $method);
            break;
        case 'stats':
            getStats($conn);
            break;
        case 'search':
            searchRecords($conn, $_GET['query'] ?? '');
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

$database->closeConnection();

// Handler functions
function handleDrivers($conn, $method)
{
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $id = $_GET['id'];
                $stmt = $conn->prepare("SELECT * FROM drivers WHERE id = ?");
                $stmt->execute([$id]);
                $driver = $stmt->fetch();

                if ($driver) {
                    echo json_encode(['success' => true, 'data' => $driver]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Driver not found']);
                }
            } else {
                $stmt = $conn->query("SELECT * FROM drivers ORDER BY name");
                $drivers = $stmt->fetchAll();
                echo json_encode(['success' => true, 'data' => $drivers]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data)
                $data = $_POST;

            // Validation
            if (
                empty($data['name']) || empty($data['address']) || empty($data['age']) ||
                empty($data['telephone']) || empty($data['licence_id']) ||
                $data['age'] < 16 || $data['age'] > 70 ||
                !preg_match('/^07\d{8}$/', $data['telephone'])
            ) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid driver data']);
                break;
            }

            // Check duplicate licence
            $stmt = $conn->prepare("SELECT id FROM drivers WHERE licence_id = ?");
            $stmt->execute([$data['licence_id']]);
            if ($stmt->fetch()) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Licence ID already exists']);
                break;
            }

            $stmt = $conn->prepare("INSERT INTO drivers (name, address, age, telephone, licence_id) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$data['name'], $data['address'], $data['age'], $data['telephone'], $data['licence_id']])) {
                echo json_encode(['success' => true, 'message' => 'Driver created successfully', 'id' => $conn->lastInsertId()]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to create driver']);
            }
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Driver ID required']);
                break;
            }

            $stmt = $conn->prepare("UPDATE drivers SET name = ?, address = ?, age = ?, telephone = ?, licence_id = ? WHERE id = ?");
            if ($stmt->execute([$data['name'], $data['address'], $data['age'], $data['telephone'], $data['licence_id'], $data['id']])) {
                echo json_encode(['success' => true, 'message' => 'Driver updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update driver']);
            }
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Driver ID required']);
                break;
            }

            $stmt = $conn->prepare("DELETE FROM drivers WHERE id = ?");
            if ($stmt->execute([$id])) {
                echo json_encode(['success' => true, 'message' => 'Driver deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to delete driver']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
}

function handleVehicles($conn, $method)
{
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $id = $_GET['id'];
                $stmt = $conn->prepare("SELECT * FROM vehicles WHERE id = ?");
                $stmt->execute([$id]);
                $vehicle = $stmt->fetch();

                if ($vehicle) {
                    echo json_encode(['success' => true, 'data' => $vehicle]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Vehicle not found']);
                }
            } else {
                $stmt = $conn->query("SELECT * FROM vehicles ORDER BY model");
                $vehicles = $stmt->fetchAll();
                echo json_encode(['success' => true, 'data' => $vehicles]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data)
                $data = $_POST;

            // Validation
            if (
                empty($data['model']) || empty($data['mileage']) || empty($data['year']) ||
                empty($data['service_records']) || empty($data['registration_no']) ||
                $data['year'] < 1900 || $data['year'] > date('Y') + 1 ||
                $data['mileage'] < 0 || $data['service_records'] < 0
            ) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid vehicle data']);
                break;
            }

            // Check duplicate registration
            $stmt = $conn->prepare("SELECT id FROM vehicles WHERE registration_no = ?");
            $stmt->execute([$data['registration_no']]);
            if ($stmt->fetch()) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Registration number already exists']);
                break;
            }

            $stmt = $conn->prepare("INSERT INTO vehicles (model, mileage, year, service_records, registration_no) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$data['model'], $data['mileage'], $data['year'], $data['service_records'], $data['registration_no']])) {
                echo json_encode(['success' => true, 'message' => 'Vehicle created successfully', 'id' => $conn->lastInsertId()]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to create vehicle']);
            }
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Vehicle ID required']);
                break;
            }

            $stmt = $conn->prepare("UPDATE vehicles SET model = ?, mileage = ?, year = ?, service_records = ?, registration_no = ? WHERE id = ?");
            if ($stmt->execute([$data['model'], $data['mileage'], $data['year'], $data['service_records'], $data['registration_no'], $data['id']])) {
                echo json_encode(['success' => true, 'message' => 'Vehicle updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update vehicle']);
            }
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Vehicle ID required']);
                break;
            }

            $stmt = $conn->prepare("DELETE FROM vehicles WHERE id = ?");
            if ($stmt->execute([$id])) {
                echo json_encode(['success' => true, 'message' => 'Vehicle deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to delete vehicle']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
}

function handleRoutes($conn, $method)
{
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $id = $_GET['id'];
                $stmt = $conn->prepare("SELECT * FROM routes WHERE id = ?");
                $stmt->execute([$id]);
                $route = $stmt->fetch();

                if ($route) {
                    echo json_encode(['success' => true, 'data' => $route]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Route not found']);
                }
            } else {
                $stmt = $conn->query("SELECT * FROM routes ORDER BY trip_date DESC");
                $routes = $stmt->fetchAll();
                echo json_encode(['success' => true, 'data' => $routes]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data)
                $data = $_POST;

            // Validation
            if (
                empty($data['vehicle_number']) || empty($data['driver_name']) || empty($data['trip_from']) ||
                empty($data['trip_to']) || empty($data['trip_date']) ||
                strtotime($data['trip_date']) < strtotime(date('Y-m-d'))
            ) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid route data']);
                break;
            }

            $stmt = $conn->prepare("INSERT INTO routes (vehicle_number, driver_name, trip_from, trip_to, trip_date, more_details) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$data['vehicle_number'], $data['driver_name'], $data['trip_from'], $data['trip_to'], $data['trip_date'], $data['more_details'] ?? ''])) {
                echo json_encode(['success' => true, 'message' => 'Route created successfully', 'id' => $conn->lastInsertId()]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to create route']);
            }
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Route ID required']);
                break;
            }

            $stmt = $conn->prepare("UPDATE routes SET vehicle_number = ?, driver_name = ?, trip_from = ?, trip_to = ?, trip_date = ?, more_details = ? WHERE id = ?");
            if ($stmt->execute([$data['vehicle_number'], $data['driver_name'], $data['trip_from'], $data['trip_to'], $data['trip_date'], $data['more_details'] ?? '', $data['id']])) {
                echo json_encode(['success' => true, 'message' => 'Route updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update route']);
            }
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Route ID required']);
                break;
            }

            $stmt = $conn->prepare("DELETE FROM routes WHERE id = ?");
            if ($stmt->execute([$id])) {
                echo json_encode(['success' => true, 'message' => 'Route deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to delete route']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
}

function handleFuel($conn, $method)
{
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $id = $_GET['id'];
                $stmt = $conn->prepare("SELECT * FROM fuel_records WHERE id = ?");
                $stmt->execute([$id]);
                $fuel = $stmt->fetch();

                if ($fuel) {
                    echo json_encode(['success' => true, 'data' => $fuel]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Fuel record not found']);
                }
            } else {
                $stmt = $conn->query("SELECT * FROM fuel_records ORDER BY date DESC");
                $fuelRecords = $stmt->fetchAll();
                echo json_encode(['success' => true, 'data' => $fuelRecords]);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data)
                $data = $_POST;

            // Validation
            if (
                empty($data['date']) || empty($data['vehicle_number']) || empty($data['fuel_type']) ||
                empty($data['liters']) || empty($data['total_value']) ||
                $data['liters'] <= 0 || $data['total_value'] <= 0
            ) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid fuel data']);
                break;
            }

            $stmt = $conn->prepare("INSERT INTO fuel_records (date, vehicle_number, fuel_type, liters, total_value) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$data['date'], $data['vehicle_number'], $data['fuel_type'], $data['liters'], $data['total_value']])) {
                echo json_encode(['success' => true, 'message' => 'Fuel record created successfully', 'id' => $conn->lastInsertId()]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to create fuel record']);
            }
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Fuel record ID required']);
                break;
            }

            $stmt = $conn->prepare("UPDATE fuel_records SET date = ?, vehicle_number = ?, fuel_type = ?, liters = ?, total_value = ? WHERE id = ?");
            if ($stmt->execute([$data['date'], $data['vehicle_number'], $data['fuel_type'], $data['liters'], $data['total_value'], $data['id']])) {
                echo json_encode(['success' => true, 'message' => 'Fuel record updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update fuel record']);
            }
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Fuel record ID required']);
                break;
            }

            $stmt = $conn->prepare("DELETE FROM fuel_records WHERE id = ?");
            if ($stmt->execute([$id])) {
                echo json_encode(['success' => true, 'message' => 'Fuel record deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to delete fuel record']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
}

function getStats($conn)
{
    try {
        $stats = [];

        // Driver count
        $stmt = $conn->query("SELECT COUNT(*) FROM drivers");
        $stats['drivers'] = $stmt->fetchColumn();

        // Vehicle count
        $stmt = $conn->query("SELECT COUNT(*) FROM vehicles");
        $stats['vehicles'] = $stmt->fetchColumn();

        // Route count
        $stmt = $conn->query("SELECT COUNT(*) FROM routes");
        $stats['routes'] = $stmt->fetchColumn();

        // Fuel records count
        $stmt = $conn->query("SELECT COUNT(*) FROM fuel_records");
        $stats['fuel'] = $stmt->fetchColumn();

        // Total fuel cost
        $stmt = $conn->query("SELECT SUM(total_value) FROM fuel_records");
        $stats['totalFuelCost'] = $stmt->fetchColumn() ?: 0;

        // Total fuel liters
        $stmt = $conn->query("SELECT SUM(liters) FROM fuel_records");
        $stats['totalFuelLiters'] = $stmt->fetchColumn() ?: 0;

        echo json_encode(['success' => true, 'data' => $stats]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to get statistics']);
    }
}

function searchRecords($conn, $query)
{
    if (empty($query)) {
        echo json_encode(['success' => false, 'message' => 'Search query required']);
        return;
    }

    try {
        $searchTerm = "%$query%";
        $results = [];

        // Search in drivers
        $stmt = $conn->prepare("SELECT 'driver' as type, id, name as title, address as subtitle FROM drivers WHERE name LIKE ? OR address LIKE ? OR licence_id LIKE ?");
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        $results = array_merge($results, $stmt->fetchAll());

        // Search in vehicles
        $stmt = $conn->prepare("SELECT 'vehicle' as type, id, model as title, registration_no as subtitle FROM vehicles WHERE model LIKE ? OR registration_no LIKE ?");
        $stmt->execute([$searchTerm, $searchTerm]);
        $results = array_merge($results, $stmt->fetchAll());

        // Search in routes
        $stmt = $conn->prepare("SELECT 'route' as type, id, CONCAT(trip_from, ' to ', trip_to) as title, vehicle_number as subtitle FROM routes WHERE trip_from LIKE ? OR trip_to LIKE ? OR vehicle_number LIKE ?");
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        $results = array_merge($results, $stmt->fetchAll());

        // Search in fuel records
        $stmt = $conn->prepare("SELECT 'fuel' as type, id, vehicle_number as title, fuel_type as subtitle FROM fuel_records WHERE vehicle_number LIKE ? OR fuel_type LIKE ?");
        $stmt->execute([$searchTerm, $searchTerm]);
        $results = array_merge($results, $stmt->fetchAll());

        echo json_encode(['success' => true, 'data' => $results]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Search failed']);
    }
}
?>