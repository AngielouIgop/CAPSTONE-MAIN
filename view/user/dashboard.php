<?php include 'contribute.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard</title>
  <link rel="stylesheet" href="css/user/dashboard.css" />
</head>

<body>
  <?php
    $profileData = $users ?? $user ?? [];
    $profileName = $profileData['fullName'] ?? '';
    $profileZone = $profileData['zone'] ?? '';
  ?>
  <div class="dashboard">
    <h2>Dashboard</h2>

    <!-- ==================== DASHBOARD HEADER ==================== -->
    <div class="dashboard-top">
      <div class="dashboard-header">
        <h2>Welcome <?= htmlspecialchars($profileName ?: ($user['fullName'] ?? 'User')) ?>!</h2>
      </div>
    </div>

    <!-- ==================== PROFILE OVERVIEW ==================== -->
    <div class="profile-header">
      <div class="profile-avatar">
        <?php
          $avatarSrc = 'images/profilePic/default-profile.png';
          $storedPath = $profileData['profilePicture'] ?? '';
          if (!empty($storedPath)) {
            $normalizedPath = str_replace('\\', '/', $storedPath);
            if (file_exists($normalizedPath)) {
              $avatarSrc = $normalizedPath;
            }
          }
        ?>
        <img src="<?= htmlspecialchars($avatarSrc) ?>" alt="Profile Picture">
      </div>
      <div class="profile-details">
        <h2 class="profile-name"><?= htmlspecialchars($profileName) ?></h2>
        <?php if (!empty($profileZone)): ?>
          <p class="profile-zone"><?= htmlspecialchars($profileZone) ?></p>
        <?php endif; ?>
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
            <div class="contributor-icon"> <img src="images/ui-elements/cont-icon.png" alt="cont-img"></div>
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
            'plastic bottles' => 'images/waste-types/plasticBottle.png',
            'plastic bottle' => 'images/waste-types/plasticBottle.png',
            'glass bottles' => 'images/waste-types/glassBottle.png',
            'glass bottle' => 'images/waste-types/glassBottle.png',
            'can' => 'images/waste-types/tinCan.png',
            'cans' => 'images/waste-types/tinCan.png',
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

   <!-- ==================== REWARDS SECTION ==================== -->
   <div class="rewards-section">
      <div class="points-row">
        <span class="points-label">Current Points:</span>
        <span class="points-value"><?= number_format($totalCurrentPoints ?? 0, 2) ?></span>
      </div>

      <div class="rewards-inner">
        <p class="rewards-title"><strong>Available Rewards:</strong></p>
        <div class="rewards-list">
          <?php
            $hasAvailable = false;
            if (!empty($rewards)):
              foreach ($rewards as $reward):
                if (($reward['availability'] ?? 0) == 1 && in_array($reward['slotNum'], [1,2,3])):
                  $hasAvailable = true;
                  if (!empty($reward['rewardImg'])) {
                    if (file_exists($reward['rewardImg'])) {
                      $rewardSrc = $reward['rewardImg'];
                    } else {
                      $imgData = base64_encode($reward['rewardImg']);
                      $rewardSrc = 'data:image/jpeg;base64,' . $imgData;
                    }
                  } else {
                    $rewardSrc = 'images/default-reward.png';
                  }
          ?>
            <div class="reward-card">
              <img src="<?= htmlspecialchars($rewardSrc) ?>" alt="<?= htmlspecialchars($reward['rewardName']) ?>">
              <div class="reward-name"><?= htmlspecialchars($reward['rewardName']) ?></div>
              <div class="reward-points"><?= htmlspecialchars($reward['pointsRequired']) ?> points</div>
              <?php $canClaim = ($totalCurrentPoints ?? 0) >= $reward['pointsRequired']; ?>
              <button class="claim-btn <?= $canClaim ? 'available' : 'insufficient' ?>"
                      <?= $canClaim ? "onclick=\"openClaimModal('".htmlspecialchars($rewardSrc)."', '".htmlspecialchars($reward['rewardName'])."', ".$reward['pointsRequired'].", ".$reward['rewardID'].", ".$reward['slotNum'].")\"" : 'disabled' ?>>
                <?= $canClaim ? 'Claim' : 'Insufficient Points' ?>
              </button>
            </div>
          <?php
                endif;
              endforeach;
            endif;
            if (!$hasAvailable):
          ?>
            <p class="no-rewards">No rewards available at the moment.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

  <!-- ==================== CLAIM CONFIRMATION MODAL ==================== -->
  <div id="claimModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <img src="images/logos/basura logo.png" alt="Basura Logo" class="modal-logo" />
        <span class="modal-title">B.A.S.U.R.A. Rewards</span>
      </div>
      <div class="modal-body">
        <p class="modal-instruction">Are you sure you want to claim this reward?</p>
        <div class="reward-preview">
          <img id="modalRewardImage" src="" alt="Reward Image">
          <div class="reward-details">
            <div class="current-points">
              <span class="points-label">CURRENT POINTS:</span>
              <span class="points-value"><?= htmlspecialchars($totalCurrentPoints ?? 0) ?> PTS</span>
            </div>
          </div>
        </div>
        <div class="modal-actions">
          <button class="modal-btn btn-confirm" id="confirmClaim">Yes</button>
          <button class="modal-btn btn-cancel" id="cancelClaim">Cancel</button>
        </div>
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
  <script src="js/claimModal.js"></script>
</body>
</html>