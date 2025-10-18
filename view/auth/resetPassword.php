<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/auth/login.css">
</head>
<body>
    <div class="login-container">
        <div class="logo-side">
            <img src="images/logos/basura logo.png" alt="Logo" class="logo">
            <h1>B.A.S.U.R.A. Rewards</h1>
            <p>Reset Your Password</p>
        </div>
        
        <div class="form-side">
            <form class="login-card" method="POST" action="?command=resetPassword">
                <h2>Reset Password</h2>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <input type="hidden" name="userID" value="<?php echo htmlspecialchars($_GET['userID'] ?? ''); ?>">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">
                
                <label for="newPassword">New Password</label>
                <div class="password-container">
                    <input type="password" id="newPassword" name="newPassword" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('newPassword')">Show</button>
                </div>
                
                <label for="confirmPassword">Confirm New Password</label>
                <div class="password-container">
                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword')">Show</button>
                </div>
                
                <button type="submit" class="btn-primary">Reset Password</button>
                
                <p class="register-link">
                    Remember your password?<br>
                </p>
                <a href="?command=login" class="btn-secondary">Back to Login</a>
            </form>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const button = passwordInput.nextElementSibling;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                button.textContent = 'Hide';
            } else {
                passwordInput.type = 'password';
                button.textContent = 'Show';
            }
        }
    </script>
</body>
</html>