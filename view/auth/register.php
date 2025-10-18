<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Register</title>
  <link rel="stylesheet" href="css/auth/register.css" />
</head>

<body>
  <div class="register-container">
    <!-- ==================== LOGO SECTION ==================== -->
    <div class="logo-side">
      <img src="images/image-toggles/user-registration.png" alt="Logo" class="logo" />
    </div>
    
    <!-- ==================== REGISTRATION FORM ==================== -->
    <div class="form-side">
      <form class="register-card" method="POST" action="?command=processRegister">
        <h3>Create an Account</h3>
        
        <!-- Registration Notice -->
        <div class="registration-notice">
          <p><strong>Note:</strong> Your registration will be reviewed by an administrator. You will be notified once your account is approved.</p>
        </div>
        
        <!-- Error Display -->
        <?php if (!empty($error)): ?>
          <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Personal Information -->
        <label for="fullname">Full Name *</label>
        <input type="text" name="fullname" id="fullname" placeholder="Enter your full name" required minlength="2" maxlength="100">
        
        <label for="email">Email *</label>
        <input type="email" name="email" id="email" placeholder="Enter your email address" required maxlength="100">
        
        <label for="zone">Zone *</label>
        <input type="text" name="zone" id="zone" placeholder="e.g., Zone 1, Zone 2, etc." required minlength="2" maxlength="50">
        
        <label for="brgyIDNum">Brgy ID *</label>
        <input type="text" name="brgyIDNum" id="brgyIDNum" placeholder="Enter your barangay ID number (Ex: 2022-28)" required minlength="5" maxlength="20">
        
        <label for="contactNumber">Contact Number *</label>
        <input type="tel" name="contactNumber" id="contactNumber" placeholder="e.g., 09123456789" required pattern="[0-9]{11}" minlength="11" maxlength="11">
        
        <!-- Account Credentials -->
        <label for="username">Username *</label>
        <input type="text" name="username" id="username" placeholder="Choose a unique username" required minlength="3" maxlength="30" pattern="[a-zA-Z0-9_]+">
        
        <label for="password">Password *</label>
        <div class="password-container">
          <input type="password" name="password" id="password" placeholder="Create a strong password" required minlength="6" maxlength="50">
          <button type="button" class="password-toggle" onclick="togglePassword()" id="passwordToggle">Show</button>
        </div>
        
        <label for="confirm">Confirm Password *</label>
        <div class="password-container">
          <input type="password" name="confirm" id="confirm" placeholder="Re-enter your password" required minlength="6" maxlength="50">
          <button type="button" class="password-toggle" onclick="togglePassword1()" id="confirmPasswordToggle">Show</button>
        </div>
        
        <!-- Terms and Conditions -->
        <div class="terms-container">
          <label class="terms-checkbox">
            <input type="checkbox" name="terms" id="terms" required>
            <span class="checkmark"></span>
            <span>I agree to the <a href="#" onclick="openTermsModal(); return false;">Terms and Conditions</a> and <a href="#" onclick="openTermsModal(); return false;">Privacy Policy</a></span>
          </label>
        </div>
        
        <!-- Submit Button -->
        <button type="submit" class="btn-primary" onclick="validateTerms(event)">Register</button>
        
        <!-- Login Link -->
        <p class="login-link">
          Already have an account? <a href="?command=login">Login here</a>
        </p>
      </form>
    </div>
  </div>

  <!-- ==================== PASSWORD TOGGLE FUNCTIONS ==================== -->
  <script>
    // Toggle main password visibility
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

    // Toggle confirm password visibility
    function togglePassword1() {
      const confirmInput = document.getElementById('confirm');
      const confirmToggle = document.getElementById('confirmPasswordToggle');

      if (confirmInput.type === 'password') {
        confirmInput.type = 'text';
        confirmToggle.textContent = 'Hide';
        confirmToggle.title = 'Hide confirm password';
      } else {
        confirmInput.type = 'password';
        confirmToggle.textContent = 'Show';
        confirmToggle.title = 'Show confirm password';
      }
    }

    // Terms and Privacy Modal Functions

    function closeModal() {
      document.getElementById('termsModal').style.display = 'none';
    }

    // Validate Terms and Passwords Before Submission
    function validateTerms(event) {
      const termsCheckbox = document.getElementById('terms');
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirm').value;
      
      // Check if passwords match
      if (password !== confirmPassword) {
        event.preventDefault();
        alert('Passwords do not match. Please make sure both password fields are identical.');
        
        // Focus on confirm password field
        document.getElementById('confirm').focus();
        return false;
      }
      
      // Check if terms are accepted
      if (!termsCheckbox.checked) {
        event.preventDefault(); // Prevent form submission
        
        // Show alert message
        alert('Please accept the Terms and Conditions and Privacy Policy before registering.');
        
        // Open the terms modal
        openTermsModal();
        
        // Scroll to terms section
        document.querySelector('.terms-container').scrollIntoView({ 
          behavior: 'smooth', 
          block: 'center' 
        });
        
        // Add error class to highlight the terms container
        const termsContainer = document.querySelector('.terms-container');
        termsContainer.classList.add('error');
        
        // Remove error class after 3 seconds
        setTimeout(() => {
          termsContainer.classList.remove('error');
        }, 3000);
        
        return false;
      }
      
      return true;
    }

    // Real-time password confirmation validation
    document.getElementById('confirm').addEventListener('input', function() {
      const password = document.getElementById('password').value;
      const confirmPassword = this.value;
      
      if (confirmPassword && password !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
        this.style.borderColor = '#dc3545';
      } else {
        this.setCustomValidity('');
        this.style.borderColor = '';
      }
    });

    function switchTab(tabName) {
      // Hide all tab contents
      document.querySelectorAll('.tab-content').forEach(content => {
        content.style.display = 'none';
        content.classList.remove('active');
      });
      
      // Remove active class from all tabs
      document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
      });
      
      // Show selected tab content
      const selectedTab = document.getElementById(tabName);
      if (selectedTab) {
        selectedTab.style.display = 'block';
        selectedTab.classList.add('active');
      }
      
      // Add active class to clicked tab
      event.target.classList.add('active');
    }

    // Initialize tabs when modal opens
    function openTermsModal() {
      document.getElementById('termsModal').style.display = 'block';
      
      // Ensure first tab is active by default
      document.querySelectorAll('.tab-content').forEach(content => {
        content.style.display = 'none';
        content.classList.remove('active');
      });
      
      document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
      });
      
      // Activate first tab
      const firstTab = document.getElementById('termsContent');
      const firstButton = document.querySelector('.tab-button');
      
      if (firstTab && firstButton) {
        firstTab.style.display = 'block';
        firstTab.classList.add('active');
        firstButton.classList.add('active');
      }
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
      const termsModal = document.getElementById('termsModal');
      if (event.target === termsModal) {
        termsModal.style.display = 'none';
      }
    }
  </script>

  <!-- Terms and Privacy Modal -->
  <div id="termsModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Terms & Privacy</h2>
        <span class="close" onclick="closeModal()">&times;</span>
      </div>
      
      <!-- Tab Navigation -->
      <div class="tab-navigation">
        <button class="tab-button active" onclick="switchTab('termsContent')">Terms & Conditions</button>
        <button class="tab-button" onclick="switchTab('privacyContent')">Privacy Policy</button>
      </div>
      
      <!-- Terms and Conditions Tab Content -->
      <div id="termsContent" class="tab-content active">
        <div class="modal-body">
          <h3>1. Acceptance of Terms</h3>
          <p>By registering for B.A.S.U.R.A. Rewards, you agree to be bound by these Terms and Conditions.</p>
          
          <h3>2. User Responsibilities</h3>
          <p>Users are responsible for:</p>
          <ul>
            <li>Providing accurate and complete information during registration</li>
            <li>Maintaining the confidentiality of their account credentials</li>
            <li>Using the system in accordance with local laws and regulations</li>
            <li>Properly disposing of recyclable materials as intended</li>
          </ul>
          
          <h3>3. Prohibited Activities</h3>
          <p>The following activities are strictly prohibited:</p>
          <ul>
            <li>Providing false information or fraudulent materials</li>
            <li>Attempting to manipulate the reward system</li>
            <li>Sharing account credentials with others</li>
            <li>Using the system for illegal purposes</li>
          </ul>
          
          <h3>4. Reward System</h3>
          <p>Rewards are earned based on verified contributions and are subject to availability. The system reserves the right to modify reward criteria at any time.</p>
          
          <h3>5. Account Termination</h3>
          <p>We reserve the right to suspend or terminate accounts that violate these terms or engage in fraudulent activities.</p>
        </div>
      </div>
      
      <!-- Privacy Policy Tab Content -->
      <div id="privacyContent" class="tab-content" style="display: none;">
        <div class="modal-body">
          <h3>1. Information We Collect</h3>
          <p>We collect the following information:</p>
          <ul>
            <li>Personal information (name, email, contact details)</li>
            <li>Contribution data (materials deposited, points earned)</li>
            <li>Account activity and usage patterns</li>
            <li>Profile pictures and preferences</li>
          </ul>
          
          <h3>2. How We Use Your Information</h3>
          <p>Your information is used to:</p>
          <ul>
            <li>Process and verify your contributions</li>
            <li>Calculate and award points</li>
            <li>Provide customer support</li>
            <li>Improve our services</li>
            <li>Send important notifications</li>
          </ul>
          
          <h3>3. Information Sharing</h3>
          <p>We do not sell, trade, or share your personal information with third parties except as required by law or with your explicit consent.</p>
          
          <h3>4. Data Security</h3>
          <p>We implement appropriate security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction.</p>
          
          <h3>5. Your Rights</h3>
          <p>You have the right to:</p>
          <ul>
            <li>Access your personal information</li>
            <li>Request corrections to inaccurate data</li>
            <li>Request deletion of your account</li>
            <li>Opt-out of non-essential communications</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</body>
</html>