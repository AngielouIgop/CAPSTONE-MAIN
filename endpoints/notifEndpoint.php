<?php

class NotifyEndpoint
{
    public $model = null;

    function __construct()
    {
        // require_once('../model/model.php');
        $this->model = new Model();
    }

    public function processRequest()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); // Method Not Allowed
            echo json_encode(['error' => 'Invalid request method']);
            return;
        }

        // Debug incoming POST
        error_log("Notification POST data: " . print_r($_POST, true));

        $sensorName = $_POST['sensor_name'] ?? '';
        $message    = $_POST['message'] ?? '';
        $status     = $_POST['status'] ?? 'unread';

        if (empty($sensorName) || empty($message)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        try {
            $sql = "INSERT INTO sensor_notifications (sensor_name, message, status, timestamp)
                    VALUES (?, ?, ?, NOW())";
            $stmt = $this->model->db->prepare($sql);
            $stmt->bind_param("sss", $sensorName, $message, $status);

            if ($stmt->execute()) {
                echo json_encode([
                    "success" => true,
                    "message" => "Notification stored successfully"
                ]);
                error_log("Notification saved: $sensorName - $message");
            } else {
                throw new Exception("Insert failed: " . $stmt->error);
            }

            $stmt->close();
        } catch (Exception $e) {
            error_log("Notification Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }
}

require_once('../model/model.php');
$endpoint = new NotifyEndpoint();
$endpoint->processRequest();
?>