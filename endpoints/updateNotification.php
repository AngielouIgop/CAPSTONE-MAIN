<?php

class UpdateNotification
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

            if ($id > 0) {
                $result = $this->model->updateNotifStatus($id, 'read');
                echo json_encode([
                    'success' => $result,
                    'message' => $result ? 'Notification marked as read.' : 'Failed to update notification.'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid notification ID.'
                ]);
            }
        } else {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid request method. Use POST.'
            ]);
        }
    }
}

require_once('../model/model.php');
$updateNotification = new UpdateNotification();
$updateNotification->processRequest();
?>