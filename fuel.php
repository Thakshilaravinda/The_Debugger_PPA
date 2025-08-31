<?php
/**
 * Fuel Records API Endpoint
 * Handles CRUD operations for fuel records
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
            // Get all fuel records or specific record
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
            // Create new fuel record
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data) {
                $data = $_POST;
            }

            // Validation
            if (
                empty($data['date']) || empty($data['vehicle_number']) || empty($data['fuel_type']) ||
                empty($data['liters']) || empty($data['total_value'])
            ) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'All fields are required']);
                break;
            }

            if ($data['liters'] <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Liters must be greater than 0']);
                break;
            }

            if ($data['total_value'] <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Total value must be greater than 0']);
                break;
            }

            $stmt = $conn->prepare("INSERT INTO fuel_records (date, vehicle_number, fuel_type, liters, total_value) VALUES (?, ?, ?, ?, ?)");
            $result = $stmt->execute([
                $data['date'],
                $data['vehicle_number'],
                $data['fuel_type'],
                $data['liters'],
                $data['total_value']
            ]);

            if ($result) {
                $id = $conn->lastInsertId();
                echo json_encode(['success' => true, 'message' => 'Fuel record created successfully', 'id' => $id]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to create fuel record']);
            }
            break;

        case 'PUT':
            // Update existing fuel record
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Fuel record ID is required']);
                break;
            }

            // Validation
            if (
                empty($data['date']) || empty($data['vehicle_number']) || empty($data['fuel_type']) ||
                empty($data['liters']) || empty($data['total_value'])
            ) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'All fields are required']);
                break;
            }

            if ($data['liters'] <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Liters must be greater than 0']);
                break;
            }

            if ($data['total_value'] <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Total value must be greater than 0']);
                break;
            }

            $stmt = $conn->prepare("UPDATE fuel_records SET date = ?, vehicle_number = ?, fuel_type = ?, liters = ?, total_value = ? WHERE id = ?");
            $result = $stmt->execute([
                $data['date'],
                $data['vehicle_number'],
                $data['fuel_type'],
                $data['liters'],
                $data['total_value'],
                $data['id']
            ]);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Fuel record updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update fuel record']);
            }
            break;

        case 'DELETE':
            // Delete fuel record
            $id = $_GET['id'] ?? null;

            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Fuel record ID is required']);
                break;
            }

            $stmt = $conn->prepare("DELETE FROM fuel_records WHERE id = ?");
            $result = $stmt->execute([$id]);

            if ($result) {
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
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

$database->closeConnection();
?>