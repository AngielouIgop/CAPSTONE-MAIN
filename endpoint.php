<?php
session_start();
require_once('model/model.php');

class Endpoint
{
    private $model;

    function __construct()
    {
        $this->model = new Model();
    }

    public function processRequest()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (!isset($_SESSION['userID'])) {
                http_response_code(401);
                echo json_encode(['error' => 'User not logged in']);
                exit;
            }

            echo json_encode([
                'userID' => $_SESSION['userID'],
                'username' => $_SESSION['username'] ?? 'Unknown'
            ]);
            exit;
        }

        // Debugging POST
        error_log("POST data: " . print_r($_POST, true));

        $material = $_POST['material'] ?? '';
        $weight = isset($_POST['weight']) ? floatval($_POST['weight']) : 0.0;
        $dateDeposited = date('Y-m-d');
        $timeDeposited = date('H:i:s');
        $userID = $_POST['userID'] ?? '';

        // Check if this is just a bin-full notification
        if (isset($_POST['binFull']) && $_POST['binFull'] == 1) {
            error_log("Plastic bin full notification received for user $userID");


            // Prepare insert
            $sensorName = "Plastic Bin"; // Or $_POST['sensorName'] if sent from ESP32
            $message = "Plastic bin is full";
            $status = "unread";

            $stmt = $this->model->db->prepare("INSERT INTO notifications (sensor_name, message, status) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $sensorName, $message, $status);

            if ($stmt->execute()) {
                echo json_encode([
                    "success" => true,
                    "message" => "Bin full notification saved"
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Failed to save notification"
                ]);
            }

            $stmt->close();

            return; // stop further processing
        }



        if (empty($material) || !isset($_POST['weight']) || empty($userID)) {
            error_log("Missing required fields");
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
            $materialQuery = "SELECT materialID FROM materialType WHERE materialName = ?";
            $stmt = $this->model->db->prepare($materialQuery);
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

            // Use the model’s calcPoints method to calculate earned points
            $pointsEarned = $this->model->calcPoints($userID, $materialID, $quantity);
            error_log("Points earned: $pointsEarned");

            // Insert waste entry
            $sql = "INSERT INTO wasteEntry (userID, materialID, quantity, pointsEarned, dateDeposited, timeDeposited, materialWeight)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->model->db->prepare($sql);
            $stmt->bind_param("iiisssd", $userID, $materialID, $quantity, $pointsEarned, $dateDeposited, $timeDeposited, $weight);

            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Material inserted. Points: $pointsEarned"]);
                error_log("Insert success: Material $material for user $userID, points: $pointsEarned");
            } else {
                throw new Exception("Insert failed: " . $stmt->error);
            }

            $stmt->close();
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            echo json_encode(["error" => $e->getMessage()]);
        }
    }
}

$endpoint = new Endpoint();
$endpoint->processRequest();
?>