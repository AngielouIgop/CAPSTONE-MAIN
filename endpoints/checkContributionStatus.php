<?php

class CheckContributionStatus
{
    public $model = null;

    function __construct()
    {
        
        $this->model = new Model();
    }

    public function processRequest()
    {
        header('Content-Type: application/json');
        
        $flagPath = __DIR__ . DIRECTORY_SEPARATOR . 'json files/contribution_flag.json';

        // 1) Primary source of truth: flag file written by setContributionStatus.php
        if (file_exists($flagPath)) {
            $raw = file_get_contents($flagPath);
            $data = json_decode($raw, true);
            if (is_array($data) && array_key_exists('contribution_started', $data)) {
                echo json_encode([
                    'status' => 'success',
                    'contribution_started' => (bool)$data['contribution_started'],
                    'userID' => $data['userID'] ?? null,
                    'username' => $data['username'] ?? null,
                    'timestamp' => $data['timestamp'] ?? null
                ]);
                exit;
            }
        }

        // 2) If no flag or unreadable, DO NOT auto-start. Optionally include current user info using shared Model DB.
        $userID = null;
        $username = null;

        if ($result = $this->model->db->query("SELECT userID, username FROM `current_user` LIMIT 1")) {
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $userID = (int)$row['userID'];
                $username = (string)$row['username'];
            }
        }

        echo json_encode([
            'status' => 'success',
            'contribution_started' => false,
            'userID' => $userID,
            'username' => $username,
            'message' => 'Waiting for start flag'
        ]);
    }
}
require_once('../model/model.php');
$checkStatus = new CheckContributionStatus();
$checkStatus->processRequest();
?>