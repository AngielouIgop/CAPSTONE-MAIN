<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sidebar</title>
  <link rel="stylesheet" href="css/uni-sidebar.css">
</head>
<body>
<?php if (isset($_SESSION['user'])): ?>
  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div>
      <h4 class="sidebar-title">Sidebar</h4>
      <div class="nav-links">
        <?php if ($_SESSION['user']['role'] === 'admin' || $_SESSION['user']['role'] === 'super admin'): ?>
          <a href="?command=adminDashboard">Home</a>
          <a href="?command=manageUser">Manage Users</a>
          <a href="?command=rewardInventory">Reward Inventory</a>
          <a href="?command=adminReport">Reports</a>
          <a href="?command=adminProfile">My Profile</a>
        <?php elseif ($_SESSION['user']['role'] === 'user'): ?>
          <a href="?command=dashboard">Home</a>
          <a href="?command=userProfile">Profile</a>
          <a href="?command=userSettings">Settings</a>
          <a href="?command=claim">Claim Rewards</a>
        <?php endif; ?>
        <a href="?command=logout">Logout</a>
      </div>
    </div>
  </div>
  <div class="overlay" id="sidebar-overlay"></div>
<?php endif; ?>

  <!-- Import sidebar toggle script -->
   <script src="js/toggle.js"></script>
</body>
</html>
