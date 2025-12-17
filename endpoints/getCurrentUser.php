<?php
header('Content-Type: application/json');

try {
    require_once('../model/model.php');
    $model = new Model();
    
    // ==================== FETCH CURRENT USER ====================
    // Get the current user where role is 'user' and is_active = TRUE
    $stmt = $model->db->prepare("
        SELECT cu.userID, cu.username, cu.role, cu.is_active, cu.last_activity
        FROM `current_user` cu
        INNER JOIN user u ON cu.userID = u.userID
        WHERE u.role = 'user' AND cu.is_active = TRUE
        ORDER BY cu.last_activity DESC
        LIMIT 1
    ");
    
    if (!$stmt) {
        echo json_encode(['error' => 'Database query preparation failed']);
        exit;
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && ($row = $result->fetch_assoc())) {
        // ==================== UPDATE USER ACTIVITY ====================
        $model->updateUserActivity($row['userID']);
        
        echo json_encode([
            'userID' => (int)$row['userID'],
            'username' => $row['username']
        ]);
    } else {
        echo json_encode([
            'error' => 'No active user logged in'
        ]);
    }
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
