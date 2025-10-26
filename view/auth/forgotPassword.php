<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/auth/login.css">
    <!-- Add reCAPTCHA script -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <div class="login-container">
        <div class="logo-side">
            <img src="images/logos/basura logo.png" alt="Logo" class="logo">
            <h1>B.A.S.U.R.A. Rewards</h1>
            <p>Password Recovery</p>
        </div>
        
        <div class="form-side">
            <form class="login-card" method="POST" action="?command=forgotPassword">
                <h2>Reset Your Password</h2>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <?php if (empty($_GET['verified'])): ?>
                    <!-- STEP 1: Verify Identity -->
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                    
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                    
                    
                     <!-- reCAPTCHA with image selection -->
                     <div class="g-recaptcha" 
                         data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>" 
                         data-callback="onRecaptchaSuccess"
                         data-expired-callback="onRecaptchaExpired"
                         data-error-callback="onRecaptchaError">
                    </div>

                    <button type="submit" class="btn-primary">Verify Identity</button>
                    
                <?php else: ?>
                    <!-- STEP 2: Reset Password -->
                    <input type="hidden" name="userID" value="<?php echo htmlspecialchars($_GET['userID']); ?>">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                    
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
                <?php endif; ?>
                
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

        function onRecaptchaSuccess(token) {
            recaptchaCompleted = true;
            document.getElementById('verifyBtn').disabled = false;
            console.log('reCAPTCHA completed successfully');
        }

        function onRecaptchaExpired() {
            recaptchaCompleted = false;
            document.getElementById('verifyBtn').disabled = true;
            console.log('reCAPTCHA expired');
        }

        function onRecaptchaError() {
            recaptchaCompleted = false;
            document.getElementById('verifyBtn').disabled = true;
            console.log('reCAPTCHA error');
        }

        // Form submission validation
        document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
            if (!recaptchaCompleted && !document.querySelector('input[name="userID"]')) {
                e.preventDefault();
                alert('Please complete the reCAPTCHA verification first.');
                return false;
            }
        });
    </script>
</body>
</html>