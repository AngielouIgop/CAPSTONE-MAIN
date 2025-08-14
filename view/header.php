<link rel="stylesheet" href="css/header-footer.css">
<link rel="stylesheet" href="css/uni-sidebar.css">

<header class="header-main">
  <div class="header-logo">
    <a href="?command=home">
      <img src="images/basura logo.png" alt="Basura Logo" class="logo-img" />
    </a>
    <p class="header-title"></p>
  </div>
  
  <?php if (!isset($_SESSION['user'])): ?>
  <nav class="header-nav">
    <ul>
      <li><a href="?command=home">Home</a></li>
      <li><a href="?command=home#about-us-section">About Us</a></li>
      <li><a href="?command=home#how-it-works">How it works</a></li>
      <li><a href="?command=login">Log in</a></li>
    </ul>
  </nav>
  <?php elseif (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'user'): ?>
  <nav class="header-nav">
    <ul>
      <li>
       <a href="#" class="start-contributing-btn" onclick="openContributeModal(); return false;">Start Contributing</a>
      </li>
    </ul>
  </nav>
  <?php elseif (isset($_SESSION['user']['role']) && ($_SESSION['user']['role'] === 'admin' || $_SESSION['user']['role'] === 'super admin')): ?>
  <nav class="header-nav">
    <ul>
      <li>
       <a href="#" class="notifications-btn" onclick="openNotificationModal(); return false;">Notifications</a>
      </li>
    </ul>
  </nav>
  <?php endif; ?>
</header>


<?php
if (isset($_SESSION['user']) && ($_SESSION['user']['role'] === 'admin' || $_SESSION['user']['role'] === 'super admin')) {
    // Admin sidebar
    ?>
    <div class="sidebar">
        <div>
          <h4 class="sidebar-title">Sidebar</h4>
          <div class="nav-links">
              <!-- <button class="sidebar-toggle">☰</button> -->
                <a href="?command=adminDashboard">Home</a>
                <a href="?command=manageUser">Manage Users</a>
                <a href="?command=rewardInventory">Reward Inventory</a>
                <a href="?command=adminReport">Reports</a>
                <a href="?command=adminProfile">My Profile</a>
                <a href="?command=logout">Logout</a>
            </div>
        </div>
    </div>
    <?php
} elseif (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'user') {
    // Regular user sidebar
    ?>
    <div class="sidebar">
        <div>
          <h4 class="sidebar-title">Sidebar</h4>
          <div class="nav-links">
              <!-- <button class="toggle-sidebar">☰</button> -->
                <a href="?command=dashboard">Home</a>
                <a href="?command=userProfile">Profile</a>
                <a href="?command=userSettings">Settings</a>
                <a href="?command=claim">Claim Rewards</a>
                <a href="?command=logout">Logout</a>
            </div>
        </div>
    </div>

    <?php
}
?>


<!-- <script>
const toggleButton = document.querySelector('.toggle-sidebar');
const sidebar = document.querySelector('.sidebar');
const mainContent = document.querySelector('.main-content');

toggleButton.addEventListener('click', () => {
  sidebar.classList.toggle('sidebar-hidden');
  mainContent.classList.toggle('sidebar-hidden');
});
</script> -->