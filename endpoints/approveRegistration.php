<?php

class ApproveRegistration
{
    public $model = null;

    function __construct()
    {
        $this->model = new Model();
    }

    public function processRequest()
    {
        header('Content-Type: application/json');

        // ==================== REQUEST VALIDATION ====================
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

        // ==================== REGISTRATION APPROVAL ====================
        try {
            $result = $this->model->approveRegistration($registrationId);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Registration approved successfully'
                ]);
            } else {
                throw new Exception('Failed to approve registration');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}

require_once('../model/model.php');
$endpoint = new ApproveRegistration();
$endpoint->processRequest();
?>