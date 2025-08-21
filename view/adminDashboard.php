<?php include 'header.php'; ?>
<?php include 'notification.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="css/adminDashboard.css">
</head>
<body>
 <?php include 'header.php'; ?>
<?php include 'notification.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="css/adminDashboard.css" />
</head>

<body>
  <div class="dashboard">
    <h2>Admin Dashboard</h2>

    <div class="dashboard-top">
      <div class="dashboard-header">
        <h2>Welcome, Admin!</h2>
      </div>
    </div>

    <!-- GRID CONTAINER -->
    <div class="dashboard-grid">

      <!-- Top Left: Plastic -->
      <div class="card stat-card">
        <img src="images/plasticBottle.png" alt="Plastic Bottles" />
        <h3>Total Plastic Contributions</h3>
        <p><?= htmlspecialchars($totalPlastic) ?></p>
      </div>

      <!-- Top Middle: Tin Cans -->
      <div class="card stat-card">
        <img src="images/tincan.png" alt="Tin Cans" />
        <h3>Total Tin Can Contributions</h3>
        <p><?= htmlspecialchars($totalCans) ?></p>
      </div>

      <!-- Top Right: Glass Bottles -->
      <div class="card stat-card">
        <img src="images/glassBottle.png" alt="Glass Bottles" />
        <h3>Total Glass Bottle Contributions</h3>
        <p><?= htmlspecialchars($totalGlassBottles) ?></p>
      </div>

      <!-- Bottom Left: User Management -->
      <div class="card admin-card">
        <h3>User Management</h3>
        <p>Add, edit, or remove users from the system.</p>
        <a href="?command=manageUser" class="btn-primary">Manage Users</a>
      </div>

      <!-- Bottom Middle: Rewards -->
      <div class="card admin-card">
        <h3>Rewards Inventory</h3>
        <p>Track and update reward items available for claiming.</p>
        <a href="?command=rewardInventory" class="btn-primary">View Inventory</a>
      </div>

      <!-- Bottom Right: Reports -->
      <div class="card admin-card">
        <h3>Reports</h3>
        <p>Generate and view activity or reward reports.</p>
        <a href="?command=adminReport" class="btn-primary">View Reports</a>
      </div>

    </div> <!-- ✅ CLOSE dashboard-grid -->
  </div> <!-- ✅ CLOSE dashboard -->

</body>
</html>

