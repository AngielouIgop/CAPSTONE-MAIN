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