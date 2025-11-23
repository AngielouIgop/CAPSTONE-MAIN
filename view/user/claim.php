<?php include 'contribute.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="css/user/claim.css">
  <title>Claim</title>
</head>
<body>

  <!-- ==================== REWARDS HEADER ==================== -->
  <div class="rewards-header">
    <span>Available Rewards</span>
    <span><b><?php echo htmlspecialchars($totalCurrentPoints); ?> pts</b></span>
  </div>

  <!-- ==================== REWARDS GRID ==================== -->
  <div class="rewards-list">
    <?php
      $maxCards = 8;
      $rewardCount = 0;
      if (!empty($rewards)) {
        foreach ($rewards as $reward) {
          $availability = (int)$reward['availability'];
          $isAvailable = $availability === 1 || $availability === 2;
          $isComingSoon = $availability === 2;

          // Show rewards from all slots (1, 2, 3) that are active (available or coming soon)
          if ($isAvailable && in_array($reward['slotNum'], [1, 2, 3])) {
            $rewardCount++;
            if (!empty($reward['rewardImg'])) {
              if (file_exists($reward['rewardImg'])) {
                $src = $reward['rewardImg'];
              } else {
                $imgData = base64_encode($reward['rewardImg']);
                $src = 'data:image/jpeg;base64,' . $imgData;
              }
            } else {
              $src = 'images/default-reward.png';
            }
    ?>
      <?php
        $cardClasses = 'reward-card';
        if ($isComingSoon) {
          $cardClasses .= ' coming-soon';
        }
        $canClaim = !$isComingSoon && $totalCurrentPoints >= $reward['pointsRequired'];
      ?>
      <div class="<?= $cardClasses ?>">
        <img src="<?php echo htmlspecialchars($src); ?>" alt="<?php echo htmlspecialchars($reward['rewardName']); ?>">
        <div class="reward-name"><?php echo htmlspecialchars($reward['rewardName']); ?></div>
        <div class="reward-points"><?php echo htmlspecialchars($reward['pointsRequired']); ?> pts</div>
        <div class="reward-slot">Slot <?php echo htmlspecialchars($reward['slotNum']); ?></div>
        <?php if ($isComingSoon): ?>
          <div class="coming-soon-label">Coming Soon</div>
        <?php endif; ?>
        <button class="claim-btn <?php echo $canClaim ? 'available' : ($isComingSoon ? 'coming-soon' : 'insufficient'); ?>"
          <?php echo $canClaim ? 'onclick="openClaimModal(\'' . htmlspecialchars($src) . '\', \'' . htmlspecialchars($reward['rewardName']) . '\', ' . $reward['pointsRequired'] . ', ' . $reward['rewardID'] . ', ' . $reward['slotNum'] . ')"' : 'disabled'; ?>>
          <?php
            if ($isComingSoon) {
              echo 'Coming Soon';
            } elseif ($canClaim) {
              echo 'Claim';
            } else {
              echo 'Insufficient points';
            }
          ?>
        </button>
      </div>
    <?php
          }
        }
      }
      // Fill remaining slots with "coming soon" cards
      for ($i = $rewardCount; $i < $maxCards; $i++) {
    ?>
      <div class="reward-card coming-soon">
        <img src="images/ui-elements/coming-soon.png" alt="Coming Soon">
        <div class="reward-name">coming soon</div>
      </div>
    <?php } ?>
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
              <span class="points-value"><?php echo htmlspecialchars($totalCurrentPoints); ?> PTS</span>
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

  <script src="js/claimModal.js"></script>
</body>
</html>