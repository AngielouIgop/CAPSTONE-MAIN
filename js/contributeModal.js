// Global contribution modal functions
function openContributeModal() {
    console.log('Opening contribute modal...');
    
    // Set session to indicate contribution has started
    fetch('endpoints/setContributionStatus.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=start'
    })
    .then(response => response.json())
    .then(data => {
        console.log('Contribution status response:', data);
        if (data.status === 'success') {
            console.log('Contribution started successfully');
            // Show success message or update UI
        } else {
            console.error('Failed to start contribution:', data.message);
            alert('Failed to start contribution: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error starting contribution:', error);
        alert('Error starting contribution. Please try again.');
    });
    
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

function closeContributeModal() {
    console.log('Closing contribute modal...');
    
    // Set session to indicate contribution has stopped
    fetch('endpoints/setContributionStatus.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=stop'
    })
    .then(response => response.json())
    .then(data => {
        console.log('Contribution stop response:', data);
    })
    .catch(error => {
        console.error('Error stopping contribution:', error);
    });
    
    // Close the contribute modal if it exists
    const modal = document.getElementById('contributeModal');
    if (modal) {
        modal.style.display = 'none';
    }
}
