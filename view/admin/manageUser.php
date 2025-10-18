<?php include 'notification.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="css/admin/manageUser.css">
</head>
<body>
    <!-- ==================== ACTION BUTTONS ==================== -->
    <button class="pill-btn add-admin-btn" style="float:right;">Add an Admin</button>
    <button class="pill-btn pending-registrations-btn" style="float:right;" onclick="openPendingModal()">
      <span class="pending-icon">⏳</span>
      <span class="pending-text">Pending Registrations</span>
      <span class="pending-badge" id="pending-counter"><?= isset($pendingRegistrationCount) ? $pendingRegistrationCount : 0 ?></span>
    </button>  
      
    <!-- ==================== USERS TABLE ==================== -->
    <div class="section-header">
        Manage Users
    </div>
    <div class="table-container">
        <table class="custom-table">
            <thead>
                <tr>
                    <th></th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Zone</th>
                    <th>Total Accumulated Points</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>     
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><span class="profile-icon">👤</span></td>
                        <td><?= htmlspecialchars($user['fullName']) ?></td>
                        <td><?= htmlspecialchars($user['contactNumber']) ?></td>
                        <td><?= htmlspecialchars($user['zone']) ?></td>
                        <td><?= htmlspecialchars($user['totalCurrentPoints']) ?> pts</td>
                        <td>
                            <a href="#" class="action-btn edit-btn" data-userid="<?= $user['userID']; ?>"
                                data-fullname="<?= htmlspecialchars($user['fullName']); ?>"
                                data-email="<?= htmlspecialchars($user['email']); ?>"
                                data-contactnumber="<?= htmlspecialchars($user['contactNumber']); ?>"
                                data-zone="<?= htmlspecialchars($user['zone']); ?>"
                                data-username="<?= htmlspecialchars($user['username']); ?>">Edit</a>
                            <a href="index.php?command=deleteUser&userID=<?= $user['userID']; ?>" class="action-btn"
                                onclick="return confirm('Are you sure you want to delete <?= htmlspecialchars($user['username'] ?? $user['contactNumber'] ?? 'this user'); ?>?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ==================== ADMIN TABLE ==================== -->
    <div class="section-header">
        Admin
    </div>
    <div class="table-container">
        <table class="custom-table">
            <thead>
                <tr>
                    <th></th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Position</th>
                    <th>Date Added</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $admin): ?>
                    <tr>
                        <td><span class="profile-icon">👤</span></td>
                        <td><?= htmlspecialchars($admin['fullName']) ?></td>
                        <td><?= htmlspecialchars($admin['contactNumber']) ?></td>
                        <td><?= htmlspecialchars($admin['position']) ?></td>
                        <td><?= htmlspecialchars($admin['registrationDate']) ?></td>
                        <td><a href="index.php?command=deleteUser&userID=<?= $admin['userID']; ?>" class="action-btn"
                        onclick="return confirm('Are you sure you want to delete <?= htmlspecialchars($admin['username'] ?? $admin['contactNumber'] ?? 'this admin'); ?>?')">Delete</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ==================== PENDING REGISTRATIONS MODAL ==================== -->
    <div id="pendingRegistrationsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Pending Registrations</h2>
            </div>
            <div class="modal-body">
                <div id="pendingRegistrationsList">
                    <!-- Pending registrations will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" onclick="closePendingModal()">Close</button>
            </div>
        </div>
    </div>

    <!-- ==================== EDIT USER MODAL ==================== -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit User Details</h2>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form id="editUserForm" action="index.php?command=updateUserProfile" method="POST">
                <input type="hidden" id="edit-userID" name="userID">

                <!-- Personal Information -->
                <label for="edit-fullname">Fullname</label>
                <input type="text" id="edit-fullname" name="fullname" required>

                <label for="edit-email">Email</label>
                <input type="email" id="edit-email" name="email" required>

                <label for="edit-zone">Zone</label>
                <input type="text" id="edit-zone" name="zone" required>

                <label for="edit-contactNumber">Contact Number</label>
                <input type="text" id="edit-contactNumber" name="contactNumber" required>

                <label for="edit-username">Username</label>
                <input type="text" id="edit-username" name="username" required>

                <!-- Password Fields -->
                <label for="edit-password">Password</label>
                <div class="password-container">
                    <input type="password" id="edit-password" name="password" placeholder="Leave blank to keep current password">
                    <button type="button" class="password-toggle" onclick="togglePassword('edit-password', this)">Show</button>
                </div>

                <label for="edit-confirmPassword">Confirm Password</label>
                <div class="password-container">
                    <input type="password" id="edit-confirmPassword" name="confirmPassword" placeholder="Confirm the new password">
                    <button type="button" class="password-toggle" onclick="togglePassword('edit-confirmPassword', this)">Show</button>
                </div>

                <div class="modal-buttons">
                    <button type="submit" class="btn-confirm">Confirm</button>
                    <button type="button" class="btn-cancel" id="cancelBtn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== ADD ADMINISTRATOR MODAL ==================== -->
    <div id="addAdministratorModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add a New Administrator</h2>
            </div>
            <form id="addAdministratorForm" action="index.php?command=addAdministrator" method="POST">
                <input type="hidden" id="add-userID" name="userID">

                <!-- Personal Information -->
                <label for="add-fullname">Full Name</label>
                <input type="text" id="add-fullname" name="fullname" placeholder="Enter full name" required>

                <label for="add-email">Email</label>
                <input type="email" id="add-email" name="email" placeholder="Enter email address" required>

                <label for="add-position">Position</label>
                <input type="text" id="add-position" name="position" placeholder="e.g., Barangay Captain, SK Kagawad 1, Barangay Kagawad 1" required>

                <label for="add-contactNumber">Contact Number</label>
                <input type="text" id="add-contactNumber" name="contactNumber" placeholder="e.g., 09123456789" required>

                <label for="add-username">Username</label>
                <input type="text" id="add-username" name="username" placeholder="Choose a unique username" required>

                <!-- Password Fields -->
                <label for="add-password">Password</label>
                <div class="password-container">
                    <input type="password" id="add-password" name="password" placeholder="Create a strong password">
                    <button type="button" class="password-toggle" onclick="togglePassword('add-password', this)">Show</button>
                </div>

                <label for="add-confirmPassword">Confirm Password</label>
                <div class="password-container">
                    <input type="password" id="add-confirmPassword" name="confirmPassword" placeholder="Re-enter password">
                    <button type="button" class="password-toggle" onclick="togglePassword('add-confirmPassword', this)">Show</button>
                </div>

                <!-- Profile Picture -->
                <label for="add-profilePicture">Profile Picture</label>
                <input type="file" id="add-profilePicture" name="profilePicture" accept="image/*">

                <div class="modal-buttons">
                    <button type="submit" class="btn-confirm">Confirm</button>
                    <button type="button" class="btn-cancel" id="cancelBtn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/manageUser.js"></script>
    
    <!-- ==================== PASSWORD TOGGLE FUNCTION ==================== -->
    <script>
        function togglePassword(inputId, toggleBtn) {
            const input = document.getElementById(inputId);
            if (input.type === "password") {
                input.type = "text";
                toggleBtn.textContent = "Hide";
            } else {
                input.type = "password";
                toggleBtn.textContent = "Show";
            }
        }
    </script>
</html>