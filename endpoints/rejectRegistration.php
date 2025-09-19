<?php

class RejectRegistration
{
    public $model = null;

    function __construct()
    {
        $this->model = new Model();
    }

    public function processRequest()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }

        $registrationId = $_POST['registrationId'] ?? null;

        if (!$registrationId) {
            http_response_code(400);
            echo json_encode(['error' => 'Registration ID is required']);
            return;
        }

        try {
            $result = $this->model->rejectRegistration($registrationId);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Registration rejected successfully'
                ]);
            } else {
                throw new Exception('Failed to reject registration');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}

require_once('../model/model.php');
$endpoint = new RejectRegistration();
$endpoint->processRequest();
?>
