// ========================================
// CONTRIBUTE MODAL - ORGANIZED BY FUNCTIONS
// ========================================

// ==================== CONTRIBUTION STATUS FUNCTIONS ====================

/**
 * Set the contribution status (start or stop)
 * @param {string} action - 'start' or 'stop'
 * @returns {Promise} Promise that resolves with the response data
 */
function setContributionStatus(action) {
  return fetch('endpoints/setContributionStatus.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=${action}`
  })
  .then(response => response.json())
  .then(data => {
    console.log(`Contribution ${action} response:`, data);
    if (data.status === 'success') {
      console.log(`Contribution ${action}ed successfully`);
    } else {
      console.error(`Failed to ${action} contribution:`, data.message);
      alert(`Failed to ${action} contribution: ` + data.message);
    }
    return data;
  })
  .catch(error => {
    console.error(`Error ${action}ing contribution:`, error);
    alert(`Error ${action}ing contribution. Please try again.`);
    throw error;
  });
}

// ==================== MODAL FUNCTIONS ====================

/**
 * Open the contribute modal and start contribution
 */
function openContributeModal() {
  console.log('Opening contribute modal...');
  
  // Set session to indicate contribution has started
  setContributionStatus('start');
  
  // Open the contribute modal if it exists
  const modal = document.getElementById('contributeModal');
  if (modal) {
    modal.style.display = 'flex';
    const instruction = document.getElementById('modalInstruction');
    if (instruction) {
      instruction.textContent = 'Please insert your waste';
    }
    const materialImg = document.getElementById('materialImage');
    if (materialImg) {
      materialImg.style.display = 'none';
    }
  } else {
    console.log('Contribute modal not found, contribution started in background');
  }
}

/**
 * Close the contribute modal and stop contribution
 */
function closeContributeModal() {
  console.log('Closing contribute modal...');
  
  // Set session to indicate contribution has stopped
  setContributionStatus('stop');
  
  // Close the contribute modal if it exists
  const modal = document.getElementById('contributeModal');
  if (modal) {
    modal.style.display = 'none';
  }
}
