const modal = document.getElementById('notification-modal');
const notificationList = document.getElementById('notification-list');

// Populate the notification list
function populateNotificationList(notifications) {
  notificationList.innerHTML = '';

  notifications.forEach(n => {
    const li = document.createElement('li');
    li.classList.add('notification-item');
    li.innerHTML = `
      <h5>${n.sensor_name}</h5>
      <p>${n.message}</p>
      <button class="done-single-btn" data-id="${n.id}">Done</button>
      <button class="notif-close-btn">&times;</button>
    `;
    notificationList.appendChild(li);
  });
}

// Event delegation for "Done" and close buttons
notificationList.addEventListener('click', (e) => {
  const target = e.target;

  if (target.classList.contains('done-single-btn')) {
    const notifId = target.dataset.id;
    fetch('endpoints/updateNotification.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `id=${notifId}`
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        target.parentElement.style.opacity = 0.5;
        setTimeout(() => target.parentElement.remove(), 400);
      } else {
        alert('Error: ' + data.message);
      }
    })
    .catch(err => console.error('Error:', err));
  }

  if (target.classList.contains('notif-close-btn')) {
    target.parentElement.remove();
  }
});

// Open modal and fetch unread notifications
function openNotificationModal() {
  fetch('endpoints/getUnreadNotifications.php')
    .then(res => res.json())
    .then(data => {
      populateNotificationList(data);
      modal.style.display = 'block';
    })
    .catch(err => console.error('Error:', err));
}

// Close modal
function closeNotificationModal() {
  modal.style.display = 'none';
}

// Event listeners
document.querySelector('.notifications-btn').addEventListener('click', openNotificationModal);
document.querySelector('.modal-close-btn').addEventListener('click', closeNotificationModal);
window.addEventListener('click', e => {
  if (e.target === modal) closeNotificationModal();
});

// Clear all notifications (just clears UI)
document.querySelector('.clear-btn').addEventListener('click', () => {
  notificationList.innerHTML = '';
});
