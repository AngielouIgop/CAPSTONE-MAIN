<?php include 'notification.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reports</title>
  <link rel="stylesheet" href="css/adminReport.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
  <div class="report-container">
    <h1 class="report-title">Reports</h1>

    <!-- ==================== SUMMARY CARDS ==================== -->
    <div class="summary-cards">
      <div class="card">
        <img src="images/waste-types/plasticBottle.png" alt="Plastic Bottles">
        <p>Total Plastic</p>
        <h2><?= htmlspecialchars($totalPlastic) ?></h2>
      </div>
      <div class="card">
        <img src="images/waste-types/tincan.png" alt="Tin Cans">
        <p>Total Cans</p>
        <h2><?= htmlspecialchars($totalCans) ?></h2>
      </div>
      <div class="card">
        <img src="images/waste-types/glassBottle.png" alt="Glass Bottles">
        <p>Total Bottles</p>
        <h2><?= htmlspecialchars($totalBottles) ?></h2>
      </div>
      <div class="date-picker">
        <span>📅 Filter by date:</span>
        <input type="date" id="date-filter" value="<?= $selectedDate ?>">
        <button id="apply-filter">Apply</button>
      </div>
    </div>

    <!-- ==================== MAIN REPORTS SECTION ==================== -->
    <div class="main-reports">
      
      <!-- ==================== CONTRIBUTION GRAPH ==================== -->
      <div class="contribution-graph">
        <h3>Contributions per Material (This Month)</h3>
        <canvas id="contributionChart"></canvas>
      </div>

      <!-- ==================== LEADING ZONES TABLE ==================== -->
      <div class="leading-zones">
        <h3>Total Contributions per Zone</h3>
        <table class="zones-table">
          <thead>
            <tr>
              <th>Zone</th>
              <th>Total Contributions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Zone 1</td>
              <td><?= htmlspecialchars($getContZone1) ?></td>
            </tr>
            <tr>
              <td>Zone 2</td>
              <td><?= htmlspecialchars($getContZone2) ?></td>
            </tr>
            <tr>
              <td>Zone 3</td>
              <td><?= htmlspecialchars($getContZone3) ?></td>
            </tr>
            <tr>
              <td>Zone 4</td>
              <td><?= htmlspecialchars($getContZone4) ?></td>
            </tr>
            <tr>
              <td>Zone 5</td>
              <td><?= htmlspecialchars($getContZone5) ?></td>
            </tr>
            <tr>
              <td>Zone 6</td>
              <td><?= htmlspecialchars($getContZone6) ?></td>
            </tr>
            <tr>
              <td>Zone 7</td>
              <td><?= htmlspecialchars($getContZone7) ?></td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- ==================== TOP CONTRIBUTORS TABLE ==================== -->
      <div class="top-contributors">
        <h3>Top Contributed Waste and Contributor</h3>
        <span class="mini-date">📅 <?= date('F d, Y', strtotime($selectedDate)) ?></span>
        <table>
          <thead>
            <tr>
              <th>Fullname</th>
              <th>Zone</th>
              <th>Total Contributed</th>
              <th>Total Current Points</th>
            </tr>
          </thead>
          <tbody>
            <?php
            // Group users by zone and calculate their total contributions
            $usersByZone = [];
            foreach ($users as $user) {
              $zone = $user['zone'];
              if (!isset($usersByZone[$zone])) {
                $usersByZone[$zone] = [];
              }

              // Calculate total contribution for this user
              $totalContribution = 0;
              foreach ($wasteHistory as $entry) {
                if ($entry['fullName'] === $user['fullName']) {
                  $totalContribution += $entry['quantity'];
                }
              }

              $usersByZone[$zone][] = [
                'user' => $user,
                'totalContribution' => $totalContribution
              ];
            }

            // Sort each zone by total contribution (highest first) and take top 7
            $topContributorsPerZone = [];
            foreach ($usersByZone as $zone => $zoneUsers) {
              // Sort by total contribution descending
              usort($zoneUsers, function ($a, $b) {
                return $b['totalContribution'] - $a['totalContribution'];
              });

              // Take only top 7 from this zone
              $topContributorsPerZone = array_merge($topContributorsPerZone, array_slice($zoneUsers, 0, 7));
            }

            // Now display the top contributors
            foreach ($topContributorsPerZone as $contributor):
              $user = $contributor['user'];
              $nameMap = [
                'Plastic' => 'Plastic Bottles',
                'Plastic Bottles' => 'Plastic Bottles',
                'Glass' => 'Glass Bottles',
                'Glass Bottles' => 'Glass Bottles',
                'Cans' => 'Cans',
                'Tin Cans' => 'Cans'
              ];
              $userTotal = ['Plastic Bottles' => 0, 'Glass Bottles' => 0, 'Cans' => 0];
              $totalPoints = 0;
              foreach ($wasteHistory as $entry) {
                if ($entry['fullName'] === $user['fullName']) {
                  $materialKey = $nameMap[$entry['materialName']] ?? null;
                  if ($materialKey) {
                    $userTotal[$materialKey] += $entry['quantity'];
                  }
                  $totalPoints += $entry['pointsEarned'];
                }
              }
              ?>
              <tr>
                <td><?= htmlspecialchars($user['fullName']) ?></td>
                <td><?= htmlspecialchars($user['zone']) ?></td>
                <td>
                  <?php foreach ($userTotal as $type => $count): ?>
                    <?= htmlspecialchars($type) ?>: <?= $count ?><br />
                  <?php endforeach; ?>
                </td>
                <td><?= $totalPoints ?> pts</td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- ==================== CHART.JS CONFIGURATION ==================== -->
      <script>
        document.addEventListener("DOMContentLoaded", function () {
          // ==================== CONTRIBUTION CHART SETUP ====================
          const wastePerMaterial = <?= json_encode($wastePerMaterial) ?>;
          const labels = wastePerMaterial.map(item => item.materialType);
          const dataValues = wastePerMaterial.map(item => item.totalQuantity);

          const ctx = document.getElementById('contributionChart').getContext('2d');
          new Chart(ctx, {
            type: 'bar',
            data: {
              labels: labels,
              datasets: [{
                data: dataValues,
                backgroundColor: ['#4cafef', '#81c784', '#ffb74d'],
                borderColor: ['#1e88e5', '#388e3c', '#f57c00'],
                borderWidth: 1
              }]
            },
            options: {
              responsive: true,
              plugins: {
                legend: {
                  labels: {
                    generateLabels: function (chart) {
                      const data = chart.data;
                      if (data.labels.length && data.datasets.length) {
                        return data.labels.map((label, i) => {
                          const value = data.datasets[0].data[i];
                          return {
                            text: `${label}: ${value}`,
                            fillStyle: data.datasets[0].backgroundColor[i],
                            strokeStyle: data.datasets[0].borderColor[i],
                            lineWidth: 1,
                            hidden: false,
                            index: i
                          };
                        });
                      }
                      return [];
                    }
                  }
                }
              },
              scales: {
                y: {
                  beginAtZero: true,
                  ticks: { precision: 0 }
                }
              }
            }
          });

          // ==================== DATE FILTER FUNCTIONALITY ====================
          const dateFilter = document.getElementById('date-filter');
          const applyFilterBtn = document.getElementById('apply-filter');

          applyFilterBtn.addEventListener('click', function () {
            const selectedDate = dateFilter.value;
            if (selectedDate) {
              // Redirect to the same page with date parameter
              const url = new URL(window.location);
              url.searchParams.set('date', selectedDate);
              window.location.href = url.toString();
            }
          });

          // Also apply filter when Enter key is pressed in date input
          dateFilter.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
              applyFilterBtn.click();
            }
          });
        });
      </script>
</body>
</html>