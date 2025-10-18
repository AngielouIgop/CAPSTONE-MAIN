<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Header</title>
  <link rel="stylesheet" href="css/shared/headerFooter.css">
</head>
<body>
<header class="header-main" data-role="<?php echo isset($_SESSION['user']) ? $_SESSION['user']['role'] : 'guest'; ?>">

  <!-- ==================== LOGO SECTION ==================== -->
  <?php if (!isset($_SESSION['user'])): ?>
    <div class="header-logo">
      <a href="?command=home" class="logo-link">
        <img src="images/logos/basura logo.png" alt="Basura Logo" class="logo-img"/>
      </a>
      <p class="header-title"></p>
    </div>
  <?php endif; ?>

  <!-- ==================== NAVIGATION MENUS ==================== -->
  <?php if (!isset($_SESSION['user'])): ?>
    <!-- Guest Navigation -->
    <nav class="header-nav" id="header-nav">
      <ul>
        <li><a href="?command=home">Home</a></li>
        <li><a href="?command=login">Log in</a></li>
        <li><a href="?command=register">Register</a></li>
      </ul>
    </nav>
    <!-- ==================== MOBILE MENU TOGGLE ==================== -->
    <button class="menu-toggle" id="menu-toggle">&#9776;</button>

  <?php elseif ($_SESSION['user']['role'] === 'user'): ?>
    <!-- User Navigation -->
    <nav class="header-nav" id="header-nav">
      <ul>
        <li><a href="#" class="start-contributing-btn" style="background:#ffca28; color:#000;" onclick="openContributeModal(); return false;">Start Contributing</a></li>
      </ul>
    </nav>
    <!-- ==================== MOBILE MENU TOGGLE ==================== -->
    <button class="menu-toggle" id="menu-toggle">&#9776;</button>

  <?php elseif ($_SESSION['user']['role'] === 'admin' || $_SESSION['user']['role'] === 'super admin'): ?>
    <!-- Admin Navigation -->
    <nav class="header-nav" id="header-nav">
      <ul>
        <li>
          <a href="#" class="notifications-btn" onclick="openNotificationModal(); return false;">
            <span class="notification-icon">🔔</span>
            <span class="notification-text">Notifications</span>
            <span class="notification-badge" id="notification-counter"><?= isset($notificationCount) ? $notificationCount : 0 ?></span>
          </a>
        </li>
      </ul>
    </nav>
    <!-- Mobile menu toggle removed for admin users -->
  <?php endif; ?>
</header>
</body>
</html>

<?php if (isset($_SESSION['user']) && ($_SESSION['user']['role'] === 'user')): ?>
<script src="js/toggle.js"></script>
<?php endif; ?>