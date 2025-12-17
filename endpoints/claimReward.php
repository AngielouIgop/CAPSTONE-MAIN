<?php
session_start();
header('Content-Type: application/json');

// ==================== SESSION VALIDATION ====================
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// ==================== INPUT VALIDATION ====================
if (!isset($_POST['rewardId']) || empty($_POST['rewardId'])) {
    echo json_encode(['success' => false, 'message' => 'Reward ID is required']);
    exit();
}

if (!isset($_POST['slotNum']) || empty($_POST['slotNum'])) {
    echo json_encode(['success' => false, 'message' => 'Slot number is required']);
    exit();
}

$rewardId = intval($_POST['rewardId']);
$slotNum = intval($_POST['slotNum']);
$userID = $_SESSION['user']['userID'];

// Debug logging
error_log("Claim attempt - UserID: $userID, RewardID: $rewardId, SlotNum: $slotNum");

// ==================== MODEL INITIALIZATION ====================
require_once('../model/model.php');
$model = new Model();

// ==================== UPDATE USER ACTIVITY ====================
$model->updateUserActivity($userID);

// ==================== USER POINTS CHECK ====================
$userPoints = $model->getUserPoints($userID);
error_log("User points: $userPoints");

// ==================== REWARD VALIDATION ====================
$rewardQuery = "SELECT * FROM reward WHERE rewardID = ? AND availability = 1";
$stmt = $model->db->prepare($rewardQuery);
$stmt->bind_param('i', $rewardId);
$stmt->execute();
$reward = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$reward) {
    error_log("Reward not found or not available - RewardID: $rewardId");
    echo json_encode(['success' => false, 'message' => 'Reward not available']);
    exit();
}

error_log("Reward found - Name: " . $reward['rewardName'] . ", Points Required: " . $reward['pointsRequired'] . ", Stock: " . $reward['availableStock']);

// ==================== ELIGIBILITY CHECKS ====================
if ($userPoints < $reward['pointsRequired']) {
    error_log("Insufficient points - User has: $userPoints, Required: " . $reward['pointsRequired']);
    echo json_encode(['success' => false, 'message' => 'Insufficient points']);
    exit();
}

if ($reward['availableStock'] <= 0) {
    error_log("Reward out of stock - Available: " . $reward['availableStock']);
    echo json_encode(['success' => false, 'message' => 'Reward out of stock']);
    exit();
}

// ==================== TRANSACTION PROCESSING ====================
$model->db->begin_transaction();

try {
    // ==================== DEDUCT USER POINTS ====================
    $newPoints = $userPoints - $reward['pointsRequired'];
    $updatePointsQuery = "UPDATE user SET totalCurrentPoints = ? WHERE userID = ?";
    $updatePointsStmt = $model->db->prepare($updatePointsQuery);
    $updatePointsStmt->bind_param('di', $newPoints, $userID);
    $updatePointsStmt->execute();
    $updatePointsStmt->close();

    // ==================== REDUCE REWARD STOCK ====================
    $newStock = $reward['availableStock'] - 1;
    $updateStockQuery = "UPDATE reward SET availableStock = ? WHERE rewardID = ?";
    $updateStockStmt = $model->db->prepare($updateStockQuery);
    $updateStockStmt->bind_param('ii', $newStock, $rewardId);
    $updateStockStmt->execute();
    $updateStockStmt->close();

    // ==================== RECORD REDEMPTION ====================
    $redemptionQuery = "INSERT INTO redemption (userID, rewardID, quantity, totalPointsUsed, redemptionDate) VALUES (?, ?, 1, ?, CURDATE())";
    $redemptionStmt = $model->db->prepare($redemptionQuery);
    $redemptionStmt->bind_param('iii', $userID, $rewardId, $reward['pointsRequired']);
    $redemptionStmt->execute();
    $redemptionStmt->close();

    // ==================== COMMIT TRANSACTION ====================
    $model->db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Reward claimed successfully',
        'newPoints' => $newPoints,
        'rewardName' => $reward['rewardName']
    ]);

} catch (Exception $e) {
    // ==================== ROLLBACK ON ERROR ====================
    $model->db->rollback();
    error_log("Claim reward error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to claim reward: ' . $e->getMessage()
    ]);
}
?>