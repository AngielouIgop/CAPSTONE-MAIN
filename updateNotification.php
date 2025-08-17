<?php
require_once('model/model.php');

header('Content-Type: application/json'); // Always return JSON

$model = new Model();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the update function exists in Model
    if (method_exists($model, 'updateNotifStatus')) {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $result = $model->updateNotifStatus($id);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'All notifications marked as read.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update notifications.'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Function updateNotifStatus() not found in Model.'
        ]);
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method. Please use POST.'
    ]);
}
?>
