// Claim Modal Functionality
let currentRewardId = null;

function openClaimModal(rewardImage, rewardName, pointsRequired, rewardId) {
  const modal = document.getElementById('claimModal');
  const modalImage = document.getElementById('modalRewardImage');
  
  if (!modal) {
    console.error('Claim modal not found!');
    return;
  }
  
  if (!modalImage) {
    console.error('Modal image element not found!');
    return;
  }
  
  // Set the reward image
  modalImage.src = rewardImage;
  modalImage.alt = rewardName;
  
  // Store the reward ID for claiming
  currentRewardId = rewardId;
  
  // Show the modal
  modal.classList.add('show');
  
  // Add click outside to close
  modal.onclick = function(event) {
    if (event.target === modal) {
      closeClaimModal();
    }
  };
}

function closeClaimModal() {
  const modal = document.getElementById('claimModal');
  modal.classList.remove('show');
  currentRewardId = null;
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
  const modal = document.getElementById('claimModal');
  const confirmBtn = document.getElementById('confirmClaim');
  const cancelBtn = document.getElementById('cancelClaim');
  
  if (confirmBtn) {
    confirmBtn.addEventListener('click', function() {
      if (currentRewardId) {
        claimReward(currentRewardId);
      }
    });
  }
  
  if (cancelBtn) {
    cancelBtn.addEventListener('click', closeClaimModal);
  }
});

function claimReward(rewardId) {
  // Show loading state
  const confirmBtn = document.getElementById('confirmClaim');
  const originalText = confirmBtn.textContent;
  confirmBtn.textContent = 'Processing...';
  confirmBtn.disabled = true;
  
  // Send claim request
  fetch('endpoints/claimReward.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: `rewardId=${rewardId}`
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert('Reward claimed successfully!');
      // Close modal
      closeClaimModal();
      // Reload page to update points and available rewards
      window.location.reload();
    } else {
      alert('Error: ' + (data.message || 'Failed to claim reward'));
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('An error occurred while claiming the reward. Please try again.');
  })
  .finally(() => {
    // Reset button state
    confirmBtn.textContent = originalText;
    confirmBtn.disabled = false;
  });
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape') {
    closeClaimModal();
  }
});
