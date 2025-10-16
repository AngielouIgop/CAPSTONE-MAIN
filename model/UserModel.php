<?php
require_once 'BaseModel.php';

class UserModel extends BaseModel
{
    // ========================================================
    // ===================== USER-SPECIFIC FUNCTIONS =========
    // ========================================================

    // ----- USER DATA -----
    public function getUserById($userID)
    {
        $query = "SELECT * FROM user WHERE userID = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $userID);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getUserData($userID)
    {
        $stmt = $this->db->prepare("SELECT fullName, username, password, email, contactNumber, zone, profilePicture FROM user WHERE userID = ?");
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getPicturePathById($userID)
    {
        $stmt = $this->db->prepare("SELECT profilePicture FROM user WHERE userID = ?");
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row['profilePicture'] : null;
    }

    public function getUserPoints($userID)
    {
        $stmt = $this->db->prepare("SELECT totalCurrentPoints FROM user WHERE userID = ?");
        $stmt->bind_param("d", $userID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? (int) $row['totalCurrentPoints'] : 0;
    }

    // ----- USER WASTE DATA -----
    public function getUserTotalPlastic($userID)
    {
        $stmt = $this->db->prepare("SELECT SUM(quantity) as totalPlastic FROM wasteentry WHERE userID = ? AND materialID = 1");
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? (int) $row['totalPlastic'] : 0;
    }

    public function getUserTotalGlassBottles($userID)
    {
        $stmt = $this->db->prepare("SELECT SUM(quantity) as totalBottles FROM wasteentry WHERE userID = ? AND materialID = 2");
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? (int) $row['totalBottles'] : 0;
    }

    public function getUserTotalCans($userID)
    {
        $stmt = $this->db->prepare("SELECT SUM(quantity) as totalCans FROM wasteentry WHERE userID = ? AND materialID = 3");
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? (int) $row['totalCans'] : 0;
    }

    public function getUserWasteHistory($userID)
    {
        $stmt = $this->db->prepare("
            SELECT
                w.entryID,
                w.dateDeposited,
                w.timeDeposited,
                w.quantity,
                w.materialWeight,
                w.pointsEarned,
                m.materialName
            FROM wasteentry w
            INNER JOIN materialType m ON m.materialID = w.materialID
            WHERE w.userID = ?
            ORDER BY w.dateDeposited DESC, w.timeDeposited DESC, w.entryID DESC
            LIMIT 10
        ");
        $stmt->bind_param('i', $userID);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    }

    // ----- USER REGISTRATION -----
    public function registerUser($fullname, $email, $zone, $brgyIDNum, $contactNumber, $username, $password)
    {
        if ($this->userExists($username) || $this->pendingUserExists($username)) {
            return false;
        }
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO pending_registrations (fullName, email, zone, brgyIDNum, contactNumber, username, password) VALUES (?,?,?,?,?,?,?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("sssssss", $fullname, $email, $zone, $brgyID, $contactNumber, $username, $hashedPassword);
        return $stmt->execute();
    }

    public function pendingUserExists($username)
    {
        $query = "SELECT COUNT(*) FROM pending_registrations WHERE username = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_row()[0];
        return $count > 0;
    }

    // ----- USER PROFILE UPDATES -----
    public function updateProfileSettings($userID, $fullName, $zone, $position, $email, $contactNumber, $username, $hashedPassword = null, $profilePicturePath = null)
    {
        $fields = "fullName=?, zone=?, position=?, email=?, contactNumber=?, username=?";
        $types = "ssssss";
        $params = [$fullName, $zone, $position, $email, $contactNumber, $username];

        if ($hashedPassword !== null) {
            $fields .= ", password=?";
            $types .= "s";
            $params[] = $hashedPassword;
        }
        if ($profilePicturePath !== null) {
            $fields .= ", profilePicture=?";
            $types .= "s";
            $params[] = $profilePicturePath;
        }

        $params[] = $userID;
        $types .= "i";

        $sql = "UPDATE user SET $fields WHERE userID=?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $result = $stmt->execute();

        return $result ? "Profile Updated" : $stmt->error;
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

    // ----- USER CONTRIBUTION DATA -----
    public function getMostContributedWaste()
    {
        $stmt = $this->db->prepare("
            SELECT m.materialID, m.materialName, m.materialImg, SUM(e.quantity) as totalQuantity
            FROM wasteentry e
            INNER JOIN materialtype m ON e.materialID = m.materialID
            GROUP BY m.materialID
            ORDER BY totalQuantity DESC
            LIMIT 1
        ");
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getTopContributors()
    {
        $stmt = $this->db->prepare("
            SELECT u.zone, u.userID, u.fullName, SUM(w.quantity) AS totalQuantity
            FROM user u
            LEFT JOIN wasteentry w ON u.userID = w.userID
            WHERE u.role = 'user'
            GROUP BY u.zone, u.userID, u.fullName
            HAVING totalQuantity IS NOT NULL
        ");
        $stmt->execute();
        $result = $stmt->get_result();

        $zoneLeaders = [];

        // loop through results, keep only top contributor per zone
        while ($row = $result->fetch_assoc()) {
            $zone = $row['zone'];
            $quantity = (int) $row['totalQuantity'];

            if (!isset($zoneLeaders[$zone]) || $quantity > $zoneLeaders[$zone]['totalQuantity']) {
                $zoneLeaders[$zone] = [
                    'userID' => $row['userID'],
                    'fullName' => $row['fullName'],
                    'totalQuantity' => $quantity
                ];
            }
        }

        return $zoneLeaders; // key = zone, value = top user
    }
}
?>
