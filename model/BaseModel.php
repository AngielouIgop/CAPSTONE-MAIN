<?php
class BaseModel
{
    public $db = null;

    // ========================================================
    // ===================== CONSTRUCTOR ======================
    // ========================================================
    function __construct()
    {
        try {
            $this->db = new mysqli('localhost', 'root', '', 'capstone');
        } catch (Exception $e) {
            exit('The database connection could not be established.');
        }
    }

    // ========================================================
    // ===================== SHARED FUNCTIONS =================
    // ========================================================

    // ----- AUTHENTICATION -----
    public function loginUser($username, $password, $role)
    {
        $query = "SELECT * FROM user WHERE username = ? AND role = ?";
        if ($stmt = $this->db->prepare($query)) {
            $stmt->bind_param('ss', $username, $role);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    return $user;
                }
            }
        }
        return false;
    }

    public function userExists($username)
    {
        $query = "SELECT 1 FROM user WHERE username = ? LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->free_result();
        $stmt->close();
        return $exists;
    }

    public function setCurrentUser($userID, $username)
    {
        $this->db->query("DELETE FROM `current_user`");
        $stmt = $this->db->prepare("INSERT INTO `current_user` (userID, username) VALUES (?, ?)");
        $stmt->bind_param("is", $userID, $username);
        $stmt->execute();
        $stmt->close();
    }

    // ----- REWARD FUNCTIONS -----
    public function getAllRewards()
    {
        $query = "SELECT * FROM reward ORDER BY pointsRequired ASC";
        $result = $this->db->query($query);
        $rewards = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $rewards[] = $row;
            }
        }
        return $rewards;
    }

    public function getRewardById($rewardID)
    {
        $stmt = $this->db->prepare("SELECT * FROM reward WHERE rewardID = ?");
        $stmt->bind_param("i", $rewardID);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // ----- NOTIFICATION FUNCTIONS -----
    public function getNotifications()
    {
        $result = $this->db->query("SELECT * FROM sensor_notifications WHERE status = 'unread'");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function updateNotifStatus($id, $status = 'read')
    {
        $stmt = $this->db->prepare("UPDATE sensor_notifications SET status = ? WHERE id = ?");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    // ----- POINTS CALCULATION -----
    public function calcPoints($userID, $materialID, $quantity, $materialWeight)
    {
        // Get material data (points per item + threshold weight)
        $query = "SELECT pointsPerItem, thresholdMaterialWeight FROM materialType WHERE materialID = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $materialID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $pointsPerItem = $row['pointsPerItem'];
            $thresholdMaterialWeight = $row['thresholdMaterialWeight'];

            // Base points
            $pointsEarned = $pointsPerItem * $quantity;

            // ✅ Extra +0.5 points if inserted weight is greater than threshold
            if ($materialWeight > $thresholdMaterialWeight) {
                $pointsEarned += 0.5;
            }

            // Update user points
            $updateQuery = "UPDATE user 
                        SET totalCurrentPoints = COALESCE(totalCurrentPoints, 0) + ? 
                        WHERE userID = ?";
            $updateStmt = $this->db->prepare($updateQuery);
            if (!$updateStmt) {
                error_log("Prepare failed: " . $this->db->error);
                return 0;
            }

            if (!$updateStmt->bind_param("di", $pointsEarned, $userID)) {
                error_log("Binding parameters failed: " . $updateStmt->error);
                return 0;
            }

            if ($updateStmt->execute()) {
                error_log("Points updated successfully. UserID: $userID, Points Earned: $pointsEarned");
                return $pointsEarned;
            } else {
                error_log("Execute failed: " . $updateStmt->error);
                return 0;
            }
        }
        return 0;
    }
}
?>
