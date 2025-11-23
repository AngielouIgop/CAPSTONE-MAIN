// ========================================
// NOTIFICATION MODAL - ORGANIZED BY FUNCTIONS
// ========================================

// ==================== CONSTANTS ====================
const modal = document.getElementById('notification-modal');
const notificationList = document.getElementById('notification-list');

// ==================== NOTIFICATION FUNCTIONS ====================

/**
 * Populate the notification list with notification items
 * @param {Array} notifications - Array of notification objects
 */
function populateNotificationList(notifications) {
  notificationList.innerHTML = '';

  notifications.forEach(n => {
    const li = document.createElement('li');
    li.classList.add('notification-item');
    
    li.innerHTML = `
      <div class="notification-header">
        <h5>${n.sensor_name}</h5>
        <span class="notification-type">Sensor</span>
      </div>
      <p>${n.message}</p>
      <div class="notification-actions">
        <button class="done-single-btn" data-id="${n.id}">Done</button>
        <button class="notif-close-btn">&times;</button>
      </div>
    `;
    
    notificationList.appendChild(li);
  });
}

/**
 * Update the notification counter badge
 */
function updateNotificationCounter() {
  fetch('endpoints/getUnreadNotifications.php')
    .then(res => res.json())
    .then(data => {
      const counter = document.getElementById('notification-counter');
      if (counter) {
        const count = data.length;
        counter.textContent = count;
        
        // Hide badge if no notifications
        if (count === 0) {
          counter.classList.add('hidden');
        } else {
          counter.classList.remove('hidden');
        }
      }
    })
    .catch(err => console.error('Error updating counter:', err));
}

/**
 * Mark a single notification as read
 * @param {number} notifId - The ID of the notification to mark as read
 */
function markNotificationAsRead(notifId) {
  fetch('endpoints/updateNotification.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `id=${notifId}`
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      updateNotificationCounter();
    } else {
      alert('Error: ' + data.message);
    }
  })
  .catch(err => console.error('Error:', err));
}

/**
 * Mark all notifications as read
 */
function markAllNotificationsAsRead() {
  fetch('endpoints/markAllNotificationsAsRead.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      notificationList.innerHTML = '';
      updateNotificationCounter();
    } else {
      alert('Error: ' + data.message);
    }
  })
  .catch(err => {
    console.error('Error:', err);
    alert('Failed to mark notifications as read.');
  });
}

// ==================== MODAL FUNCTIONS ====================

/**
 * Open the notification modal and load unread notifications
 */
function openNotificationModal() {
  fetch('endpoints/getUnreadNotifications.php')
    .then(res => res.json())
    .then(data => {
      populateNotificationList(data);
      modal.style.display = 'block';
      updateNotificationCounter();
    })
    .catch(err => console.error('Error:', err));
}

/**
 * Close the notification modal
 */
function closeNotificationModal() {
  modal.style.display = 'none';
}

// ==================== EVENT LISTENERS ====================

// Notification list click delegation (for Done and Close buttons)
notificationList.addEventListener('click', (e) => {
  const target = e.target;

  if (target.classList.contains('done-single-btn')) {
    const notifId = target.dataset.id;
    target.parentElement.style.opacity = 0.5;
    setTimeout(() => {
      target.parentElement.remove();
      markNotificationAsRead(notifId);
    }, 400);
  }

  if (target.classList.contains('notif-close-btn')) {
    target.parentElement.remove();
  }
});

// Modal open/close event listeners
document.querySelector('.notifications-btn').addEventListener('click', openNotificationModal);
document.querySelector('.modal-close-btn').addEventListener('click', closeNotificationModal);
window.addEventListener('click', e => {
  if (e.target === modal) closeNotificationModal();
});

// Clear all notifications button
document.querySelector('.clear-btn').addEventListener('click', markAllNotificationsAsRead);

// ==================== INITIALIZATION ====================

// Update counter on page load
document.addEventListener('DOMContentLoaded', function() {
  updateNotificationCounter();
});
