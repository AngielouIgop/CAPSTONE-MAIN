<?php
header('Content-Type: application/json');

// ==================== MODEL INITIALIZATION ====================
require_once('../model/model.php');
$model = new Model();

// ==================== CHECK RECENT REDEMPTIONS ====================
// Check for recent redemptions from any slot (within last 3 seconds)
$query = "SELECT r.*, rew.slotNum FROM redemption r
          INNER JOIN reward rew ON r.rewardID = rew.rewardID
          WHERE r.redemption >= DATE_SUB(NOW(), INTERVAL 3 SECOND) 
          AND rew.slotNum IN (?, ?, ?)
          ORDER BY r.redemption DESC LIMIT 1";

$stmt = $model->db->prepare($query);
$slot1 = 1;
$slot2 = 2;
$slot3 = 3;
$stmt->bind_param('iii', $slot1, $slot2, $slot3);
$stmt->execute();
$result = $stmt->get_result();
$redemption = $result->fetch_assoc();
$stmt->close();

// ==================== RESPONSE GENERATION ====================
if ($redemption) {
    echo json_encode([
        'trigger' => true,
        'slotNum' => (int)$redemption['slotNum'],
        'redemptionID' => $redemption['redemptionID'],
        'redemption_time' => $redemption['redemption']
    ]);
} else {
    echo json_encode([
        'trigger' => false
    ]);
}
?>