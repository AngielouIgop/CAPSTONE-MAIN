<?php
require_once('../model/model.php');
header('Content-Type: application/json');

$model = new Model();
$result = $model->db->query("SELECT userID, username FROM `current_user` LIMIT 1");
if ($result && ($row = $result->fetch_assoc())) {
    echo json_encode([
        'userID' => $row['userID'],
        'username' => $row['username']
    ]);
} else {
    echo json_encode(['error' => 'No user logged in']);
}
?>