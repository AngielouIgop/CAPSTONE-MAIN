<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login</title>
  <link rel="stylesheet" href="css/auth/login.css" />
</head>

<body>
  <div class="login-container">
    <!-- ==================== LOGO SECTION ==================== -->
    <div class="logo-side">
      <img src="images/image-toggles/user-login.png" alt="Logo" class="logo" />
    </div>

    <!-- ==================== LOGIN FORM ==================== -->
    <div class="form-side">
      <form class="login-card" method="POST" action="?command=login">
        <h2>Login</h2>

        <!-- Error/Notice Messages -->
        <?php if (!empty($error)): ?>
          <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($notice)): ?>
          <div class="alert alert-notice"><?php echo htmlspecialchars($notice); ?></div>
        <?php endif; ?>

        <!-- Role Selection -->
        <label for="loginRole">Login As</label>
        <select id="loginRole" name="loginRole" required onchange="toggleLoginType()">
          <option value="user" class="mobile-only">User</option>
          <option value="admin" class="desktop-only">Admin</option>
          <option value="super admin" class="desktop-only">Super Admin</option>
        </select>

        <!-- Username Input -->
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>

        <!-- Password Input with Toggle -->
        <label for="password">Password</label>
        <div class="password-container">
          <input type="password" id="password" name="password" required>
          <button type="button" class="password-toggle" onclick="togglePassword()" id="passwordToggle">Show</button>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn-primary">Login</button>

        <div class="forgot-password">
          <a href="?command=forgotPassword" class="forgot-link">Can't remember your password?</a>
        </div>

        <a href="?command=register" class="btn-secondary">Register</a>
      </form>
    </div>
  </div>

  <!-- ==================== JAVASCRIPT FUNCTIONS ==================== -->
  <script>
    // Update image based on role selection
    function setLoginType(role) {
      const logoImage = document.querySelector('.logo');
      
      if (role === 'user') {
        logoImage.src = 'images/image-toggles/user-login.png';
        logoImage.alt = 'User Login Logo';
      } else if (role === 'admin') {
        logoImage.src = 'images/image-toggles/admin-login.png';
        logoImage.alt = 'Admin Login Logo';
      } else if (role === 'super admin') {
        logoImage.src = 'images/image-toggles/admin-login.png';
        logoImage.alt = 'Super Admin Login Logo';
      }
    }

    // Handle role selection change
    function toggleLoginType() {
      const select = document.getElementById('loginRole');
      const role = select.value;
      localStorage.setItem('loginRole', role);
      setLoginType(role);
    }

    // Function to initialize role selection based on screen size
    function initializeRoleSelection() {
      const select = document.getElementById('loginRole');
      const role = localStorage.getItem('loginRole');
      
      // Check if we're on mobile or desktop
      const isMobile = window.innerWidth <= 900;
      
      if (isMobile) {
        // On mobile, default to 'user' and hide admin options
        select.value = 'user';
        setLoginType('user');
      } else {
        // On desktop, default to 'admin' and hide user option
        if (role && ['admin', 'super admin'].includes(role)) {
          select.value = role;
        } else {
          select.value = 'admin';
        }
        setLoginType(select.value);
      }
    }

    // Initialize page with saved role preference
    document.addEventListener('DOMContentLoaded', initializeRoleSelection);
    
    // Handle window resize to update role selection
    window.addEventListener('resize', initializeRoleSelection);

    // Toggle password visibility
    function togglePassword() {
      const passwordInput = document.getElementById('password');
      const passwordToggle = document.getElementById('passwordToggle');

      if (!passwordInput || !passwordToggle) {
        console.error('Password toggle elements not found');
        return;
      }

      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        passwordToggle.textContent = 'Hide';
        passwordToggle.title = 'Hide password';
      } else {
        passwordInput.type = 'password';
        passwordToggle.textContent = 'Show';
        passwordToggle.title = 'Show password';
      }
    }
  </script>
</body>

</html>