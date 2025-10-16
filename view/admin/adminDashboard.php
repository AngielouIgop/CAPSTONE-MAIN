<?php include 'notification.php'; ?>

<!-- ==================== ADMIN DASHBOARD STYLES & SCRIPTS ==================== -->
<link rel="stylesheet" href="css/adminDashboard.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="dashboard">
  <!-- ==================== DASHBOARD HEADER ==================== -->
  <div class="dashboard-header">
    <h1>Admin Dashboard</h1>
    <p class="welcome-text">Welcome back, Admin!</p>
  </div>

  <!-- ==================== QUICK STATISTICS ==================== -->
  <div class="quick-stats">
    <div class="stat-item">
      <div class="stat-icon"><input type="image" src="images/adminIcons/user.png" alt="Total Users"></div>
      <div class="stat-info">
        <span class="stat-number"><?= htmlspecialchars($totalUsers ?? 0) ?></span>
        <span class="stat-label">Total Users</span>
      </div>
    </div>
    <div class="stat-item">
      <div class="stat-icon"><input type="image" src="images/adminIcons/totalContribution.png" alt="Total Contributions"></div>
      <div class="stat-info">
        <span class="stat-number"><?= htmlspecialchars(($totalPlastic + $totalCans + $totalGlassBottles)) ?></span>
        <span class="stat-label">Total Contributions</span>
      </div>
    </div>
    <div class="stat-item">
      <div class="stat-icon"><input type="image" src="images/adminIcons/reward.png" alt="Total Rewards"></div>
      <div class="stat-info">
        <span class="stat-number"><?= htmlspecialchars($totalRewards ?? 0) ?></span>
        <span class="stat-label">Available Rewards</span>
      </div>
    </div>
    <div class="stat-item">
      <div class="stat-icon"><input type="image" src="images/adminIcons/todaysContribution.png" alt="Todays Contributions"></div>
      <div class="stat-info">
        <span class="stat-number"><?= htmlspecialchars($todayContributions ?? 0) ?></span>
        <span class="stat-label">Today's Contributions</span>
      </div>
    </div>
    <div class="stat-item">
      <div class="stat-icon"><input type="image" src="images/adminIcons/pending.png" alt="Total Pending Registrations"></div>
      <div class="stat-info">
        <span class="stat-number"><?= htmlspecialchars($pendingRegistrationCount ?? 0) ?></span>
        <span class="stat-label">Pending Registrations</span>
      </div>
    </div>
  </div>

  <!-- ==================== MAIN DASHBOARD GRID ==================== -->
  <div class="dashboard-grid">
    
    <!-- ==================== WASTE DISTRIBUTION CHART ==================== -->
    <div class="card chart-card">
      <div class="card-header">
        <h3>Waste Distribution</h3>
        <p>Breakdown of contributions by material type</p>
      </div>
      <div class="chart-container">
        <canvas id="wasteDistributionChart"></canvas>
      </div>
    </div>

    <!-- ==================== ZONE PERFORMANCE CHART ==================== -->
    <div class="card chart-card">
      <div class="card-header">
        <h3>Zone Performance</h3>
        <p>Contributions by zone this month</p>
      </div>
      <div class="chart-container">
        <canvas id="zonePerformanceChart"></canvas>
      </div>
    </div>

    <!-- ==================== WASTE TYPE STATISTICS ==================== -->
    <div class="card stat-card plastic-card">
      <div class="card-icon">
        <img src="images/waste-types/plasticBottle.png" alt="Plastic Bottles" />
      </div>
      <div class="card-content">
        <h3>Plastic Bottles</h3>
        <div class="stat-number"><?= htmlspecialchars($totalPlastic) ?></div>
      </div>
    </div>

    <div class="card stat-card cans-card">
      <div class="card-icon">
        <img src="images/waste-types/tinCan.png" alt="Tin Cans" />
      </div>
      <div class="card-content">
        <h3>Tin Cans</h3>
        <div class="stat-number"><?= htmlspecialchars($totalCans) ?></div>
      </div>
    </div>

    <div class="card stat-card glass-card">
      <div class="card-icon">
        <img src="images/waste-types/glassBottle.png" alt="Glass Bottles" />
      </div>
      <div class="card-content">
        <h3>Glass Bottles</h3>
        <div class="stat-number"><?= htmlspecialchars($totalGlassBottles) ?></div>
      </div>
    </div>

    <!-- ==================== ADMIN ACTION CARDS ==================== -->
    <div class="card admin-card user-management">
      <div class="card-icon">👥</div>
      <div class="card-content">
        <h3>User Management</h3>
        <p>Add, edit, or remove users from the system.</p>
        <a href="?command=manageUser" class="btn-primary">Manage Users</a>
      </div>
    </div>

    <div class="card admin-card rewards">
      <div class="card-icon">🏆</div>
      <div class="card-content">
        <h3>Rewards Inventory</h3>
        <p>Track and update reward items available for claiming.</p>
        <a href="?command=rewardInventory" class="btn-primary">View Inventory</a>
      </div>
    </div>

    <div class="card admin-card reports">
      <div class="card-icon">📊</div>
      <div class="card-content">
        <h3>Reports</h3>
        <p>Generate and view activity or reward reports.</p>
        <a href="?command=adminReport" class="btn-primary">View Reports</a>
      </div>
    </div>

  </div>
</div>

<!-- ==================== CHART.JS CONFIGURATION ==================== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ==================== WASTE DISTRIBUTION DONUT CHART ====================
    const wasteCtx = document.getElementById('wasteDistributionChart').getContext('2d');
    new Chart(wasteCtx, {
      type: 'doughnut',
      data: {
        labels: ['Plastic Bottles', 'Tin Cans', 'Glass Bottles'],
        datasets: [{
          data: [<?= $totalPlastic ?>, <?= $totalCans ?>, <?= $totalGlassBottles ?>],
                backgroundColor: ['#4cafef', '#ffb74d', '#81c784'], // Blue for plastic bottles, Orange for cans, Green for glass bottles
                borderColor: ['#1e88e5', '#f57c00', '#388e3c'], // Blue for plastic bottles, Orange for cans, Green for glass bottles
                borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              padding: 20,
              usePointStyle: true
            }
          }
        }
      }
    });
  
    // ==================== ZONE PERFORMANCE BAR CHART ====================
    const zoneCtx = document.getElementById('zonePerformanceChart').getContext('2d');
    new Chart(zoneCtx, {
      type: 'bar',
      data: {
        labels: ['Zone 1', 'Zone 2', 'Zone 3', 'Zone 4', 'Zone 5', 'Zone 6', 'Zone 7'],
        datasets: [{
          label: 'Contributions',
          data: [<?= $getContZone1 ?>, <?= $getContZone2 ?>, <?= $getContZone3 ?>, <?= $getContZone4 ?>, <?= $getContZone5 ?>, <?= $getContZone6 ?>, <?= $getContZone7 ?>],
          backgroundColor: 'rgba(96, 0, 251, 0.8)',
          borderColor: 'rgb(255, 255, 255)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              precision: 0
            }
          }
        },
        plugins: {
          legend: {
            display: false
          }
        }
      }
    });
  });
</script>