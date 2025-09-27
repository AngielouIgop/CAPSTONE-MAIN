<?php include 'contribute.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard</title>
  <link rel="stylesheet" href="css/dashboard.css" />
</head>

<body>
  <div class="dashboard">
    <h2>Dashboard</h2>

    <!-- ==================== DASHBOARD HEADER ==================== -->
    <div class="dashboard-top">
      <div class="dashboard-header">
        <h2>Welcome User <?= htmlspecialchars($user['fullName']) ?>!</h2>
      </div>
    </div>

    <!-- ==================== MAIN DASHBOARD GRID ==================== -->
    <div class="dashboard-grid">
      
      <!-- ==================== WASTE CONTRIBUTION HISTORY ==================== -->
      <div class="card history-card">
        <h3>Your Recent Waste Contributions</h3>
        <?php if (!empty($wasteHistory)): ?>
          <div class="history-table-wrap">
            <table class="history-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Time</th>
                  <th>Material</th>
                  <th>Qty</th>
                  <th>Weight (g)</th>
                  <th>Points</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($wasteHistory as $row): ?>
                  <tr>
                    <td><?= htmlspecialchars($row['dateDeposited']) ?></td>
                    <td><?= htmlspecialchars($row['timeDeposited'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['materialName']) ?></td>
                    <td><?= htmlspecialchars($row['quantity']) ?></td>
                    <td><?= htmlspecialchars($row['materialWeight']) ?></td>
                    <td><?= htmlspecialchars($row['pointsEarned']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="no-data">
            <p>No recent contributions.</p>
            <small>Start contributing to see your history here!</small>
          </div>
        <?php endif; ?>
      </div>

      <!-- ==================== CALENDAR WIDGET ==================== -->
      <div class="card calendar-card">
        <h3 id="calendar-month-year"></h3>
        <div id="calendar"></div>
      </div>

      <!-- ==================== TOP CONTRIBUTORS BY ZONE ==================== -->
      <div class="card contributors-card">
        <h3>Top Contributors Per Zone</h3>
        <div class="contributors-grid">
          <?php
          $zones = ['Zone 1', 'Zone 2', 'Zone 3', 'Zone 4', 'Zone 5', 'Zone 6', 'Zone 7'];
          foreach ($zones as $zone): ?>
            <div class="contributor">
            <div class="contributor-icon"> <img src="images/cont-icon.png" alt="cont-img"></div>
              <?php if (isset($topContributors[$zone])): ?>
                <?= htmlspecialchars($topContributors[$zone]['fullName']) ?><br>
                Points: <?= htmlspecialchars($topContributors[$zone]['totalQuantity']) ?><br>
                <?= htmlspecialchars($zone) ?>
              <?php else: ?>
                No contributors yet<br>
                <?= htmlspecialchars($zone) ?>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- ==================== MOST CONTRIBUTED WASTE ==================== -->
      <div class="card waste-card">
        <h3>Most Contributed Waste</h3>
        <?php if ($mostContributedWaste): ?>
          <?php
          // Map waste types to image files
          $wasteImages = [
            'plastic bottles' => 'images/plasticBottle.png',
            'plastic bottle' => 'images/plasticBottle.png',
            'glass bottles' => 'images/glassBottle.png',
            'glass bottle' => 'images/glassBottle.png',
            'tin cans' => 'images/tinCan.png',
            'tin can' => 'images/tinCan.png',
          ];
          
          $wasteName = strtolower($mostContributedWaste['materialName']);
          $imagePath = $wasteImages[$wasteName] ?? 'images/default-waste.png';
          ?>
          <img src="<?= $imagePath ?>" 
               alt="<?= htmlspecialchars($mostContributedWaste['materialName']) ?>" 
               class="waste-img">
          <p><?= htmlspecialchars($mostContributedWaste['materialName']) ?></p>
          <small>Total: <?= $mostContributedWaste['totalQuantity'] ?></small>
        <?php else: ?>
          <p>No data yet</p>
        <?php endif; ?>
      </div>
    </div> 
  </div> 

  <!-- ==================== CALENDAR JAVASCRIPT ==================== -->
  <script>
    // Calendar Rendering
    const calendar = document.getElementById('calendar');
    const monthYear = document.getElementById('calendar-month-year');
    const months = ["January", "February", "March", "April", "May", "June",
      "July", "August", "September", "October", "November", "December"];

    function renderCalendar() {
      const today = new Date();
      const month = today.getMonth();
      const year = today.getFullYear();
      const currentDay = today.getDate();

      monthYear.textContent = `${months[month]} ${year}`;

      const firstDay = new Date(year, month, 1).getDay();
      const daysInMonth = new Date(year, month + 1, 0).getDate();

      let calendarHTML = "<table><tr>";
      const days = ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"];
      days.forEach(d => calendarHTML += `<th>${d}</th>`);
      calendarHTML += "</tr><tr>";

      for (let i = 0; i < firstDay; i++) {
        calendarHTML += "<td></td>";
      }

      for (let day = 1; day <= daysInMonth; day++) {
        if ((firstDay + day - 1) % 7 === 0 && day !== 1) {
          calendarHTML += "</tr><tr>";
        }
        const className = (day === currentDay) ? "today" : "";
        calendarHTML += `<td class="${className}">${day}</td>`;
      }

      calendarHTML += "</tr></table>";
      calendar.innerHTML = calendarHTML;
    }

    renderCalendar();
  </script>
  <script src="js/contributeModal.js"></script>
</body>
</html>