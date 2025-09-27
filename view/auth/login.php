<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login</title>
  <link rel="stylesheet" href="css/login.css" />
</head>

<body>
  <div class="login-container">
    <!-- ==================== LOGO SECTION ==================== -->
    <div class="logo-side">
      <img src="images/basura logo.png" alt="Logo" class="logo" />
      <h1>B.A.S.U.R.A. Rewards</h1>
      <p id="loginType">Official Admin Login</p>
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
          <option value="user">User</option>
          <option value="admin">Admin</option>
          <option value="super admin">Super Admin</option>
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
        
        <!-- Registration Link -->
        <p class="register-link">
          Don't have an account yet?<br>
        </p>
        <a href="?command=register" class="btn-secondary">Register</a>
      </form>
    </div>
  </div>

  <!-- ==================== JAVASCRIPT FUNCTIONS ==================== -->
  <script>
    // Update login type display based on role selection
    function setLoginType(role) {
      const loginType = document.getElementById('loginType');
      loginType.textContent = role === 'user' ? 'Official User Login' : 'Official Admin Login';
    }

    // Handle role selection change
    function toggleLoginType() {
      const select = document.getElementById('loginRole');
      const role = select.value;
      localStorage.setItem('loginRole', role);
      setLoginType(role);
    }

    // Initialize page with saved role preference
    document.addEventListener('DOMContentLoaded', () => {
      const select = document.getElementById('loginRole');
      const role = localStorage.getItem('loginRole');

        if (role && ['user', 'admin', 'super admin'].includes(role)) {
        select.value = role;
      }
      setLoginType(select.value);
    });

    // Toggle password visibility
    function togglePassword() {
      const passwordInput = document.getElementById('password');
      const passwordToggle = document.getElementById('passwordToggle');

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