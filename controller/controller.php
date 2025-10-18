<?php

class Controller
{
    public $model = null;

    function __construct()
    {
        require_once('model/model.php');
        $this->model = new Model();
    }

    public function getWeb()
    {
        $command = isset($_GET['command']) ? $_GET['command'] : 'home';

        switch ($command) {
            // ===========================================
            // PUBLIC PAGES
            // ===========================================
            case 'home':
                include_once('view/public/home.php');
                break;
            case 'about':
                include_once('view/public/about.php');
                break;

            // ===========================================
            // USER FUNCTIONS
            // ===========================================
            case 'register':
                include('view/auth/register.php');
                break;

            case 'processRegister':
                $fullname = $_POST['fullname'] ?? '';
                $email = $_POST['email'] ?? '';
                $contactNumber = $_POST['contactNumber'] ?? '';
                $username = $_POST['username'] ?? '';
                $password = $_POST['password'] ?? '';
                $confirm = $_POST['confirm'] ?? '';
                $zone = $_POST['zone'] ?? '';
                $brgyIDNum = $_POST['brgyIDNum'] ?? '';
                $terms = isset($_POST['terms']) ? true : false;
                $error = '';

                if (empty($fullname) || empty($email) || empty($username) || empty($password) || empty($confirm) || empty($contactNumber) || empty($zone) || empty($brgyIDNum)) {
                    $error = "Please fill out all the required fields.";
                } elseif ($password !== $confirm) {
                    $error = "Passwords do not match.";
                } elseif (!$terms) {
                    $error = "You must agree to the Terms and Conditions and Privacy Policy to register.";
                } elseif ($this->model->userExists($username) || $this->model->pendingUserExists($username)) {
                    $error = "Username already exists.";
                } else {
                    $success = $this->model->registerUser($fullname, $email, $zone, $brgyIDNum, $contactNumber, $username, $password);

                    if (isset($success) && $success) {
                        echo "<script>alert('Registration submitted for approval. You will be notified once approved.'); window.location.href='?command=login';</script>";
                    } else {
                        $error = "Registration failed. Please try again.";
                    }
                }

                if ($error) {
                    include('view/auth/register.php');
                }
                break;

            case 'login':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $username = $_POST['username'] ?? '';
                    $password = $_POST['password'] ?? '';
                    $loginRole = strtolower($_POST['loginRole'] ?? '');
                    $error = '';
                    $notice = '';

                    if ($this->model->userExists($username)) {
                        $user = $this->model->loginUser($username, $password, $loginRole);
                        if ($user) {
                            if ($user['role'] === $loginRole) {
                                $_SESSION['user'] = $user;
                                $_SESSION['userID'] = $user['userID'];
                                $_SESSION['username'] = $user['username'];

                                $this->model->setCurrentUser($user['userID'], $user['username']);

                                if ($loginRole === 'admin') {
                                    header('Location: ?command=adminDashboard');
                                } elseif ($loginRole === 'super admin') {
                                    header('Location: ?command=adminDashboard');
                                } else {
                                    header('Location: ?command=dashboard');
                                }
                                exit;
                            } else {
                                $error = "Selected role doesn't match your account.";
                            }
                        } else {
                            $error = "Invalid username or password.";
                        }
                    } else {
                        if ($this->model->isRegistrationPending($username)) {
                            $notice = "Your registration is still being processed. Please wait for admin approval.";
                        } elseif ($this->model->isRegistrationRejected($username)) {
                            $notice = "Your registration has been rejected. Please contact the administrator for more information.";
                        } else {
                            $error = "User does not exist.";
                        }
                    }
                }
                include_once('view/auth/login.php');
                break;

            case 'forgotPassword':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $username = $_POST['username'] ?? '';
                    $email = $_POST['email'] ?? '';
                    $newPassword = $_POST['newPassword'] ?? '';
                    $confirmPassword = $_POST['confirmPassword'] ?? '';
                    $token = $_POST['token'] ?? '';
                    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
                    $error = '';
                    $success = '';
            
                    if (empty($newPassword)) {
                        if (empty($username) || empty($email)) {
                            $error = "Please fill out all the required fields.";
                        } elseif (empty($recaptchaResponse)) {
                            $error = "Please complete the reCAPTCHA verification.";
                        } elseif (!$this->model->verifyRecaptcha($recaptchaResponse)) {
                            $error = "reCAPTCHA verification failed. Please try again.";
                        } else {
                            $user = $this->model->verifyUserIdentity($username, $email);
                            if ($user) {
                                $token = $this->model->generatePasswordResetToken($user['userID']);
                                if ($token) {
                                    header("Location: ?command=forgotPassword&verified=1&userID=" . $user['userID'] . "&token=" . $token);
                                    exit;
                                } else {
                                    $error = "Failed to generate reset token. Please try again.";
                                }
                            } else {
                                $error = "Invalid username or email address.";
                            }
                        }
                    } else {
                        $userID = $_POST['userID'] ?? '';
                        if (empty($userID) || empty($token) || empty($newPassword) || empty($confirmPassword)) {
                            $error = "Please fill out all the required fields.";
                        } elseif ($newPassword !== $confirmPassword) {
                            $error = "Passwords do not match.";
                        } elseif (strlen($newPassword) < 6) {
                            $error = "Password must be at least 6 characters long.";
                        } else {
                            if ($this->model->verifyPasswordResetToken($userID, $token)) {
                                if ($this->model->updateUserPassword($userID, $newPassword)) {
                                    $success = "Password updated successfully! You can now login with your new password.";
                                    echo "<script>setTimeout(function(){ window.location.href='?command=login'; }, 3000);</script>";
                                } else {
                                    $error = "Failed to update password. Please try again.";
                                }
                            } else {
                                $error = "Invalid or expired reset token.";
                            }
                        }
                    }
            
                    if ($error) {
                        include('view/auth/forgotPassword.php');
                    } elseif ($success) {
                        include('view/auth/forgotPassword.php');
                    }
                } else {
                    include('view/auth/forgotPassword.php');
                }
                break;

