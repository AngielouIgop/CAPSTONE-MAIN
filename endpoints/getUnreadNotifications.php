<?php

class GetUnreadNotifications
{
    public $model = null;

    function __construct()
    {
        require_once('../model/model.php');
        $this->model = new Model();
    }

    public function processRequest()
    {
        header('Content-Type: application/json');

        $notifications = $this->model->getAllNotifications(); // includes both sensor and pending registration notifications
        echo json_encode($notifications);
    }
}
require_once('../model/model.php');
$getUnreadNotifications = new GetUnreadNotifications();
$getUnreadNotifications->processRequest();
?>