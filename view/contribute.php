<link rel="stylesheet" href="css/contribute.css">

<div id="contributeModal" class="modal-overlay" style="display:none;">
  <div class="modal-content">
    <div class="modal-header">
      <img src="images/basura logo.png" alt="Basura Logo" class="modal-logo" />
      <span class="modal-title">B.A.S.U.R.A. Rewards</span>
    </div>
    <div class="modal-body">
      <img id="materialImage" src="images/placeholder.png" alt="Material" style="width:80px;height:80px;display:none;margin:0 auto 12px auto;">
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


<script>
    function openContributeModal() {
  document.getElementById('contributeModal').style.display = 'flex';
  document.getElementById('modalInstruction').textContent = 'Please insert your waste';

  var progressBg = document.querySelector('.dynamic-progress-bar-bg');
  var progressFill = document.querySelector('.dynamic-progress-bar-fill');
  progressBg.style.display = 'flex';
  progressFill.style.width = '0%';
  progressFill.style.animation = 'progressBarMove 10s linear forwards';

  setTimeout(function() {
    progressBg.style.display = 'none';
    progressFill.style.animation = '';
    document.getElementById('modalInstruction').textContent = 'Ready! Please press Done after inserting your waste.';
  }, 10000);
}

function closeModal() {
  document.getElementById('contributeModal').style.display = 'none';
}

function submitWaste() {
  var progressBg = document.querySelector('.dynamic-progress-bar-bg');
  var progressFill = document.querySelector('.dynamic-progress-bar-fill');
  var materialImg = document.getElementById('materialImage');
  progressBg.style.display = 'flex';
  progressFill.style.width = '0%';
  progressFill.style.animation = 'progressBarMove 2s linear forwards';

  var data = {
    material: 'Plastic Bottles', // Replace with actual detected material from ESP32 if available
    sensor_value: 123,
    userID: userID // userID should be defined globally or injected via PHP inline script
  };

  fetch('endpoint.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: new URLSearchParams(data)
  })
  .then(response => response.text())
  .then(result => {
    progressFill.style.width = '100%';
    setTimeout(function() {
      progressBg.style.display = 'none';

      let detectedMaterial = '';
      if (result.indexOf('Plastic Bottles') !== -1) detectedMaterial = 'Plastic Bottles';
      else if (result.indexOf('Glass Bottles') !== -1) detectedMaterial = 'Glass Bottles';
      else if (result.indexOf('Cans') !== -1) detectedMaterial = 'Cans';

      if (detectedMaterial === 'Plastic Bottles') {
        materialImg.src = 'images/plasticBottle.png';
      } else if (detectedMaterial === 'Glass Bottles') {
        materialImg.src = 'images/glassBottle.png';
      } else if (detectedMaterial === 'Cans') {
        materialImg.src = 'images/tinCan.png';
      } else {
        materialImg.src = 'images/placeholder.png';
      }
      materialImg.style.display = 'block';
      document.getElementById('modalInstruction').textContent = result + ' Please insert your next waste.';
    }, 500);
  })
  .catch(error => {
    progressBg.style.display = 'none';
    materialImg.src = 'images/placeholder.png';
    materialImg.style.display = 'block';
    document.getElementById('modalInstruction').textContent = 'Error: ' + error;
  });
}

</script>