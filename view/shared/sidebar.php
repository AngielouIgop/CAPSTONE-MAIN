<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sidebar</title>
  <link rel="stylesheet" href="css/sidebar.css">
</head>
<body>

<!-- ==================== SIDEBAR TOGGLE BUTTON (MOBILE ONLY) ==================== -->
<?php if (isset($_SESSION['user'])): ?>
<button class="sidebar-toggle-btn" id="sidebar-toggle-btn" title="Toggle Sidebar">
  <span class="toggle-icon">☰</span>
</button>
<?php endif; ?>

<!-- ==================== SIDEBAR NAVIGATION ==================== -->
<div class="sidebar <?php echo isset($_SESSION['user']) ? '' : 'guest-hidden'; ?>" id="sidebar">
  <div>
    <h4 class="sidebar-title">Navigation</h4>
    <div class="nav-links">
      <?php if (isset($_SESSION['user'])): ?>
        <?php if ($_SESSION['user']['role'] === 'admin' || $_SESSION['user']['role'] === 'super admin'): ?>
          <!-- ==================== ADMIN NAVIGATION ==================== -->
          <a href="?command=adminDashboard">Home</a>
          <a href="?command=manageUser">Manage Users</a>
          <a href="?command=rewardInventory">Reward Inventory</a>
          <a href="?command=adminReport">Reports</a>
          <a href="?command=adminSettings">My Profile</a>
        <?php elseif ($_SESSION['user']['role'] === 'user'): ?>
          <!-- ==================== USER NAVIGATION ==================== -->
          <a href="?command=dashboard">Home</a>
          <a href="?command=userProfile">Profile</a>
          <a href="?command=userSettings">Settings</a>
          <a href="?command=claim">Claim Rewards</a>
        <?php endif; ?>
        <!-- ==================== LOGOUT LINK ==================== -->
        <a href="?command=logout">Logout</a>
      <?php else: ?>
        <!-- ==================== GUEST NAVIGATION ==================== -->
        <a href="?command=home">Home</a>
        <a href="?command=home#about-us-section">About Us</a>
        <a href="?command=home#how-it-works">How it works</a>
        <a href="?command=login">Log in</a>
        <a href="?command=register">Register</a>
      <?php endif; ?>
    </div>
  </div>
</div>
<div class="overlay" id="sidebar-overlay"></div>

  <!-- ==================== SIDEBAR TOGGLE SCRIPT ==================== -->
   <?php if (isset($_SESSION['user'])): ?>
   <script src="js/toggle.js"></script>
   <?php endif; ?>
</body>
</html>