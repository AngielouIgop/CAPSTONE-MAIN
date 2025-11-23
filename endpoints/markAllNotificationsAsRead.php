<?php

class MarkAllNotificationsAsRead
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

        // ==================== REQUEST VALIDATION ====================
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // ==================== MARK ALL NOTIFICATIONS AS READ ====================
            $result = $this->model->markAllNotificationsAsRead();
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'All notifications marked as read.' : 'Failed to update notifications.'
            ]);
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
$markAllAsRead = new MarkAllNotificationsAsRead();
$markAllAsRead->processRequest();
?>

