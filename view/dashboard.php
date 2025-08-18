<?php include 'header.php'; ?>
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

    <div class="dashboard-top">
      <div class="dashboard-header">
        <h2>Welcome User <?= htmlspecialchars($user['fullName']) ?>!</h2>
      </div>
    </div>



    <div class="dashboard-grid">
      <!-- Top Left: Leading Zone Contributors -->
      <div class="card chart-card">
        <h3>Leading Zone Contributors</h3>
        <canvas id="zoneChart"></canvas>
      </div>

      <!-- Top Right: Calendar -->
      <div class="card calendar-card">
        <h3 id="calendar-month-year"></h3> <!-- FIX: Added element for month/year -->
        <div id="calendar"></div>
      </div>
      <!-- Bottom Left: Top Contributors -->
      <div class="card contributors-card">
        <h3>Top Contributors Per Zone</h3>
        <div class="contributors-grid">
          <?php
          $zones = ['Zone 1', 'Zone 2', 'Zone 3', 'Zone 4', 'Zone 5', 'Zone 6', 'Zone 7'];
          foreach ($zones as $zone):
            ?>
            <div class="contributor">
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



      <!-- Bottom Right: Most Contributed Waste -->
<div class="card waste-card">
  <h3>Most Contributed Waste</h3>
  <?php if ($mostContributedWaste): ?>
      <img src="data:image/png;base64,<?= base64_encode($mostContributedWaste['materialImg']) ?>" 
           alt="<?= htmlspecialchars($mostContributedWaste['materialName']) ?>" 
           class="waste-img">
      <p><?= htmlspecialchars($mostContributedWaste['materialName']) ?></p>
      <small>Total: <?= $mostContributedWaste['totalQuantity'] ?></small>
  <?php else: ?>
      <p>No data yet</p>
  <?php endif; ?>
</div>



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
</body>

</html>