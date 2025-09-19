<?php

class GetCurrentUser
{
    public $model = null;

    function __construct()
    {
        $this->model = new Model();
    }

    public function processRequest()
    {
        header('Content-Type: application/json');

        $result = $this->model->db->query("SELECT userID, username FROM `current_user` LIMIT 1");
        if ($result && ($row = $result->fetch_assoc())) {
            echo json_encode([
                'userID' => $row['userID'],
                'username' => $row['username']
            ]);
        } else {
            echo json_encode(['error' => 'No user logged in']);
        }
    }
}

require_once('../model/model.php');
$getCurrentUser = new GetCurrentUser();
$getCurrentUser->processRequest();
?>