<?php
/**
 * Routes API Endpoint
 * Handles CRUD operations for routes
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

try {
    switch ($method) {
        case 'GET':
            // Get all routes or specific route
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
            // Create new route
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data) {
                $data = $_POST;
            }

            // Validation
            if (
                empty($data['vehicle_number']) || empty($data['driver_name']) || empty($data['trip_from']) ||
                empty($data['trip_to']) || empty($data['trip_date'])
            ) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'All fields are required']);
                break;
            }

            // Check if trip date is not in the past
            if (strtotime($data['trip_date']) < strtotime(date('Y-m-d'))) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Trip date cannot be in the past']);
                break;
            }

            $stmt = $conn->prepare("INSERT INTO routes (vehicle_number, driver_name, trip_from, trip_to, trip_date, more_details) VALUES (?, ?, ?, ?, ?, ?)");
            $result = $stmt->execute([
                $data['vehicle_number'],
                $data['driver_name'],
                $data['trip_from'],
                $data['trip_to'],
                $data['trip_date'],
                $data['more_details'] ?? ''
            ]);

            if ($result) {
                $id = $conn->lastInsertId();
                echo json_encode(['success' => true, 'message' => 'Route created successfully', 'id' => $id]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to create route']);
            }
            break;

        case 'PUT':
            // Update existing route
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Route ID is required']);
                break;
            }

            // Validation
            if (
                empty($data['vehicle_number']) || empty($data['driver_name']) || empty($data['trip_from']) ||
                empty($data['trip_to']) || empty($data['trip_date'])
            ) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'All fields are required']);
                break;
            }

            // Check if trip date is not in the past
            if (strtotime($data['trip_date']) < strtotime(date('Y-m-d'))) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Trip date cannot be in the past']);
                break;
            }

            $stmt = $conn->prepare("UPDATE routes SET vehicle_number = ?, driver_name = ?, trip_from = ?, trip_to = ?, trip_date = ?, more_details = ? WHERE id = ?");
            $result = $stmt->execute([
                $data['vehicle_number'],
                $data['driver_name'],
                $data['trip_from'],
                $data['trip_to'],
                $data['trip_date'],
                $data['more_details'] ?? '',
                $data['id']
            ]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Route updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update route']);
            }
            break;

        case 'DELETE':
            // Delete route
            $id = $_GET['id'] ?? null;

            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Route ID is required']);
                break;
            }

            $stmt = $conn->prepare("DELETE FROM routes WHERE id = ?");
            $result = $stmt->execute([$id]);

            if ($result) {
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
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

$database->closeConnection();
?>