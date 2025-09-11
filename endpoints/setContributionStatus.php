<?php

class SetContributionStatus
{
    public $model = null;

    function __construct()
    {
        // require_once('model/model.php');
        $this->model = new Model();
    }

    public function processRequest()
    {
        header('Content-Type: application/json');

        $db = $this->model->db;

        $action = $_POST['action'] ?? '';

        // Path to the flag file the Arduino will poll - match the path in checkContributionStatus.php
        $flagPath = __DIR__ . DIRECTORY_SEPARATOR . 'json files' . DIRECTORY_SEPARATOR . 'contribution_flag.json';

        // Ensure the directory exists
        $flagDir = dirname($flagPath);
        if (!is_dir($flagDir)) {
            if (!mkdir($flagDir, 0755, true)) {
                error_log("Failed to create directory: " . $flagDir);
                echo json_encode(['status' => 'error', 'message' => 'Failed to create directory']);
                exit;
            }
        }

        // Try to get current user (if any)
        $currentUser = null;
        if ($result = $db->query("SELECT userID, username FROM `current_user` LIMIT 1")) {
            if ($result->num_rows > 0) {
                $currentUser = $result->fetch_assoc();
            }
        }

        if ($action === 'start') {
            if (!$currentUser) {
                echo json_encode(['status' => 'error', 'message' => 'No current user found']);
                exit;
            }

            $payload = [
                'contribution_started' => true,
                'userID' => (int)$currentUser['userID'],
                'username' => (string)$currentUser['username'],
                'timestamp' => time()
            ];

            $result = file_put_contents($flagPath, json_encode($payload));
            if ($result !== false) {
                error_log("Contribution flag written successfully: " . $flagPath);
                echo json_encode(['status' => 'success', 'message' => 'Contribution started']);
            } else {
                error_log("Failed to write contribution flag to: " . $flagPath);
                echo json_encode(['status' => 'error', 'message' => 'Failed to write flag']);
            }
        } elseif ($action === 'stop') {
            $payload = [
                'contribution_started' => false,
                'userID' => $currentUser ? (int)$currentUser['userID'] : null,
                'username' => $currentUser ? (string)$currentUser['username'] : null,
                'timestamp' => time()
            ];

            $result = file_put_contents($flagPath, json_encode($payload));
            if ($result !== false) {
                error_log("Contribution stopped flag written successfully: " . $flagPath);
                echo json_encode(['status' => 'success', 'message' => 'Contribution stopped']);
            } else {
                error_log("Failed to write stop flag to: " . $flagPath);
                echo json_encode(['status' => 'error', 'message' => 'Failed to write flag']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        }
    }
}

require_once('../model/model.php');
$setContributionStatus = new SetContributionStatus();
$setContributionStatus->processRequest();
?>