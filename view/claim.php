<?php include 'header.php'; ?>
<?php include 'contribute.php'; ?>

<script>
  var userID = '<?php echo $_SESSION['user']['userID']; ?>';
</script>
<script src="js/contributeModal.js"></script>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="css/claim.css">
  <title>Claim</title>
</head>
<body>

  <div class="rewards-header">
    <span>Available Rewards:</span>
    <span>CURRENT POINTS: <b><?php echo htmlspecialchars($totalCurrentPoints); ?> pts</b></span>
  </div>

  <div class="rewards-list">
    <?php
      $maxCards = 8;
      $rewardCount = 0;
      if (!empty($rewards)) {
        foreach ($rewards as $reward) {
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
      <div class="reward-card">
        <img src="<?php echo htmlspecialchars($src); ?>" alt="<?php echo htmlspecialchars($reward['rewardName']); ?>">
        <div class="reward-name"><?php echo htmlspecialchars($reward['rewardName']); ?></div>
        <div class="reward-points"><?php echo htmlspecialchars($reward['pointsRequired']); ?> pts</div>
        <button class="claim-btn <?php echo ($totalCurrentPoints >= $reward['pointsRequired']) ? 'available' : 'insufficient'; ?>"
          <?php echo ($totalCurrentPoints >= $reward['pointsRequired']) ? '' : 'disabled'; ?>>
          <?php echo ($totalCurrentPoints >= $reward['pointsRequired']) ? 'Claim' : 'Insufficient points'; ?>
        </button>
      </div>
    <?php
        }
      }
      for ($i = $rewardCount; $i < $maxCards; $i++) {
    ?>
      <div class="reward-card coming-soon">
        <img src="images/coming-soon.png" alt="Coming Soon">
        <div class="reward-name">coming soon</div>
      </div>
    <?php } ?>
  </div>

</body>
</html>
