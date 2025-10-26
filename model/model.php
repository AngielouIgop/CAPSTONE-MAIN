<?php
class Model
{
    public $db = null;

    function __construct()
    {
        try {
            require_once(__DIR__ . '/../config/database.php');
            $this->db = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

            if ($this->db->connect_error) {
                throw new Exception('Connection failed: ' . $this->db->connect_error);
            }

            $this->db->set_charset(DB_CHARSET);

        } catch (Exception $e) {
            error_log('Database connection error: ' . $e->getMessage());
            exit('The database connection could not be established.');
        }
    }

    // ===========================================
    // USER FUNCTIONS
    // ===========================================

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

    public function getUserPoints($userID)
    {
        $stmt = $this->db->prepare("SELECT totalCurrentPoints FROM user WHERE userID = ?");
        $stmt->bind_param("d", $userID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? (int) $row['totalCurrentPoints'] : 0;
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

    public function getAllRewards()
    {
        $stmt = $this->db->prepare("SELECT * FROM reward ORDER BY pointsRequired ASC");
        $stmt->execute();
        $result = $stmt->get_result();
        $rewards = [];
        while ($row = $result->fetch_assoc()) {
            $rewards[] = $row;
        }
        $stmt->close();
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

        return $zoneLeaders;
    }

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

    public function registerUser($fullname, $email, $zone, $brgyIDNum, $contactNumber, $username, $password)
    {
        if ($this->userExists($username) || $this->pendingUserExists($username)) {
            return false;
        }
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO pending_registrations (fullName, email, zone, brgyIDNum, contactNumber, username, password) VALUES (?,?,?,?,?,?,?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("sssssss", $fullname, $email, $zone, $brgyIDNum, $contactNumber, $username, $hashedPassword);
        return $stmt->execute();
    }

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

    public function getRegistrationStatus($username)
    {
        $query = "SELECT status FROM pending_registrations WHERE username = ? ORDER BY submittedAt DESC LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['status'];
        }
        return null;
    }

    public function isRegistrationPending($username)
    {
        $status = $this->getRegistrationStatus($username);
        return $status === 'pending';
    }

    public function isRegistrationRejected($username)
    {
        $status = $this->getRegistrationStatus($username);
        return $status === 'rejected';
    }

    public function setCurrentUser($userID, $username)
    {
        $roleStmt = $this->db->prepare("SELECT role FROM user WHERE userID = ?");
        $roleStmt->bind_param("i", $userID);
        $roleStmt->execute();
        $roleResult = $roleStmt->get_result();
        $userRole = 'user';
        if ($roleResult && ($roleRow = $roleResult->fetch_assoc())) {
            $userRole = $roleRow['role'];
        }
        $roleStmt->close();

        $checkStmt = $this->db->prepare("SELECT id FROM `current_user` WHERE userID = ? AND is_active = TRUE");
        $checkStmt->bind_param("i", $userID);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $updateStmt = $this->db->prepare("UPDATE `current_user` SET username = ?, role = ?, last_activity = CURRENT_TIMESTAMP WHERE userID = ? AND is_active = TRUE");
            $updateStmt->bind_param("ssi", $username, $userRole, $userID);
            $updateStmt->execute();
            $updateStmt->close();
        } else {
            $stmt = $this->db->prepare("INSERT INTO `current_user` (userID, username, role) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $userID, $username, $userRole);
            $stmt->execute();
            $stmt->close();
        }
        $checkStmt->close();
    }

    public function clearCurrentUser()
    {
        $stmt = $this->db->prepare("DELETE FROM `current_user`");
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function clearUserSession($userID)
    {
        $stmt = $this->db->prepare("UPDATE current_user SET is_active = FALSE WHERE userID = ?");
        $stmt->bind_param("i", $userID);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

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

    public function calcPoints($userID, $materialID, $quantity, $materialWeight)
    {
        $query = "SELECT pointsPerItem, thresholdMaterialWeight FROM materialType WHERE materialID = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $materialID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $pointsPerItem = $row['pointsPerItem'];
            $thresholdMaterialWeight = $row['thresholdMaterialWeight'];

            $pointsEarned = $pointsPerItem * $quantity;

            // Calculate weight difference for bonus points
            $weightDifference = $materialWeight - $thresholdMaterialWeight;
            
            if ($weightDifference > 0) {
                if ($weightDifference >= 5 && $weightDifference <= 50) {
                    $pointsEarned += 0.3;
                } elseif ($weightDifference >= 51 && $weightDifference <= 100) {
                $pointsEarned += 0.5;
                } elseif ($weightDifference > 100) {
                    $pointsEarned += 1.0;
                }
            }

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

    public function verifyIdentity($username, $email)
    {
        $query = "SELECT userID, username, email FROM user WHERE username = ? AND email = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
            return false;
        }

    public function verifyUserIdentity($username, $email)
    {
        $query = "SELECT userID, username, email FROM user WHERE username = ? AND email = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        return false;
    }

    public function generatePasswordResetToken($userID)
    {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $query = "INSERT INTO password_reset_tokens (userID, token, expires_at) VALUES (?, ?, ?) 
              ON DUPLICATE KEY UPDATE token = ?, expires_at = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('issss', $userID, $token, $expiry, $token, $expiry);

        if ($stmt->execute()) {
            return $token;
        }
        return false;
    }

    public function verifyPasswordResetToken($userID, $token)
    {
        $query = "SELECT * FROM password_reset_tokens WHERE userID = ? AND token = ? AND expires_at > NOW()";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('is', $userID, $token);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows === 1;
    }

    public function updateUserPassword($userID, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $query = "UPDATE user SET password = ? WHERE userID = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('si', $hashedPassword, $userID);

        if ($stmt->execute()) {
            $this->deletePasswordResetToken($userID);
            return true;
        }
        return false;
    }

    public function deletePasswordResetToken($userID)
    {
        $query = "DELETE FROM password_reset_tokens WHERE userID = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $userID);
        return $stmt->execute();
    }

    public function verifyRecaptcha($recaptchaResponse)
    {
        $secretKey = RECAPTCHA_SECRET_KEY;
        $recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify';
        
        $data = array(
            'secret' => $secretKey,
            'response' => $recaptchaResponse,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        );
        
        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            )
        );
        
        $context = stream_context_create($options);
        $result = file_get_contents($recaptchaUrl, false, $context);
        $response = json_decode($result, true);
        
        return $response['success'] ?? false;
    }

    // ===========================================
    // ADMIN FUNCTIONS
    // ===========================================

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

    public function getAllUser()
    {
        $stmt = $this->db->prepare("SELECT * FROM user");
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

    public function getNotifications()
    {
        $stmt = $this->db->prepare("SELECT * FROM sensor_notifications WHERE status = ?");
        $status = 'unread';
        $stmt->bind_param('s', $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $notifications = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $notifications;
    }

    public function getPendingRegistrationNotifications()
    {
        $stmt = $this->db->prepare("SELECT * FROM pending_registrations WHERE status = ? ORDER BY submittedAt DESC");
        $status = 'pending';
        $stmt->bind_param('s', $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $notifications = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $notifications;
    }

    public function getAllNotifications()
    {
        return $this->getNotifications();
    }

    public function getPendingRegistrations()
    {
        $stmt = $this->db->prepare("SELECT * FROM pending_registrations WHERE status = ? ORDER BY submittedAt DESC");
        $status = 'pending';
        $stmt->bind_param('s', $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $registrations = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $registrations;
    }

    public function approveRegistration($registrationId)
    {
        $query = "SELECT * FROM pending_registrations WHERE id = ? AND status = 'pending'";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $registrationId);
        $stmt->execute();
        $result = $stmt->get_result();
        $pendingUser = $result->fetch_assoc();

        if (!$pendingUser) {
            return false;
        }

        $insertQuery = "INSERT INTO user (fullname, email, zone, contactNumber, username, password, role, totalCurrentPoints) VALUES (?,?,?,?,?,?,'user', 0.00)";
        $insertStmt = $this->db->prepare($insertQuery);
        $insertStmt->bind_param("ssssss", $pendingUser['fullName'], $pendingUser['email'], $pendingUser['zone'], $pendingUser['contactNumber'], $pendingUser['username'], $pendingUser['password']);

        if ($insertStmt->execute()) {
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

    public function addReward($rewardName, $pointsRequired, $slotNum, $availableStock, $imagePath, $availability = 1)
    {
        $query = "INSERT INTO reward (rewardName, pointsRequired, slotNum, availableStock, rewardImg, availability) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("siiisi", $rewardName, $pointsRequired, $slotNum, $availableStock, $imagePath, $availability);
        return $stmt->execute();
    }

    public function updateReward($rewardName, $pointsRequired, $slotNum, $availableStock, $rewardID, $imagePath, $availability)
    {
        $query = "UPDATE reward 
                  SET rewardName = ?, pointsRequired = ?, slotNum = ?, availableStock = ?, availability = ? 
                  WHERE rewardID = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("siiiii", $rewardName, $pointsRequired, $slotNum, $availableStock, $availability, $rewardID);
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

    public function getRewardImagePathById($rewardID)
    {
        $stmt = $this->db->prepare("SELECT rewardImg FROM reward WHERE rewardID = ?");
        $stmt->bind_param("i", $rewardID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row['rewardImg'] : null;
    }

    public function validateAndUploadImage($file, $targetDir)
    {
        $result = ['success' => false, 'error' => '', 'path' => ''];

        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            $result['error'] = "No file uploaded or upload error occurred.";
            return $result;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            $result['error'] = "File size too large. Maximum size is 5MB.";
            return $result;
        }

        $imageFileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowedTypes)) {
            $result['error'] = "Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.";
            return $result;
        }

        $check = getimagesize($file['tmp_name']);
        if ($check === false) {
            $result['error'] = "File is not a valid image.";
            return $result;
        }

        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                $result['error'] = "Failed to create target directory.";
                return $result;
            }
        }

        $fileName = uniqid() . '_' . basename($file['name']);
        $targetPath = $targetDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $result['success'] = true;
            $result['path'] = $targetPath;
        } else {
            $result['error'] = "Failed to upload file.";
        }

        return $result;
    }

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
            JOIN materialtype m ON w.materialID = m.materialID
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
        $stmt->close();
        return $data;
    }

    public function getZoneContributionByDate($zone, $date)
    {
        $stmt = $this->db->prepare("
            SELECT u.zone, SUM(w.quantity) AS totalQuantity
            FROM user u
            LEFT JOIN wasteentry w ON u.userID = w.userID AND DATE(w.dateDeposited) = ?
            WHERE u.zone = ?
            GROUP BY u.zone
        ");
        $stmt->bind_param("ss", $date, $zone);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row['totalQuantity'] : 0;
    }

    public function getWasteHistoryByDate($date)
    {
        $stmt = $this->db->prepare("
            SELECT w.entryID, w.dateDeposited, w.timeDeposited, w.quantity, w.materialWeight, w.pointsEarned,
                   m.materialName, u.fullName, u.zone
            FROM wasteentry w
            INNER JOIN materialtype m ON w.materialID = m.materialID
            INNER JOIN user u ON w.userID = u.userID
            WHERE DATE(w.dateDeposited) = ?
            ORDER BY w.dateDeposited DESC, w.timeDeposited DESC
        ");
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
        return $data;
    }

    public function getAllActiveUsers()
    {
        $stmt = $this->db->prepare("
            SELECT cu.userID, cu.username, cu.role, cu.login_time, cu.last_activity, u.fullName, u.email, u.zone
            FROM current_user cu
            INNER JOIN user u ON cu.userID = u.userID
            WHERE cu.is_active = TRUE
            ORDER BY cu.last_activity DESC
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $stmt->close();
        return $users;
    }

    public function getActiveUsersByRole($role)
    {
        $stmt = $this->db->prepare("
            SELECT cu.userID, cu.username, cu.role, cu.login_time, cu.last_activity, u.fullName, u.email, u.zone
            FROM current_user cu
            INNER JOIN user u ON cu.userID = u.userID
            WHERE cu.is_active = TRUE AND cu.role = ?
            ORDER BY cu.last_activity DESC
        ");
        $stmt->bind_param("s", $role);
        $stmt->execute();
        $result = $stmt->get_result();
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $stmt->close();
        return $users;
    }

    public function cleanupInactiveSessions($hours = 24)
    {
        $stmt = $this->db->prepare("
            UPDATE current_user 
            SET is_active = FALSE 
            WHERE last_activity < DATE_SUB(NOW(), INTERVAL ? HOUR) AND is_active = TRUE
        ");
        $stmt->bind_param("i", $hours);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
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

    public function getPicturePathById($userID)
    {
        $stmt = $this->db->prepare("SELECT profilePicture FROM user WHERE userID = ?");
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row ? $row['profilePicture'] : null;
}
}
?>