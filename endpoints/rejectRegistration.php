<?php
require_once '../model/model.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$registrationId = $_POST['registrationId'] ?? null;

if (!$registrationId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Registration ID is required']);
    exit;
}

try {
    $model = new Model();
    $result = $model->rejectRegistration($registrationId);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Registration rejected successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to reject registration']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error rejecting registration: ' . $e->getMessage()]);
}
?>
