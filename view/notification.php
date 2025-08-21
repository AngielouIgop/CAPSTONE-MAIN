<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notifications</title>
  <link rel="stylesheet" href="css/notification.css">
</head>

<body>

  <!-- Custom Modal -->
  <div id="notification-modal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h4>Notifications</h4>
        <button class="clear-btn">Clear All</button>
        <button class="modal-close-btn">&times;</button>
      </div>
      <div class="modal-body">
        <ul id="notification-list"></ul>
        <button  class="done-btn" id="done-btn">Done</button>
      </div>
    </div>
  </div>

  <script>
  const notifications = <?php echo json_encode($notification); ?>;
</script>

<script>
// Elements
const modal = document.getElementById('notification-modal');
const notificationList = document.getElementById('notification-list');

// Fetch notifications (dummy data for now)
function fetchNotifications() {
  return notifications.map(n => ({
    title: n.sensor_name,
    message: n.message
  }));
}


// Populate the notification list
function populateNotificationList(notifications) {
  notificationList.innerHTML = '';

  notifications.forEach(n => {
    const li = document.createElement('li');
    li.classList.add('notification-item');
    li.innerHTML = `
      <h5>${n.sensor_name}</h5>
      <p>${n.message}</p>
      <button class="done-btn" data-id="${n.id}">Done</button>
      <button class="notif-close-btn">&times;</button>
    `;
    notificationList.appendChild(li);
  });

  document.querySelectorAll('.done-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const notifId = btn.dataset.id;
      fetch('updateNotification.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${notifId}`
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          btn.parentElement.remove();
        }
      });
    });
  });
}



// Open modal
function openNotificationModal() {
  populateNotificationList(fetchNotifications());
  modal.style.display = 'block';
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

// Clear all notifications
document.querySelector('.clear-btn').addEventListener('click', () => {
  notificationList.innerHTML = '';
});
</script>
<script>
document.getElementById('done-btn').addEventListener('click', () => {
  fetch('updateNotification.php', {
    method: 'POST'
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert('All notifications marked as read!');
      // Optionally update UI
      document.querySelectorAll('.notification-item').forEach(item => {
        item.style.opacity = 0.5; // Fade out or visually mark as done
      });
    } else {
      alert('Error: ' + data.message);
    }
  })
  .catch(err => console.error('Error:', err));
});
</script>
</body>

</html>