<?php include 'header.php'; ?>
<?php include 'contribute.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>User Profile</title>
  <link rel="stylesheet" href="css/userProfile.css">
</head>
<body>
  <div class="user-dashboard">
    <div class="profile-header">
      <div class="profile-avatar">
        <?php
          if (!empty($users['profilePicture'])) {
              // Check if it's a file path or binary data
              if (file_exists($users['profilePicture'])) {
                  $src = $users['profilePicture'];
              } else {
                  // Assume it's binary data
                  $imgData = base64_encode($users['profilePicture']);
                  $src = 'data:image/jpeg;base64,' . $imgData;
              }
          } else {
              $src = 'images/default-profile.jpg';
          }
        ?>
        <img src="<?php echo $src; ?>" alt="Profile Picture">
      </div>
      <div class="profile-details">
        <?php if (isset($users) && $users): ?>
          <h2 class="profile-name"><?php echo htmlspecialchars($users['fullName']); ?></h2>
          <p class="profile-zone"><?php echo htmlspecialchars($users['zone']); ?></p>
        <?php endif; ?>
      </div>
    </div>                 

    <div class="rewards-section">
      <div class="points-row">
        <span class="points-label">Current Points:</span>
        <span class="points-value"><?php echo number_format($totalCurrentPoints, 2); ?></span>
      </div>
      <div class="rewards-inner">
        <p class="rewards-title"><strong>Available Rewards:</strong></p>
        <div class="rewards-list">
          <?php
            $hasAvailable = false;
            if (isset($rewards) && !empty($rewards)):
              foreach ($rewards as $reward):
                if (isset($reward['availability']) && $reward['availability'] == 1): // Only show available rewards
                  $hasAvailable = true;
                  // Image logic: use file path if exists, else base64 if binary, else default
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
              <div class="reward-points"><?php echo htmlspecialchars($reward['pointsRequired']); ?> points</div>
              <button class="claim-btn <?php echo ($totalCurrentPoints >= $reward['pointsRequired']) ? 'available' : 'insufficient'; ?>"
                      <?php echo ($totalCurrentPoints >= $reward['pointsRequired']) ? '' : 'disabled'; ?>>
                <?php echo ($totalCurrentPoints >= $reward['pointsRequired']) ? 'Claim' : 'Insufficient Points'; ?>
              </button>
            </div>
          <?php
                endif;
              endforeach;
              if (!$hasAvailable):
          ?>
            <p>No rewards available at the moment.</p>
          <?php
              endif;
            else:
          ?>
            <p>No rewards available at the moment.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
