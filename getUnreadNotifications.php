<?php
require_once('model/model.php');
header('Content-Type: application/json');

$model = new Model();
$notifications = $model->getNotifications(); // only unread
echo json_encode($notifications);