            case 'logout':
                $this->model->clearCurrentUser();
                session_unset();
                session_destroy();
                header("Location: ?command=login");
                exit();

            case 'dashboard':
                if (!isset($_SESSION['user'])) {
                    header('Location: ?command=Login');
                    exit();
                }

                $userID = $_SESSION['user']['userID'];
                $user = $this->model->getUserById($userID);
                $wasteHistory = $this->model->getUserWasteHistory($userID);
                $mostContributedWaste = $this->model->getMostContributedWaste();
                $topContributors = $this->model->getTopContributors();

                include_once('view/user/dashboard.php');
                break;

            case 'userProfile':
                if (!isset($_SESSION['user'])) {
                    header('Location: ?command=Login');
                    exit();
                }
                $userID = $_SESSION['user']['userID'];
                $users = $this->model->getUserData($userID);
                $totalCurrentPoints = (float) $this->model->getUserPoints($userID);
                $rewards = $this->model->getAllRewards();

                include_once('view/user/userProfile.php');
                break;

            case 'userSettings':
                if (!isset($_SESSION['user'])) {
                    header('Location: ?command=login');
                    exit();
                }
                $userID = $_SESSION['user']['userID'];
                $users = $this->model->getUserData($userID);

                include_once('view/user/userSettings.php');
                break;

            case 'claim':
                if (!isset($_SESSION['user'])) {
                    header('Location: ?command=Login');
                }
                $userID = $_SESSION['user']['userID'];
                $users = $this->model->getUserPoints($userID);
                $totalCurrentPoints = $this->model->getUserPoints($userID);
                $rewards = $this->model->getAllRewards();
                include_once('view/user/claim.php');
                break;

            case 'contribute':
                include_once('view/user/contribute.php');
                break;

