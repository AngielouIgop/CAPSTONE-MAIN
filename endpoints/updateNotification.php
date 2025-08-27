<?php 
require_once('../model/model.php');
header('Content-Type: application/json');

$model = new Model();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id > 0) {
        $result = $model->updateNotifStatus($id, 'read');
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
