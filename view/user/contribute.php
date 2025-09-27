<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contribute</title>
  <link rel="stylesheet" href="css/contribute.css">
</head>
<body>
  <!-- ==================== CONTRIBUTION MODAL ==================== -->
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
        <div class="modal-actions">
          <button class="modal-btn done-btn" onclick="submitWaste()">Done</button>
          <button class="modal-btn cancel-btn" onclick="closeModal()">Cancel</button>
        </div>
      </div>
    </div>
  </div>
  
  <!-- ==================== THANK YOU MODAL ==================== -->
  <div id="thankYouModal" class="modal-overlay" style="display:none;">
    <div class="modal-content">
      <div class="modal-header">
        <img src="images/basura logo.png" alt="Basura Logo" class="modal-logo" />
        <span class="modal-title">Thank You!</span>
      </div>
      <div class="modal-body" style="text-align:center;">
        <p class="modal-instruction">Thank you for Contributing</p>
        <button class="modal-btn done-btn" onclick="closeThankYouModal()">Close</button>
      </div>
    </div>
  </div>

  <!-- ==================== JAVASCRIPT FUNCTIONS ==================== -->
  <script>
    // ==================== MODAL CONTROL FUNCTIONS ====================
    function openThankYouModal() {
      const modal = document.getElementById('thankYouModal');
      modal.style.display = 'flex';
    }

    function closeThankYouModal() {
      document.getElementById('thankYouModal').style.display = 'none';
    }

    // ==================== CONTRIBUTION FUNCTIONS ====================
    const userID = <?php echo json_encode($_SESSION['user']['userID']); ?>;

    function openContributeModal() {
      // Set session to indicate contribution has started
      fetch('endpoints/setContributionStatus.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=start'
      });

      document.getElementById('contributeModal').style.display = 'flex';
      document.getElementById('modalInstruction').textContent = 'Please insert your waste';
      document.getElementById('materialImage').style.display = 'none';
    }

    function closeModal() {
      // Set session to indicate contribution has stopped
      fetch('endpoints/setContributionStatus.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=stop'
      });

      document.getElementById('contributeModal').style.display = 'none';
    }

    // ==================== WASTE SUBMISSION FUNCTION ====================
    async function submitWaste() {
      const materialImg = document.getElementById('materialImage');
      const modalInstruction = document.getElementById('modalInstruction');

      modalInstruction.textContent = 'Finishing...';

      try {
        // Stop contribution status
        await fetch('endpoints/setContributionStatus.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'action=stop'
        });

        // Hide start contributing modal
        document.getElementById('contributeModal').style.display = 'none';

        // Show Thank You modal
        openThankYouModal();
      } catch (error) {
        materialImg.src = 'images/1.png';
        materialImg.style.display = 'block';
        modalInstruction.textContent = 'Error submitting. Please try again.';
        console.error('Error:', error);
      }
    }
  </script>
</body>
</html>