            case 'updateProfileSettings': {
                if (!isset($_SESSION['user']) && !isset($_SESSION['admin'])) {
                    echo "<script>alert('You must be logged in.'); window.location.href='?command=login';</script>";
                    exit();
                }

                $sessionType = isset($_SESSION['user']) ? 'user' : 'admin';
                $userID = $_SESSION[$sessionType]['userID'];
                $role = $_SESSION[$sessionType]['role'];

                $currentUser = $this->model->getUserById($userID);

                $fullName = !empty($_REQUEST['fullname']) ? $_REQUEST['fullname'] : $currentUser['fullName'];
                $email = !empty($_REQUEST['email']) ? $_REQUEST['email'] : $currentUser['email'];
                $contactNumber = !empty($_REQUEST['contactNumber']) ? $_REQUEST['contactNumber'] : $currentUser['contactNumber'];
                $username = !empty($_REQUEST['username']) ? $_REQUEST['username'] : $currentUser['username'];
                $password = $_REQUEST['password'] ?? '';
                $confirmPassword = $_REQUEST['confirmPassword'] ?? '';

                if ($role === 'user') {
                    $zone = !empty($_REQUEST['zone']) ? $_REQUEST['zone'] : $currentUser['zone'];
                    $position = $currentUser['position'];
                    $redirectCommand = 'userSettings';
                } else {
                    $position = !empty($_REQUEST['position']) ? $_REQUEST['position'] : $currentUser['position'];
                    $zone = $currentUser['zone'];
                    $redirectCommand = 'adminSettings';
                }

                if ($password && $password !== $confirmPassword) {
                    echo "<script>alert('Passwords do not match!'); window.location.href='?command=" . $redirectCommand . "';</script>";
                    break;
                }

                $profilePicturePath = $currentUser['profilePicture'];
                $removeProfilePicture = isset($_REQUEST['removeProfilePicture']) && $_REQUEST['removeProfilePicture'] === '1';

                if ($removeProfilePicture) {
                    if ($profilePicturePath && file_exists($profilePicturePath)) {
                        if (unlink($profilePicturePath)) {
                        } else {
                            echo "<script>alert('Failed to delete the image file.'); window.location.href='?command=" . $redirectCommand . "';</script>";
                            break;
                        }
                    }
                    $profilePicturePath = null;
                } elseif (!empty($_FILES["profilePicture"]["name"])) {
                    if ($profilePicturePath && file_exists($profilePicturePath)) {
                        if (unlink($profilePicturePath)) {
                        } else {
                            echo "<script>alert('Failed to delete the image file.'); window.location.href='?command=" . $redirectCommand . "';</script>";
                            break;
                        }
                    }

                    $targetDir = "images/profilePic/";
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0777, true);
                    }
                    $fileName = uniqid() . '_' . basename($_FILES["profilePicture"]["name"]);
                    $newProfilePicturePath = $targetDir . $fileName;
                    $imageFileType = strtolower(pathinfo($newProfilePicturePath, PATHINFO_EXTENSION));
                    $check = getimagesize($_FILES["profilePicture"]["tmp_name"]);

                    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
                    if ($check === false) {
                        echo "<script>alert('File is not an image.'); window.location.href='?command=" . $redirectCommand . "';</script>";
                        break;
                    }
                    if (!in_array($imageFileType, $allowedTypes)) {
                        echo "<script>alert('Only JPG, JPEG, PNG & GIF files are allowed.'); window.location.href='?command=" . $redirectCommand . "';</script>";
                        break;
                    }
                    if (!move_uploaded_file($_FILES["profilePicture"]["tmp_name"], $newProfilePicturePath)) {
                        echo "<script>alert('Failed to upload image.'); window.location.href='?command=" . $redirectCommand . "';</script>";
                        break;
                    }
                    $profilePicturePath = $newProfilePicturePath;
                }

                $hashedPassword = $password ? password_hash($password, PASSWORD_DEFAULT) : null;

                $result = $this->model->updateProfileSettings(
                    $userID,
                    $fullName,
                    $zone,
                    $position,
                    $email,
                    $contactNumber,
                    $username,
                    $hashedPassword,
                    $profilePicturePath
                );

                $_SESSION[$sessionType]['username'] = $username;
                $_SESSION[$sessionType]['fullName'] = $fullName;

                echo "<script>alert('Profile updated successfully.'); window.location.href='?command=" . $redirectCommand . "';</script>";
                break;
            }

            // ===========================================
            // ADMIN FUNCTIONS
            // ===========================================
            case 'adminDashboard':
                $totalPlastic = $this->model->getTotalPlastic();
                $totalCans = $this->model->getTotalCans();
                $totalGlassBottles = $this->model->getTotalBottles();
                $notification = $this->model->getNotifications();

                $totalUsers = count($this->model->getAllUsers());
                $totalRewards = count($this->model->getAllRewards());
                $todayContributions = $this->model->getTotalPlasticByDate(date('Y-m-d')) +
                    $this->model->getTotalCansByDate(date('Y-m-d')) +
                    $this->model->getTotalBottlesByDate(date('Y-m-d'));

                $pendingRegistrations = $this->model->getPendingRegistrationNotifications();
                $pendingRegistrationCount = count($pendingRegistrations);

                $sensorNotificationCount = count($this->model->getNotifications());
                $notificationCount = $sensorNotificationCount + $pendingRegistrationCount;

                $getContZone1 = $this->model->getContZone1();
                $getContZone2 = $this->model->getContZone2();
                $getContZone3 = $this->model->getContZone3();
                $getContZone4 = $this->model->getContZone4();
                $getContZone5 = $this->model->getContZone5();
                $getContZone6 = $this->model->getContZone6();
                $getContZone7 = $this->model->getContZone7();

                include_once('view/admin/adminDashboard.php');
                break;

            case 'adminSettings':
                if (!isset($_SESSION['user'])) {
                    header('Location: ?command=Login');
                    exit();
                }

                $userID = $_SESSION['user']['userID'];
                $admin = $this->model->getUserData($userID);

                $notificationCount = count($this->model->getNotifications());

                include_once('view/admin/adminSettings.php');
                break;

            case 'adminReport':
                $selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
                $userID = $_SESSION['userID'];
                $notificationCount = count($this->model->getNotifications());

                if ($selectedDate && $selectedDate !== date('Y-m-d')) {
                    $totalCans = $this->model->getTotalCansByDate($selectedDate);
                    $totalBottles = $this->model->getTotalBottlesByDate($selectedDate);
                    $totalPlastic = $this->model->getTotalPlasticByDate($selectedDate);
                    $wastePerMaterial = $this->model->getWasteContributionsPerMaterialByDate($selectedDate);
                    $wasteHistory = $this->model->getWasteHistoryByDate($selectedDate);

                    $getContZone1 = $this->model->getZoneContributionByDate('Zone 1', $selectedDate);
                    $getContZone2 = $this->model->getZoneContributionByDate('Zone 2', $selectedDate);
                    $getContZone3 = $this->model->getZoneContributionByDate('Zone 3', $selectedDate);
                    $getContZone4 = $this->model->getZoneContributionByDate('Zone 4', $selectedDate);
                    $getContZone5 = $this->model->getZoneContributionByDate('Zone 5', $selectedDate);
                    $getContZone6 = $this->model->getZoneContributionByDate('Zone 6', $selectedDate);
                    $getContZone7 = $this->model->getZoneContributionByDate('Zone 7', $selectedDate);
                } else {
                    $totalCans = $this->model->getTotalCans();
                    $totalBottles = $this->model->getTotalBottles();
                    $totalPlastic = $this->model->getTotalPlastic();
                    $wastePerMaterial = $this->model->getWasteContributionsPerMaterialThisMonth();
                    $wasteHistory = $this->model->getWasteHistory();

                    $getContZone1 = $this->model->getContZone1();
                    $getContZone2 = $this->model->getContZone2();
                    $getContZone3 = $this->model->getContZone3();
                    $getContZone4 = $this->model->getContZone4();
                    $getContZone5 = $this->model->getContZone5();
                    $getContZone6 = $this->model->getContZone6();
                    $getContZone7 = $this->model->getContZone7();
                }

                $users = $this->model->getTopUsers(7);

                include_once('view/admin/adminReport.php');
                break;

            case 'manageUser':
                $users = $this->model->getAllUsers();
                $admins = $this->model->getAllAdmins();
                $notificationCount = count($this->model->getNotifications());

                include_once('view/admin/manageUser.php');
                break;

            case 'updateUserProfile':
                $userID = $_POST['userID'];
                $fullName = $_POST['fullname'];
                $email = $_POST['email'];
                $zone = $_POST['zone'];
                $contactNumber = $_POST['contactNumber'];
                $username = $_POST['username'];
                $password = $_POST['password'];
                $confirmPassword = $_POST['confirmPassword'];

                if (!empty($password) && $password !== $confirmPassword) {
                    echo "<script>alert('Passwords do not match!'); window.location.href='?command=manageUser';</script>";
                    exit();
                }

                $hashedPassword = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;

                $result = $this->model->updateUserProfile(
                    $userID,
                    $fullName,
                    $zone,
                    $email,
                    $contactNumber,
                    $username,
                    $hashedPassword
                );

                if ($result) {
                    echo "<script>alert('User profile updated successfully.'); window.location.href='?command=manageUser';</script>";
                } else {
                    echo "<script>alert('Failed to update user profile.'); window.location.href='?command=manageUser';</script>";
                }
                break;

            case 'deleteUser':
                $userID = $_REQUEST['userID'];
                $result = $this->model->deleteUser($userID);

                echo "<script>
                    alert('" . $result . "');
                    window.location.href='index.php?command=manageUser';
                 </script>";
                break;

            case 'addAdministrator':
                if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'super admin') {
                    echo "<script>alert('Access denied. Only Super Admin can add an administrator.');
                    window.location.href='?command=manageUser';</script>";
                    exit();
                }

                $fullname = $_POST['fullname'] ?? '';
                $email = $_POST['email'] ?? '';
                $contactNumber = $_POST['contactNumber'] ?? '';
                $username = $_POST['username'] ?? '';
                $password = $_POST['password'] ?? '';
                $confirm = $_POST['confirmPassword'] ?? '';
                $position = $_POST['position'] ?? '';
                $error = '';

                if (empty($fullname) || empty($email) || empty($username) || empty($password) || empty($confirm) || empty($contactNumber) || empty($position)) {
                    $error = "Please fill out all the required fields.";
                } elseif ($password !== $confirm) {
                    $error = "Passwords do not match.";
                } elseif ($this->model->userExists($username)) {
                    $error = "Username already exists.";
                } else {
                    $success = $this->model->addAdministrator($fullname, $email, $position, $contactNumber, $username, $password, 'admin');
                    if ($success) {
                        echo "<script>alert('Administrator added successfully.'); window.location.href='?command=manageUser';</script>";
                        exit();
                    } else {
                        $error = "Failed to add administrator. Please try again.";
                    }
                }

                if ($error) {
                    echo "<script>alert('$error'); window.location.href='?command=manageUser';</script>";
                }
                break;

            case 'rewardInventory':
                if (!isset($_SESSION['user'])) {
                    header('Location: ?command=Login');
                }
                $userID = $_SESSION['user']['userID'];
                $rewards = $this->model->getAllRewards();
                include_once('view/admin/rewardInventory.php');
                break;

            case 'addReward':
                $rewardName = $_POST['rewardName'];
                $availableStock = $_POST['availableStock'];
                $slotNum = $_POST['slotNum'];
                $pointsRequired = $_POST['pointsRequired'];
                $availability = isset($_POST['availability']) ? intval($_POST['availability']) : 1;

                if (empty($rewardName) || empty($pointsRequired) || empty($slotNum) || empty($availableStock)) {
                    echo "<script>alert('Please fill out all required fields.'); window.location.href='?command=rewardInventory';</script>";
                    exit();
                }

                $imagePath = null;

                if (!empty($_FILES['rewardImg']['name'])) {
                    $uploadResult = $this->model->validateAndUploadImage($_FILES["rewardImg"], "images/reward/");

                    if ($uploadResult['success']) {
                        $imagePath = $uploadResult['path'];
                    } else {
                        echo "<script>alert('" . $uploadResult['error'] . "'); window.location.href='?command=rewardInventory';</script>";
                        exit();
                    }
                } else {
                    echo "<script>alert('Please select an image for the reward.'); window.location.href='?command=rewardInventory';</script>";
                    exit();
                }

                $result = $this->model->addReward($rewardName, $pointsRequired, $slotNum, $availableStock, $imagePath, $availability);

                if ($result) {
                    echo "<script>alert('Reward added successfully.'); window.location.href='?command=rewardInventory';</script>";
                } else {
                    echo "<script>alert('Failed to add reward. Please try again.'); window.location.href='?command=rewardInventory';</script>";
                }
                break;

            case 'updateReward':
                $rewardID = $_POST['rewardID'];
                $rewardName = $_POST['rewardName'];
                $availableStock = $_POST['availableStock'];
                $slotNum = $_POST['slotNum'];
                $pointsRequired = $_POST['pointsRequired'];
                $availability = isset($_POST['availability']) ? intval($_POST['availability']) : 1;

                if (empty($rewardName) || empty($pointsRequired) || empty($slotNum) || empty($availableStock)) {
                    echo "<script>alert('Please fill out all required fields.'); window.location.href='?command=rewardInventory';</script>";
                    exit();
                }

                $result = $this->model->updateReward($rewardName, $pointsRequired, $slotNum, $availableStock, $rewardID, "", $availability);

                if ($result) {
                    echo "<script>alert('Reward updated successfully.'); window.location.href='?command=rewardInventory';</script>";
                } else {
                    echo "<script>alert('Failed to update reward. Please try again.'); window.location.href='?command=rewardInventory';</script>";
                }
                break;

            case 'deleteReward':
                $rewardID = $_REQUEST['rewardID'];
                $imagePath = $this->model->getRewardImagePathById($rewardID);
                $result = $this->model->deleteReward($rewardID);

                if ($imagePath && file_exists($imagePath)) {
                    unlink($imagePath);
                }

                echo "<script>
                    alert('" . $result . "');
                    window.location.href='index.php?command=rewardInventory';
                 </script>";
                break;

            default:
                include_once('view/shared/404.php');
                break;
        }
    }
}
?>