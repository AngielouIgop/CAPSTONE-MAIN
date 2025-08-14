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
  <div class="dashboard-container">

    <!-- Summary Section -->
    <section class="summary-section">
      <div class="summary-card">
        <img src="images/plasticBottle.png" alt="Plastic Bottles">
        <p>Plastic Bottles</p>
        <h2><?= htmlspecialchars($userTotalPlastic) ?></h2>
      </div>
      <div class="summary-card">
        <img src="images/tincan.png" alt="Tin Cans">
        <p>Tin Cans</p>
        <h2><?= htmlspecialchars($userTotalCans) ?></h2>
      </div>
      <div class="summary-card">
        <img src="images/glassBottle.png" alt="Glass Bottles">
        <p>Glass Bottles</p>
        <h2><?= htmlspecialchars($userTotalGlass) ?></h2>
      </div>

      <!-- Date Filter -->
      <div class="filter-card">
        <span>📅 Filter by date:</span>
        <input type="date" id="date-filter" value="<?= date('Y-m-d') ?>">
        <button id="apply-filter">Apply</button>
      </div>
    </section>

    <!-- Main Content -->
    <section class="content-section">
      <h2>Dashboard</h2>
      <div class="card-row">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Account Info</h5>
            <p class="card-text">View your personal details and account information.</p>
            <a href="?command=userProfile" class="btn-primary">Go to Profile</a>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Settings</h5>
            <p class="card-text">Update your account settings, preferences, and more.</p>
            <a href="?command=userSettings" class="btn-primary">Go to Settings</a>
          </div>
        </div>

        <!-- Calendar Card -->
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Calendar</h5>
            <div id="calendar-month-year"></div>
            <div id="calendar"></div>
          </div>
        </div>
      </div>
    </section>
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
