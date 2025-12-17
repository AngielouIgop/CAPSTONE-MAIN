<?php
/**
 * Activity Heartbeat Endpoint
 * Handles user activity updates and auto-logout functionality
 */

header('Content-Type: application/json');
session_start();

require_once('../model/model.php');

try {
    $model = new Model();
    
    // Check if user is logged in
    if (!isset($_SESSION['user']) || !isset($_SESSION['user']['userID'])) {
        echo json_encode(['error' => 'User not logged in']);
        exit;
    }

    $userID = $_SESSION['user']['userID'];
    $action = $_POST['action'] ?? 'heartbeat';

    if ($action === 'auto_logout') {
        // Auto-logout due to inactivity
        $currentSessionId = session_id();
        
        // Delete from current_user table
        try {
            // Find the record by userID or session_id and get its id
            $stmt = $model->db->prepare("SELECT id FROM `current_user` WHERE (userID = ? OR current_session_id = ?) AND is_active = 1 LIMIT 1");
            if ($userID && $currentSessionId) {
                $stmt->bind_param("is", $userID, $currentSessionId);
            } elseif ($userID) {
                $stmt = $model->db->prepare("SELECT id FROM `current_user` WHERE userID = ? AND is_active = 1 LIMIT 1");
                $stmt->bind_param("i", $userID);
            } else {
                $stmt = $model->db->prepare("SELECT id FROM `current_user` WHERE current_session_id = ? AND is_active = 1 LIMIT 1");
                $stmt->bind_param("s", $currentSessionId);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            if ($row && isset($row['id'])) {
                $recordId = $row['id'];
                // Delete the record using the id
                $deleteStmt = $model->db->prepare("DELETE FROM `current_user` WHERE id = ?");
                $deleteStmt->bind_param("i", $recordId);
                $deleteStmt->execute();
                $deleteStmt->close();
            }
        } catch (Exception $e) {
            error_log("Auto-logout error: " . $e->getMessage());
        }
        
        // Clear all session variables
        $_SESSION = array();
        
        // Delete the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy the session completely
        session_unset();
        session_destroy();
        
        echo json_encode(['success' => true, 'message' => 'Auto-logged out due to inactivity']);
        
    } else {
        // Regular heartbeat - update last_activity
        // First check if user still exists in current_user table
        $checkStmt = $model->db->prepare("SELECT id FROM `current_user` WHERE userID = ? AND is_active = 1 LIMIT 1");
        $checkStmt->bind_param("i", $userID);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $checkStmt->close();
        
        if ($checkResult->num_rows === 0) {
            // User was already logged out (maybe by another process or manual logout)
            echo json_encode(['error' => 'Session expired', 'logout' => true]);
            exit;
        }
        
        $success = $model->updateUserActivity($userID);
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Activity updated']);
        } else {
            echo json_encode(['error' => 'Failed to update activity']);
        }
    }
    
} catch (Exception $e) {
    error_log("Activity heartbeat error: " . $e->getMessage());
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>

