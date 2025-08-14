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
  <div class="content">
    <div class="admin-container">
      <h2>Welcome, Admin!</h2>

      <!-- Summary Cards -->
      <div class="summary-cards">
        <div class="card">
          <img src="images/plasticBottle.png" alt="Plastic Bottles">
          <p>Total Plastic Contributions</p>
          <h2><?= htmlspecialchars($totalPlastic) ?></h2>
        </div>
        <div class="card">
          <img src="images/tincan.png" alt="Tin Cans">
          <p>Total Tin Can Contributions</p>
          <h2><?= htmlspecialchars($totalCans) ?></h2>
        </div>
        <div class="card">
          <img src="images/glassBottle.png" alt="Glass Bottles">
          <p>Total Glass Bottle Contributions</p>
          <h2><?= htmlspecialchars($totalGlassBottles) ?></h2>
        </div>
      </div>

      <p class="lead">Monitor user activity, manage rewards, and configure settings here.</p>

      <!-- Admin Action Cards -->
      <div class="admin-row">
        <div class="admin-card">
          <h5>User Management</h5>
          <p>Add, edit, or remove users from the system.</p>
          <a href="?command=manageUser" class="btn-primary">Manage Users</a>
        </div>
        <div class="admin-card">
          <h5>Rewards Inventory</h5>
          <p>Track and update reward items available for claiming.</p>
          <a href="?command=rewardInventory" class="btn-primary">View Inventory</a>
        </div>
        <div class="admin-card">
          <h5>Reports</h5>
          <p>Generate and view activity or reward reports.</p>
          <a href="?command=adminReport" class="btn-primary">View Reports</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
