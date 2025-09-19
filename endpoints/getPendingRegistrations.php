<?php

class GetPendingRegistrations
{
    public $model = null;

    function __construct()
    {
        $this->model = new Model();
    }

    public function processRequest()
    {
        header('Content-Type: application/json');

        try {
            $pendingRegistrations = $this->model->getPendingRegistrations();
            
            echo json_encode($pendingRegistrations);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}

require_once('../model/model.php');
$endpoint = new GetPendingRegistrations();
$endpoint->processRequest();
?>
