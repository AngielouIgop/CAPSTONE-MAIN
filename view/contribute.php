<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contribute</title>
  <link rel="stylesheet" href="css/contribute.css">
</head>

<body>
  <div id="contributeModal" class="modal-overlay" style="display:none;">
    <div class="modal-content">
      <div class="modal-header">
        <img src="images/basura logo.png" alt="Basura Logo" class="modal-logo" />
        <span class="modal-title">B.A.S.U.R.A. Rewards</span>
      </div>
      <div class="modal-body">
        <img id="materialImage" src="images/1.png" alt="Material"
          style="width:80px;height:80px;display:none;margin:0 auto 12px auto;">
        <p class="modal-instruction" id="modalInstruction">Please insert your waste</p>
        <div class="dynamic-progress-bar-bg" style="display:none;">
          <div class="dynamic-progress-bar-fill"></div>
        </div>
        <div class="modal-actions">
          <button class="modal-btn done-btn" onclick="submitWaste()">Done</button>
          <button class="modal-btn cancel-btn" onclick="closeModal()">Cancel</button>
        </div>
      </div>
    </div>
  </div>
  <!-- Thank You Modal -->
  <div id="thankYouModal" class="modal-overlay" style="display:none;">
    <div class="modal-content">
      <div class="modal-header">
        <img src="images/basura logo.png" alt="Basura Logo" class="modal-logo" />
        <span class="modal-title">Thank You!</span>
      </div>
      <div class="modal-body">
        <p class="modal-instruction">Thank you for your contribution!</p>
        <button class="modal-btn done-btn" onclick="closeThankYouModal()">Close</button>
      </div>
    </div>
  </div>
</body>

</html>

<script>
  const userID = <?php echo json_encode($_SESSION['user']['userID']); ?>;

  function openContributeModal() {
    document.getElementById('contributeModal').style.display = 'flex';
    document.getElementById('modalInstruction').textContent = 'Please insert your waste';

    var progressBg = document.querySelector('.dynamic-progress-bar-bg');
    var progressFill = document.querySelector('.dynamic-progress-bar-fill');
    progressBg.style.display = 'flex';
    progressFill.style.width = '0%';
    progressFill.style.animation = 'progressBarMove 10s linear forwards';

    setTimeout(function () {
      progressBg.style.display = 'none';
      progressFill.style.animation = '';
      document.getElementById('modalInstruction').textContent = 'Ready! Please press Done after inserting your waste.';
    }, 10000);
  }

  function closeModal() {
    document.getElementById('contributeModal').style.display = 'none';
  }

  function openThankYouModal() {
  const modal = document.getElementById('thankYouModal');
  modal.style.display = 'flex';
  setTimeout(() => modal.style.display = 'none', 2000);
}

  function closeThankYouModal() {
    document.getElementById('thankYouModal').style.display = 'none';
  }

function submitWaste() {
  const progressBg = document.querySelector('.dynamic-progress-bar-bg');
  const progressFill = document.querySelector('.dynamic-progress-bar-fill');
  const materialImg = document.getElementById('materialImage');

  progressBg.style.display = 'flex';
  progressFill.style.width = '0%';
  progressFill.style.animation = 'progressBarMove 2s linear forwards';

  const data = {
    material: 'Plastic Bottles', // replace with detected material
    sensor_value: 123,
    userID: userID
  };

  fetch('endpoint.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams(data)
  })
    .then(res => res.text())
    .then(result => {
      progressFill.style.width = '100%';
      progressBg.style.display = 'none';
      materialImg.style.display = 'block';

      // Show Thank You modal immediately
      openThankYouModal();

      // Optional: update material image
      if (result.includes('Plastic Bottles')) materialImg.src = 'images/plasticBottle.png';
      else if (result.includes('Glass Bottles')) materialImg.src = 'images/glassBottle.png';
      else if (result.includes('Cans')) materialImg.src = 'images/tinCan.png';
      else materialImg.src = 'images/1.png';

      document.getElementById('modalInstruction').textContent = result + ' Please insert your next waste.';
    })
    .catch(error => {
      progressBg.style.display = 'none';
      materialImg.src = 'images/1.png';
      materialImg.style.display = 'block';
      document.getElementById('modalInstruction').textContent = 'Error: ' + error;
    });
}

</script>