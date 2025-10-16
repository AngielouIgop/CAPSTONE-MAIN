<?php include 'notification.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Profile</title>
  <link rel="stylesheet" href="css/adminSettings.css">
  
  <!-- ==================== IMAGE PREVIEW SCRIPT ==================== -->
  <script type="text/javascript">
    function imagePreview(event) {
      if (event.target.files.length > 0) {
        var src = URL.createObjectURL(event.target.files[0]);
        var preview = document.getElementById("previewImage");
        preview.src = src;
        preview.style.display = "block";
      }
    }
  </script>
 
<body>
  <!-- ==================== ADMIN PROFILE UPDATE FORM ==================== -->
  <form class="profile-form" method="post" enctype="multipart/form-data" action="?command=updateProfileSettings">
    <h2 class="profile-form-title">Admin Profile Info</h2>

    <!-- ==================== PROFILE IMAGE SECTION ==================== -->
    <div class="profile-form-top">
      <div class="profile-img-box">
        <?php
        $src = !empty($admin['profilePicture']) && file_exists($admin['profilePicture']) ? $admin['profilePicture'] : 'images/default-profile.jpg';
        ?>
        <img src="<?php echo $src; ?>" alt="Profile Picture" class="profile-img" id="previewImage">
      </div>
      <div class="profile-img-actions">
        <label class="change-picture-btn">
          Change picture
          <input type="file" name="profilePicture" accept="image/*" style="display:none;"
            onchange="imagePreview(event)">
        </label>
      </div>
    </div>

    <!-- ==================== FORM FIELDS ==================== -->
    <div class="profile-form-fields">
      <!-- Personal Information Row -->
      <div class="form-row">
        <div class="form-group">
          <label>Fullname</label>
          <input type="text" name="fullname" value="<?php echo htmlspecialchars($admin['fullName'] ?? ''); ?>">
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>">
        </div>
      </div>

      <!-- Contact Information Row -->
      <div class="form-row">
        <div class="form-group">
          <label>Contact Number</label>
          <input type="text" name="contactNumber" value="<?php echo htmlspecialchars($admin['contactNumber'] ?? ''); ?>">
        </div>
        <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" value="<?php echo htmlspecialchars($admin['username'] ?? ''); ?>">
        </div>
      </div>

      <!-- Password Fields Row -->
      <div class="form-row">
        <div class="form-group">
          <label>Password</label>
          <div class="password-container">
            <input type="password" id="password" name="password" placeholder="Leave blank to keep current password">
            <button type="button" class="password-toggle" onclick="togglePassword('password', this)">Show</button>
          </div>
        </div>
        <div class="form-group">
          <label>Confirm password</label>
          <div class="password-container">
            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm the new password">
            <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword', this)">Show</button>
          </div>
        </div>
      </div>
    </div>

    <!-- ==================== SUBMIT BUTTON ==================== -->
    <button type="submit" class="save-btn">Confirm and Save</button>
  </form>

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
</body>
</html>