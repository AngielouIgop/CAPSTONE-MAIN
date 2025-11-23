<?php include 'notification.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reports</title>
  <link rel="stylesheet" href="css/admin/adminReport.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
  <div class="report-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h1 class="report-title" style="margin: 0;">Reports</h1>
      <div style="display: flex; align-items: center; gap: 10px;">
        <label style="font-weight: bold;">📅 Download for Month:</label>
        <select id="download-month" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
          <option value="">Select Month</option>
          <?php
          $currentMonth = date('Y-m');
          for ($i = 11; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $monthName = date('F Y', strtotime($month));
            $selected = ($month === $currentMonth) ? 'selected' : '';
            echo "<option value='$month' $selected>$monthName</option>";
          }
          ?>
        </select>
        <button id="download-all-data" class="download-btn" style="padding: 10px 20px; background: #2196F3; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: bold;">📥 Download CSV & Chart</button>
      </div>
    </div>

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
        <div class="no-contributions-message" id="chartNoData" style="display: none;">
          <div class="message-content">
            <h3>No Contributions Found</h3>
            <p>There are no contributions recorded for the selected date: <span id="selectedDateDisplay"></span></p>
          </div>
        </div>
        <canvas id="contributionChart"></canvas>
      </div>

      <!-- ==================== LEADING ZONES TABLE ==================== -->
      <div class="leading-zones">
        <h3>Total Contributions per Zone</h3>
        <table id="zones-table" class="zones-table">
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
        <table id="contributors-table">
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
          const contributionChart = new Chart(ctx, {
            type: 'bar',
            data: {
              labels: labels,
              datasets: [{
                data: dataValues,
                backgroundColor: ['#ffb74d', '#81c784', '#4cafef'], // Blue for bottles, Green for glass, Orange for cans
                borderColor: ['#f57c00', '#388e3c', '#1e88e5'], // Blue for bottles, Green for glass, Orange for cans
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
              // Show loading state
              const chartNoData = document.getElementById('chartNoData');
              const chartCanvas = document.getElementById('contributionChart');
              if (chartNoData) chartNoData.style.display = 'none';
              if (chartCanvas) chartCanvas.style.display = '';

              // Redirect to the same page with date parameter
              const url = new URL(window.location);
              url.searchParams.set('date', selectedDate);
              window.location.href = url.toString();
            }
          });

          function checkContributionsForDate() {
            const totalContributions = <?= ($totalPlastic + $totalCans + $totalBottles) ?>;
            const chartNoData = document.getElementById('chartNoData');
            const chartCanvas = document.getElementById('contributionChart');
            const selectedDateDisplay = document.getElementById('selectedDateDisplay');

            if (totalContributions === 0) {
              if (chartNoData) chartNoData.style.display = 'block';
              if (selectedDateDisplay) selectedDateDisplay.textContent = '<?= date('F d, Y', strtotime($selectedDate)) ?>';
              if (chartCanvas) chartCanvas.style.display = 'none';
            } else {
              if (chartNoData) chartNoData.style.display = 'none';
              if (chartCanvas) chartCanvas.style.display = '';
            }
          }

          // Run once on load
          checkContributionsForDate();


          // Also apply filter when Enter key is pressed in date input
          dateFilter.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
              applyFilterBtn.click();
            }
          });

        
          function downloadMonthlyChartImage(reportData) {
            const totalPlastic = Number(reportData.totalPlastic) || 0;
            const totalCans = Number(reportData.totalCans) || 0;
            const totalBottles = Number(reportData.totalBottles) || 0;
            const hasValues = [totalPlastic, totalCans, totalBottles].some(value => value > 0);

            if (!hasValues) {
              alert('No contribution data available to generate a chart for this month.');
              return;
            }

            const hiddenCanvas = document.createElement('canvas');
            hiddenCanvas.width = 1280;
            hiddenCanvas.height = 720;
            hiddenCanvas.style.position = 'fixed';
            hiddenCanvas.style.left = '-9999px';
            document.body.appendChild(hiddenCanvas);

            const chartConfig = {
              type: 'bar',
              data: {
                labels: ['Plastic Bottles', 'Cans', 'Glass Bottles'],
                datasets: [{
                  data: [totalPlastic, totalCans, totalBottles],
                  backgroundColor: ['#ffb74d', '#81c784', '#4cafef'],
                  borderColor: ['#f57c00', '#388e3c', '#1e88e5'],
                  borderWidth: 1
                }]
              },
              options: {
                responsive: false,
                animation: false,
                plugins: {
                  title: {
                    display: true,
                    text: `Monthly Contributions – ${reportData.monthName}`,
                    font: {
                      family: "'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
                      size: 28,
                      weight: 'bold'
                    },
                    padding: {
                      top: 20,
                      bottom: 20
                    }
                  },
                  legend: {
                    display: false
                  }
                },
                scales: {
                  y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                  }
                }
              }
            };

            const hiddenCtx = hiddenCanvas.getContext('2d');
            const exportChart = new Chart(hiddenCtx, chartConfig);
            exportChart.update();

            requestAnimationFrame(() => {
              const chartImage = hiddenCanvas.toDataURL('image/png', 1.0);
              const downloadLink = document.createElement('a');
              downloadLink.href = chartImage;
              downloadLink.download = `admin_report_${reportData.month}_chart.png`;
              downloadLink.click();

              exportChart.destroy();
              hiddenCanvas.remove();
            });
          }

          function downloadAllData() {
            const selectedMonth = document.getElementById('download-month').value;
            
            if (!selectedMonth) {
              alert('Please select a month to download data.');
              return;
            }

            // Show loading state
            const downloadBtn = document.getElementById('download-all-data');
            const originalText = downloadBtn.textContent;
            downloadBtn.textContent = 'Loading...';
            downloadBtn.disabled = true;

            // Fetch data for the selected month
            fetch(`endpoints/getMonthlyReportData.php?month=${selectedMonth}`)
              .then(res => res.json())
              .then(data => {
                if (data.error) {
                  alert('Error: ' + data.error);
                  downloadBtn.textContent = originalText;
                  downloadBtn.disabled = false;
                  return;
                }

                // Build CSV content
                let csvContent = '';
                csvContent += 'MONTHLY REPORT\n';
                csvContent += `Month: ${data.monthName}\n`;
                csvContent += `Total Plastic: ${data.totalPlastic}\n`;
                csvContent += `Total Cans: ${data.totalCans}\n`;
                csvContent += `Total Bottles: ${data.totalBottles}\n\n`;

                // Zone Contributions
                csvContent += 'TOTAL CONTRIBUTIONS PER ZONE\n';
                csvContent += 'Zone,Total Contributions\n';
                data.zones.forEach(zone => {
                  csvContent += `"${zone.zone}","${zone.total}"\n`;
                });
                csvContent += '\n\n';

                // Top Contributors
                csvContent += 'TOP CONTRIBUTORS\n';
                csvContent += 'Fullname,Zone,Total Contributed,Total Points\n';
                data.contributors.forEach(contributor => {
                  csvContent += `"${contributor.fullName}","${contributor.zone}","${contributor.contributed}","${contributor.points}"\n`;
                });

                // Download CSV file
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);
                
                link.setAttribute('href', url);
                link.setAttribute('download', `admin_report_${selectedMonth}.csv`);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                try {
                  downloadMonthlyChartImage(data);
                } catch (chartError) {
                  console.error('Chart export failed:', chartError);
                  alert('CSV downloaded, but the chart could not be generated. Please try again.');
                }

                downloadBtn.disabled = false;
                downloadBtn.textContent = originalText;
              })
              .catch(err => {
                console.error('Error:', err);
                alert('Failed to download data. Please try again.');
                downloadBtn.textContent = originalText;
                downloadBtn.disabled = false;
              });
          }

          // Download all data button
          document.getElementById('download-all-data').addEventListener('click', downloadAllData);
        });
      </script>
</body>

</html>