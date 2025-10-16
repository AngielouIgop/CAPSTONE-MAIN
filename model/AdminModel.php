<?php
require_once 'BaseModel.php';

class AdminModel extends BaseModel
{
    // ========================================================
    // ===================== ADMIN-SPECIFIC FUNCTIONS ========
    // ========================================================

    // ----- USER MANAGEMENT -----
    public function getAllUsers()
    {
        $stmt = $this->db->prepare("SELECT * FROM user WHERE role = 'user'");
        $stmt->execute();
        $result = $stmt->get_result();
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $stmt->close();
        return $users;
    }

    public function getTopUsers($limit = 7)
    {
        $stmt = $this->db->prepare("SELECT * FROM user WHERE role = 'user' LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $stmt->close();
        return $users;
    }

    public function getUserData($userID)
    {
        $stmt = $this->db->prepare("SELECT fullName, username, password, email, contactNumber, zone, profilePicture FROM user WHERE userID = ?");
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function updateUserProfile($userID, $fullName, $zone, $email, $contactNumber, $username, $hashedPassword)
    {
        $sql = "UPDATE user SET fullName=?, zone=?, email=?, contactNumber=?, username=?";
        $params = [$fullName, $zone, $email, $contactNumber, $username];
        $types = "sssss";

        if ($hashedPassword) {
            $sql .= ", password=?";
            $params[] = $hashedPassword;
            $types .= "s";
        }

        $sql .= " WHERE userID=?";
        $params[] = $userID;
        $types .= "i";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        return $stmt->execute();
    }

    public function getAllAdmins()
    {
        $stmt = $this->db->prepare("SELECT * FROM user WHERE role = 'admin'");
        $stmt->execute();
        $result = $stmt->get_result();
        $admins = [];
        while ($row = $result->fetch_assoc()) {
            $admins[] = $row;
        }
        $stmt->close();
        return $admins;
    }

    public function getAllUser()
    {
        $result = $this->db->query("SELECT * FROM user");
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        return $users;
    }

    public function deleteUser($userID)
    {
        $stmt = $this->db->prepare("DELETE FROM user WHERE userID = ?");
        if (!$stmt) {
            return "Error preparing statement: " . $this->db->error;
        }
        $stmt->bind_param("i", $userID);
        if ($stmt->execute()) {
            $affected = $stmt->affected_rows;
            $stmt->close();
            if ($affected > 0) {
                return "User deleted successfully.";
            } else {
                return "No user found with that ID.";
            }
        } else {
            $error = $stmt->error;
            $stmt->close();
            return "Error deleting user: $error";
        }
    }

    // ----- PENDING REGISTRATIONS -----
    public function getPendingRegistrations()
    {
        $query = "SELECT * FROM pending_registrations WHERE status = 'pending' ORDER BY submittedAt DESC";
        $result = $this->db->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function approveRegistration($registrationId)
    {
        // Get pending registration data
        $query = "SELECT * FROM pending_registrations WHERE id = ? AND status = 'pending'";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $registrationId);
        $stmt->execute();
        $result = $stmt->get_result();
        $pendingUser = $result->fetch_assoc();

        if (!$pendingUser) {
            return false;
        }

        // Insert into users table
        $insertQuery = "INSERT INTO user (fullname, email, zone, contactNumber, username, password, role, totalCurrentPoints) VALUES (?,?,?,?,?,?,'user', 0.00)";
        $insertStmt = $this->db->prepare($insertQuery);
        $insertStmt->bind_param("ssssss", $pendingUser['fullName'], $pendingUser['email'], $pendingUser['zone'], $pendingUser['contactNumber'], $pendingUser['username'], $pendingUser['password']);

        if ($insertStmt->execute()) {
            // Update pending registration status
            $updateQuery = "UPDATE pending_registrations SET status = 'approved' WHERE id = ?";
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->bind_param("i", $registrationId);
            return $updateStmt->execute();
        }
        return false;
    }

    public function rejectRegistration($registrationId)
    {
        $query = "UPDATE pending_registrations SET status = 'rejected' WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $registrationId);
        return $stmt->execute();
    }

    public function addAdministrator($fullname, $email, $position, $contactNumber, $username, $password, $role = 'admin')
    {
        if (!in_array($role, ['user', 'admin'])) {
            return false;
        }
        if ($this->userExists($username)) {
            return false;
        }
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO user (fullname, email, position, contactNumber, username, password, role) VALUES (?,?,?,?,?,?,?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("sssssss", $fullname, $email, $position, $contactNumber, $username, $hashedPassword, $role);
        return $stmt->execute();
    }

    // ----- REWARD MANAGEMENT -----
    public function addReward($rewardName, $pointsRequired, $slotNum, $availableStock, $imagePath, $availability = 1)
    {
        $query = "INSERT INTO reward (rewardName, pointsRequired, slotNum, availableStock, rewardImg, availability) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("siiisi", $rewardName, $pointsRequired, $slotNum, $availableStock, $imagePath, $availability);
        return $stmt->execute();
    }

    public function updateReward($rewardName, $pointsRequired, $slotNum, $availableStock, $rewardID, $imagePath, $availability)
    {
        if (!empty($imagePath)) {
            $query = "UPDATE reward 
                      SET rewardName = ?, pointsRequired = ?, slotNum = ?, availableStock = ?, availability = ?, rewardImg = ? 
                      WHERE rewardID = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("siiisii", $rewardName, $pointsRequired, $slotNum, $availableStock, $availability, $imagePath, $rewardID);
        } else {
            $query = "UPDATE reward 
                      SET rewardName = ?, pointsRequired = ?, slotNum = ?, availableStock = ?, availability = ? 
                      WHERE rewardID = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("siiiii", $rewardName, $pointsRequired, $slotNum, $availableStock, $availability, $rewardID);
        }
        return $stmt->execute();
    }

    public function deleteReward($rewardID)
    {
        $stmt = $this->db->prepare("DELETE FROM reward WHERE rewardID = ?");
        if (!$stmt) {
            return "Error preparing statement: " . $this->db->error;
        }
        $stmt->bind_param("i", $rewardID);
        if ($stmt->execute()) {
            $affected = $stmt->affected_rows;
            $stmt->close();
            if ($affected > 0) {
                return "Reward deleted successfully.";
            } else {
                return "No reward found with that ID.";
            }
        } else {
            $error = $stmt->error;
            $stmt->close();
            return "Error deleting reward: $error";
        }
    }

    // ----- WASTE STATISTICS -----
    public function getTotalPlastic()
    {
        $stmt = $this->db->prepare("SELECT SUM(quantity) as totalPlastic FROM wasteentry WHERE materialID = 1");
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row['totalPlastic'] : 0;
    }

    public function getTotalBottles()
    {
        $stmt = $this->db->prepare("SELECT SUM(quantity) as totalBottles FROM wasteentry WHERE materialID = 2");
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row['totalBottles'] : 0;
    }

    public function getTotalCans()
    {
        $stmt = $this->db->prepare("SELECT SUM(quantity) as totalCans FROM wasteentry WHERE materialID = 3");
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row['totalCans'] : 0;
    }

    // ----- WASTE STATS PER MONTH -----
    public function getWasteContributionsPerMaterialThisMonth()
    {
        $stmt = $this->db->prepare("
            SELECT m.materialName, SUM(w.quantity) AS totalQuantity
            FROM wasteentry w
            JOIN materialType m ON w.materialID = m.materialID
            WHERE MONTH(w.dateDeposited) = MONTH(CURRENT_DATE())
              AND YEAR(w.dateDeposited) = YEAR(CURRENT_DATE())
            GROUP BY m.materialName
        ");
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'materialType' => $row['materialName'],
                'totalQuantity' => (int) $row['totalQuantity']
            ];
        }

        return $data;
    }

    // ----- CONTRIBUTIONS PER ZONE -----
    public function getContZone1()
    {
        return $this->getZoneContribution('Zone 1');
    }
    public function getContZone2()
    {
        return $this->getZoneContribution('Zone 2');
    }
    public function getContZone3()
    {
        return $this->getZoneContribution('Zone 3');
    }
    public function getContZone4()
    {
        return $this->getZoneContribution('Zone 4');
    }
    public function getContZone5()
    {
        return $this->getZoneContribution('Zone 5');
    }
    public function getContZone6()
    {
        return $this->getZoneContribution('Zone 6');
    }
    public function getContZone7()
    {
        return $this->getZoneContribution('Zone 7');
    }

    private function getZoneContribution($zone)
    {
        $stmt = $this->db->prepare("
            SELECT SUM(w.quantity) AS totalQuantity
            FROM wasteentry w
            JOIN user u ON w.userID = u.userID
            WHERE u.zone = ?
        ");
        $stmt->bind_param("s", $zone);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? (int) $row['totalQuantity'] : 0;
    }

    // ----- WASTE HISTORY -----
    public function getWasteHistory()
    {
        $stmt = $this->db->prepare("
            SELECT w.*, u.fullName, m.materialName
            FROM wasteentry w
            JOIN user u ON w.userID = u.userID
            JOIN materialType m ON w.materialID = m.materialID
            ORDER BY w.dateDeposited DESC
        ");
        $stmt->execute();
        $result = $stmt->get_result();

        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        return $history;
    }

    // ----- DATE FILTERED FUNCTIONS -----
    public function getTotalPlasticByDate($date)
    {
        $stmt = $this->db->prepare("SELECT SUM(quantity) as totalPlastic FROM wasteentry WHERE materialID = 1 AND DATE(dateDeposited) = ?");
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row['totalPlastic'] : 0;
    }

    public function getTotalBottlesByDate($date)
    {
        $stmt = $this->db->prepare("SELECT SUM(quantity) as totalBottles FROM wasteentry WHERE materialID = 2 AND DATE(dateDeposited) = ?");
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row['totalBottles'] : 0;
    }

    public function getTotalCansByDate($date)
    {
        $stmt = $this->db->prepare("SELECT SUM(quantity) as totalCans FROM wasteentry WHERE materialID = 3 AND DATE(dateDeposited) = ?");
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row['totalCans'] : 0;
    }

    public function getWasteContributionsPerMaterialByDate($date)
    {
        $stmt = $this->db->prepare("
            SELECT m.materialName, SUM(w.quantity) AS totalQuantity
            FROM wasteentry w
            JOIN materialType m ON w.materialID = m.materialID
            WHERE DATE(w.dateDeposited) = ?
            GROUP BY m.materialName
        ");
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'materialType' => $row['materialName'],
                'totalQuantity' => (int) $row['totalQuantity']
            ];
        }

        return $data;
    }

    public function getZoneContributionByDate($zone, $date)
    {
        $stmt = $this->db->prepare("
            SELECT SUM(w.quantity) AS totalQuantity
            FROM wasteentry w
            JOIN user u ON w.userID = u.userID
            WHERE u.zone = ? AND DATE(w.dateDeposited) = ?
        ");
        $stmt->bind_param("ss", $zone, $date);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? (int) $row['totalQuantity'] : 0;
    }

    public function getWasteHistoryByDate($date)
    {
        $stmt = $this->db->prepare("
            SELECT w.*, u.fullName, m.materialName
            FROM wasteentry w
            JOIN user u ON w.userID = u.userID
            JOIN materialType m ON w.materialID = m.materialID
            WHERE DATE(w.dateDeposited) = ?
            ORDER BY w.dateDeposited DESC
        ");
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result();

        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        return $history;
    }
}
?>
