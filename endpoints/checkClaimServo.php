<?php
header('Content-Type: application/json');

// Include model
require_once('../model/model.php');
$model = new Model();

// Check for recent redemptions from any slot (within last 3 seconds)
$query = "SELECT r.*, rew.slotNum FROM redemption r
          INNER JOIN reward rew ON r.rewardID = rew.rewardID
          WHERE r.redemption >= DATE_SUB(NOW(), INTERVAL 3 SECOND) 
          AND rew.slotNum IN (1, 2, 3)
          ORDER BY r.redemption DESC LIMIT 1";

$result = $model->db->query($query);
$redemption = $result->fetch_assoc();

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
