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

        // ==================== FETCH CURRENT USER ====================
        // Get the current user where role is 'user' (simple structure)
        $stmt = $this->model->db->prepare("
            SELECT cu.userID, cu.username 
            FROM `current_user` cu
            INNER JOIN user u ON cu.userID = u.userID
            WHERE u.role = 'user'
            LIMIT 1
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && ($row = $result->fetch_assoc())) {
            echo json_encode([
                'userID' => $row['userID'],
                'username' => $row['username']
            ]);
        } else {
            echo json_encode(['error' => 'No user logged in']);
        }
        $stmt->close();
    }
}

require_once('../model/model.php');
$getCurrentUser = new GetCurrentUser();
$getCurrentUser->processRequest();
?>