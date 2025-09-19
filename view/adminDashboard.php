<?php include 'notification.php'; ?>

<!-- Include admin dashboard CSS -->
<link rel="stylesheet" href="css/adminDashboard.css">
<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="dashboard">
  <div class="dashboard-header">
    <h1>Admin Dashboard</h1>
  <p class="welcome-text">Welcome back, Admin!</p>
  </div>

  <!-- Quick Stats Row -->
  <div class="quick-stats">
    <div class="stat-item">
      <div class="stat-icon">👥</div>
      <div class="stat-info">
        <span class="stat-number"><?= htmlspecialchars($totalUsers ?? 0) ?></span>
        <span class="stat-label">Total Users</span>
      </div>
    </div>
    <div class="stat-item">
      <div class="stat-icon">📊</div>
      <div class="stat-info">
        <span class="stat-number"><?= htmlspecialchars(($totalPlastic + $totalCans + $totalGlassBottles)) ?></span>
        <span class="stat-label">Total Contributions</span>
      </div>
    </div>
    <div class="stat-item">
      <div class="stat-icon">🏆</div>
      <div class="stat-info">
        <span class="stat-number"><?= htmlspecialchars($totalRewards ?? 0) ?></span>
        <span class="stat-label">Available Rewards</span>
      </div>
    </div>
    <div class="stat-item">
      <div class="stat-icon">📈</div>
      <div class="stat-info">
        <span class="stat-number"><?= htmlspecialchars($todayContributions ?? 0) ?></span>
        <span class="stat-label">Today's Contributions</span>
      </div>
    </div>
    <div class="stat-item">
      <div class="stat-icon">⏳</div>
      <div class="stat-info">
        <span class="stat-number"><?= htmlspecialchars($pendingRegistrationCount ?? 0) ?></span>
        <span class="stat-label">Pending Registrations</span>
      </div>
    </div>
  </div>

  <!-- Main Dashboard Grid -->
  <div class="dashboard-grid">
    
    <!-- Waste Distribution Chart -->
    <div class="card chart-card">
      <div class="card-header">
        <h3>Waste Distribution</h3>
        <p>Breakdown of contributions by material type</p>
      </div>
      <div class="chart-container">
        <canvas id="wasteDistributionChart"></canvas>
      </div>
    </div>

    <!-- Zone Performance Chart -->
    <div class="card chart-card">
      <div class="card-header">
        <h3>Zone Performance</h3>
        <p>Contributions by zone this month</p>
      </div>
      <div class="chart-container">
        <canvas id="zonePerformanceChart"></canvas>
      </div>
    </div>

    <!-- Enhanced Statistics Cards -->
    <div class="card stat-card plastic-card">
      <div class="card-icon">
        <img src="images/plasticBottle.png" alt="Plastic Bottles" />
      </div>
      <div class="card-content">
        <h3>Plastic Bottles</h3>
        <div class="stat-number"><?= htmlspecialchars($totalPlastic) ?></div>
      </div>
    </div>

    <div class="card stat-card cans-card">
      <div class="card-icon">
        <img src="images/tinCan.png" alt="Tin Cans" />
      </div>
      <div class="card-content">
        <h3>Tin Cans</h3>
        <div class="stat-number"><?= htmlspecialchars($totalCans) ?></div>
      </div>
    </div>

    <div class="card stat-card glass-card">
      <div class="card-icon">
        <img src="images/glassBottle.png" alt="Glass Bottles" />
      </div>
      <div class="card-content">
        <h3>Glass Bottles</h3>
        <div class="stat-number"><?= htmlspecialchars($totalGlassBottles) ?></div>
      </div>
    </div>

    <!-- Action Cards -->
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Waste Distribution Pie Chart
    const wasteCtx = document.getElementById('wasteDistributionChart').getContext('2d');
    new Chart(wasteCtx, {
      type: 'doughnut',
      data: {
        labels: ['Plastic Bottles', 'Tin Cans', 'Glass Bottles'],
        datasets: [{
          data: [<?= $totalPlastic ?>, <?= $totalCans ?>, <?= $totalGlassBottles ?>],
          backgroundColor: ['#31326F', '#4FB7B3', '#A8FBD3'],
          borderColor: ['#31326F', '#4FB7B3', '#A8FBD3'],
          borderWidth: 2
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
  
    // Zone Performance Bar Chart
    const zoneCtx = document.getElementById('zonePerformanceChart').getContext('2d');
    new Chart(zoneCtx, {
      type: 'bar',
      data: {
        labels: ['Zone 1', 'Zone 2', 'Zone 3', 'Zone 4', 'Zone 5', 'Zone 6', 'Zone 7'],
        datasets: [{
          label: 'Contributions',
          data: [<?= $getContZone1 ?>, <?= $getContZone2 ?>, <?= $getContZone3 ?>, <?= $getContZone4 ?>, <?= $getContZone5 ?>, <?= $getContZone6 ?>, <?= $getContZone7 ?>],
          backgroundColor: 'rgba(81, 0, 255, 0.8)',
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

