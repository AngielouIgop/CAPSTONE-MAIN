<?php
require_once '../model/model.php';

header('Content-Type: application/json');

try {
    $model = new Model();
    $pendingRegistrations = $model->getPendingRegistrations();
    
    echo json_encode($pendingRegistrations);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch pending registrations: ' . $e->getMessage()]);
}
?>
