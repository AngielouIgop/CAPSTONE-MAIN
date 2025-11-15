<?php
session_start();

class GetMonthlyReportData
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

        // ==================== SESSION VALIDATION ====================
        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        // ==================== INPUT VALIDATION ====================
        if (!isset($_GET['month']) || empty($_GET['month'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Month parameter is required']);
            exit;
        }

        $monthParam = $_GET['month'];
        if (!preg_match('/^\d{4}-\d{2}$/', $monthParam)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid month format. Use YYYY-MM']);
            exit;
        }

        // ==================== MONTHLY DATA PROCESSING ====================
        try {
            list($year, $month) = explode('-', $monthParam);
            
            // ==================== GET TOTALS FOR THE MONTH ====================
            $totalPlastic = $this->model->getTotalPlasticByMonth($year, $month);
            $totalCans = $this->model->getTotalCansByMonth($year, $month);
            $totalBottles = $this->model->getTotalBottlesByMonth($year, $month);
            
            // ==================== GET ZONE CONTRIBUTIONS FOR THE MONTH ====================
            $zones = [];
            for ($i = 1; $i <= 7; $i++) {
                $zoneName = "Zone $i";
                $total = $this->model->getZoneContributionByMonth($zoneName, $year, $month);
                $zones[] = ['zone' => $zoneName, 'total' => $total];
            }
            
            // ==================== GET TOP CONTRIBUTORS FOR THE MONTH ====================
            $contributors = $this->model->getTopContributorsByMonth($year, $month);
            
            // ==================== FORMAT CONTRIBUTORS DATA ====================
            $contributorsData = [];
            foreach ($contributors as $contributor) {
                $contributorsData[] = [
                    'fullName' => $contributor['fullName'],
                    'zone' => $contributor['zone'],
                    'contributed' => $contributor['totalContributed'],
                    'points' => $contributor['totalPoints']
                ];
            }
            
            $monthName = date('F Y', strtotime("$year-$month-01"));
            
            echo json_encode([
                'success' => true,
                'month' => $monthParam,
                'monthName' => $monthName,
                'totalPlastic' => $totalPlastic,
                'totalCans' => $totalCans,
                'totalBottles' => $totalBottles,
                'zones' => $zones,
                'contributors' => $contributorsData
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        }
    }
}

require_once('../model/model.php');
$getMonthlyReportData = new GetMonthlyReportData();
$getMonthlyReportData->processRequest();
?>

