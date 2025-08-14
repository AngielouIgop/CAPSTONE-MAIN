<?php
class Model
{
    public $db = null;
    function __construct()
    {
        try {
            $this->db = new mysqli('localhost', 'root', '', 'capstone');
        } catch (Exception $e) {
            exit('The database connection could not be established.');
        }
    }

    // ===================== GET FUNCTIONS =====================
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

    public function getAllUser()
    {
        $result = $this->db->query("SELECT * FROM user");
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        return $users;
    }

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

    public function getPicturePathById($userID)
    {
        $stmt = $this->db->prepare("SELECT profilePicture FROM user WHERE userID = ?");
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row['profilePicture'] : null;
    }
// --------get user points
public function getUserPoints($userID)
{
    $stmt = $this->db->prepare("SELECT totalCurrentPoints FROM user WHERE userID = ?");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? (int)$row['totalCurrentPoints'] : 0;
}

// ----- get reward by Id
public function getRewardById($rewardID)
{
    $stmt = $this->db->prepare("SELECT * FROM reward WHERE rewardID = ?");
    $stmt->bind_param("i", $rewardID);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

    // -----------------------------------------------------------------------------  Total Number Per Item
    public function getTotalPlastic(){
        $stmt = $this->db->prepare("SELECT SUM(quantity) as totalPlastic FROM wasteentry WHERE materialID = 1");
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row['totalPlastic'] : 0;
    }

public function getTotalBottles(){
    $stmt = $this->db->prepare("SELECT SUM(quantity) as totalBottles FROM wasteentry WHERE materialID = 2");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? $row['totalBottles'] : 0;
}

    public function getTotalCans(){
        $stmt = $this->db->prepare("SELECT SUM(quantity) as totalCans FROM wasteentry WHERE materialID = 3");
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row['totalCans'] : 0;
    }
// -------------------------------------------------------------------------Waste Contribution Per Month
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
            'totalQuantity' => (int)$row['totalQuantity']
        ];
    }

    return $data;
}

// ---------------------------------------------------------------------

// public function getContributionsPerZone()
// {
//     $stmt = $this->db->prepare("
//         SELECT u.zone AS zone, 
//                SUM(w.quantity) AS totalQuantity
//         FROM user u
//         LEFT JOIN wasteentry w ON w.userID = u.userID
//         GROUP BY u.zone
//         ORDER BY totalQuantity DESC
//     ");
//     $stmt->execute();
//     $result = $stmt->get_result();

//     $data = [];
//     while ($row = $result->fetch_assoc()) {
//         // Convert NULL sums to 0 in PHP instead of SQL
//         $data[] = [
//             'zone' => $row['zone'],
//             'totalQuantity' => $row['totalQuantity'] !== null ? (int) $row['totalQuantity'] : 0
//         ];
//     }

//     return $data;
// }

