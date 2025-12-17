<?php
session_start();

class SetContributionStatus
{
    public $model = null;

    function __construct()
    {
        require_once('../model/model.php');
        $this->model = new Model();
    }

    public function processRequest()
    {
        header('Content-Type: application/json');

        // ==================== SESSION VALIDATION ====================
        if (!isset($_SESSION['user'])) {
            echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
            exit;
        }

        $db = $this->model->db;

        // ==================== INPUT PROCESSING ====================
        $action = $_POST['action'] ?? '';

        // Path to the flag file the Arduino will poll - match the path in checkContributionStatus.php
        $flagPath = __DIR__ . DIRECTORY_SEPARATOR . 'json files' . DIRECTORY_SEPARATOR . 'contribution_flag.json';

        // ==================== DIRECTORY SETUP ====================
        $flagDir = dirname($flagPath);
        if (!is_dir($flagDir)) {
            if (!mkdir($flagDir, 0755, true)) {
                error_log("Failed to create directory: " . $flagDir);
                echo json_encode(['status' => 'error', 'message' => 'Failed to create directory']);
                exit;
            }
        }

        // ==================== GET USER FROM SESSION ====================
        $userID = $_SESSION['user']['userID'];
        $username = $_SESSION['user']['username'];

        // ==================== ENSURE USER EXISTS IN current_user TABLE ====================
        // Check if user exists in current_user table
        $checkStmt = $db->prepare("SELECT id FROM `current_user` WHERE userID = ?");
        if ($checkStmt) {
            $checkStmt->bind_param("i", $userID);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows === 0) {
                // User doesn't exist in current_user table, add them
                $this->model->setCurrentUser($userID, $username, session_id());
            }
            $checkStmt->close();
        }

        // ==================== UPDATE USER ACTIVITY ====================
        $this->model->updateUserActivity($userID);

        // ==================== CONTRIBUTION START ====================
        if ($action === 'start') {
            $payload = [
                'contribution_started' => true,
                'userID' => (int)$userID,
                'username' => (string)$username,
                'timestamp' => date('Y-m-d H:i:s')
            ];

            $result = file_put_contents($flagPath, json_encode($payload));
            if ($result !== false) {
                error_log("Contribution flag written successfully: " . $flagPath);
                echo json_encode(['status' => 'success', 'message' => 'Contribution started']);
            } else {
                error_log("Failed to write contribution flag to: " . $flagPath);
                echo json_encode(['status' => 'error', 'message' => 'Failed to write flag']);
            }
        } 
        // ==================== CONTRIBUTION STOP ====================
        elseif ($action === 'stop') {
            $payload = [
                'contribution_started' => false,
                'userID' => (int)$userID,
                'username' => (string)$username,
                'timestamp' => date('Y-m-d H:i:s')
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