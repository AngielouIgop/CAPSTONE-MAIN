<?php

class Endpoint
{
    public $model = null;

    function __construct()
    {
        $this->model = new Model();
    }

    public function processRequest()
    {
        header('Content-Type: application/json');

        // Handle GET requests (fetch user)
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (!isset($_SESSION['user']['userID'])) {
                http_response_code(401);
                echo json_encode(['error' => 'User not logged in']);
                exit;
            }

            echo json_encode([
                'userID' => $_SESSION['user']['userID'],
                'username' => $_SESSION['user']['username'] ?? 'Unknown'
            ]);
            exit;
        }


        // ✅ Handle waste entry only
        $material = $_POST['material'] ?? '';
        $weight = isset($_POST['weight']) ? floatval($_POST['weight']) : 0.0;
        
        // Set timezone to Philippines (or your preferred timezone)
        date_default_timezone_set('Asia/Manila');
        
        $dateDeposited = date('Y-m-d');
        $timeDeposited = date('H:i:s');
        $userID = $_POST['userID'] ?? '';

        if (empty($material) || !isset($_POST['weight']) || empty($userID)) {
            http_response_code(400);
            echo json_encode(["error" => "Missing required fields"]);
            return;
        }

        try {
            // Check if user exists
            $userCheck = $this->model->getUserById($userID);
            if (!$userCheck) {
                throw new Exception("Invalid userID: $userID");
            }

            // Get material info
            $materialQuery = "SELECT materialID FROM materialtype WHERE materialName = ?";
            $stmt = $this->model->db->prepare($materialQuery);
            if (!$stmt) {
                throw new Exception("Material query prepare failed: " . $this->model->db->error);
            }
            
            $stmt->bind_param("s", $material);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                throw new Exception("Material not found: $material");
            }

            $row = $result->fetch_assoc();
            $materialID = $row['materialID'];
            $quantity = 1;

            $stmt->close();

            // Calculate points
            $pointsEarned = $this->model->calcPoints($userID, $materialID, $quantity, $weight);

            // Insert waste entry
            $sql = "INSERT INTO wasteentry (userID, materialID, quantity, pointsEarned, dateDeposited, timeDeposited, materialWeight)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->model->db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Insert query prepare failed: " . $this->model->db->error);
            }
            
            $stmt->bind_param("iiidssd", $userID, $materialID, $quantity, $pointsEarned, $dateDeposited, $timeDeposited, $weight);

            if ($stmt->execute()) {
                echo json_encode([
                    "success" => true,
                    "message" => "Material inserted. Points: $pointsEarned"
                ]);
            } else {
                throw new Exception("Insert failed: " . $stmt->error);
            }

            $stmt->close();
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }
}

require_once('../model/model.php');
$endpoint = new Endpoint();
$endpoint->processRequest();
?>