public function getContZone1(){
    $stmt = $this->db->prepare("
        SELECT SUM(w.quantity) AS totalQuantity
        FROM wasteentry w
        JOIN user u ON w.userID = u.userID
        WHERE u.zone = 'Zone 1' 
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? (int)$row['totalQuantity'] : 0;
}

public function getContZone2(){
    $stmt = $this->db->prepare("
        SELECT SUM(w.quantity) AS totalQuantity
        FROM wasteentry w
        JOIN user u ON w.userID = u.userID
        WHERE u.zone = 'Zone 2'
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? (int)$row['totalQuantity'] : 0;
}

public function getContZone3(){
    $stmt = $this->db->prepare("
        SELECT SUM(w.quantity) AS totalQuantity
        FROM wasteentry w
        JOIN user u ON w.userID = u.userID
        WHERE u.zone = 'Zone 3'
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? (int)$row['totalQuantity'] : 0;
}

public function getContZone4(){
    $stmt = $this->db->prepare("
        SELECT SUM(w.quantity) AS totalQuantity
        FROM wasteentry w
        JOIN user u ON w.userID = u.userID
        WHERE u.zone = 'Zone 4'
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? (int)$row['totalQuantity'] : 0;
}

public function getContZone5(){
    $stmt = $this->db->prepare("
        SELECT SUM(w.quantity) AS totalQuantity
        FROM wasteentry w
        JOIN user u ON w.userID = u.userID
        WHERE u.zone = 'Zone 5'
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? (int)$row['totalQuantity'] : 0;
}

public function getContZone6(){
    $stmt = $this->db->prepare("
        SELECT SUM(w.quantity) AS totalQuantity
        FROM wasteentry w
        JOIN user u ON w.userID = u.userID
        WHERE u.zone = 'Zone 6'
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? (int)$row['totalQuantity'] : 0;
}
public function getContZone7(){
    $stmt = $this->db->prepare("
        SELECT SUM(w.quantity) AS totalQuantity
        FROM wasteentry w
        JOIN user u ON w.userID = u.userID
        WHERE u.zone = 'Zone 7'
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? (int)$row['totalQuantity'] : 0;
}



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

// ----------------------------------------------------------------------------

// ------ get user total item
public function getUserTotalPlastic($userID){
    $stmt = $this->db->prepare("SELECT SUM(quantity) as totalPlastic FROM wasteentry WHERE userID = ? AND materialID = 1");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? (int)$row['totalPlastic'] : 0;

}

public function getUserTotalGlassBottles($userID){
    $stmt = $this->db->prepare("SELECT SUM(quantity) as totalBottles FROM wasteentry WHERE userID = ? AND materialID = 2");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? (int)$row['totalBottles'] : 0;
}

public function getUserTotalCans($userID){
    $stmt = $this->db->prepare("SELECT SUM(quantity) as totalCans FROM wasteentry WHERE userID = ? AND materialID = 3");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? (int)$row['totalCans'] : 0;
}

    // ===================== ADD FUNCTIONS =====================
    public function registerUser($fullname, $email, $zone, $contactNumber, $username, $password)
    {
        if ($this->userExists($username)) {
            return false;
        }
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO user (fullname, email, zone, contactNumber, username, password, role) VALUES (?,?,?,?,?,?,'user')";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ssssss", $fullname, $email, $zone, $contactNumber, $username, $hashedPassword);
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

    public function addReward($rewardName, $pointsRequired, $slotNum, $availableStock, $imagePath, $availability = 1)
    {
        $query = "INSERT INTO reward (rewardName, pointsRequired, slotNum, availableStock, rewardImg, availability) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("siiisi", $rewardName, $pointsRequired, $slotNum, $availableStock, $imagePath, $availability);
        return $stmt->execute();
    }

    public function setCurrentUser($userID, $username)
    {
        $this->db->query("DELETE FROM `current_user`");
        $stmt = $this->db->prepare("INSERT INTO `current_user` (userID, username) VALUES (?, ?)");
        $stmt->bind_param("is", $userID, $username);
        $stmt->execute();
        $stmt->close();
    }

    // ===================== UPDATE FUNCTIONS =====================
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

    public function updateReward($rewardName, $pointsRequired, $slotNum, $availableStock, $rewardID, $imagePath, $availability) {
        if (!empty($imagePath)) {
            $query = "UPDATE reward 
                    SET rewardName = ?, pointsRequired = ?, slotNum = ?, availableStock = ?, availability = ?, rewardImg = ? 
                    WHERE rewardID = ?";
            $params = [$rewardName, $pointsRequired, $slotNum, $availableStock, $availability, $imagePath, $rewardID];
        } else {
            $query = "UPDATE reward 
                    SET rewardName = ?, pointsRequired = ?, slotNum = ?, availableStock = ?, availability = ? 
                    WHERE rewardID = ?";
            $params = [$rewardName, $pointsRequired, $slotNum, $availableStock, $availability, $rewardID];
        }

        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }


    public function calcPoints($userID, $materialID, $quantity)
    {
        $query = "SELECT pointsPerItem FROM materialType WHERE materialID = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $materialID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $pointsPerItem = $row['pointsPerItem'];
            $pointsEarned = $pointsPerItem * $quantity;

            $updateQuery = "UPDATE user SET totalCurrentPoints = COALESCE(totalCurrentPoints, 0) + ? WHERE userID = ?";
            $updateStmt = $this->db->prepare($updateQuery);
            if (!$updateStmt) {
                error_log("Prepare failed: " . $this->db->error);
                return 0;
            }

            if (!$updateStmt->bind_param("ii", $pointsEarned, $userID)) {
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

    // ===================== DELETE FUNCTIONS =====================
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

    // ===================== UTILITY FUNCTIONS =====================
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
        $query = "SELECT * FROM user WHERE username = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
}
?>