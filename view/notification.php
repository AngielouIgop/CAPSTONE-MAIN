<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/notification.css">
    <title>Notifications</title>
</head>
<body>
<!-- Modal container -->
<div id="notification-modal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Notifications</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <!-- Notification list will be populated here -->
        <ul id="notification-list"></ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
</body>
</html>

<script>
// Get the modal container and notification list elements
const modal = document.getElementById('notification-modal');
const notificationList = document.getElementById('notification-list');

// Function to populate the notification list
function populateNotificationList(notifications) {
  notificationList.innerHTML = ''; // Clear the list
  notifications.forEach(notification => {
    const notificationHTML = `
      <li>
        <h5>${notification.title}</h5>
        <p>${notification.message}</p>
      </li>
    `;
    notificationList.innerHTML += notificationHTML;
  });
}

// Function to fetch notifications from the database
function fetchNotifications() {
  // Replace this with your actual database query
  const notifications = [
    { title: 'Notification 1', message: 'This is the first notification' },
    { title: 'Notification 2', message: 'This is the second notification' },
    // Add more notifications here...
  ];
  return notifications;
}

// Function to open the modal and populate the notification list
function openNotificationModal() {
  const notifications = fetchNotifications();
  populateNotificationList(notifications);
  modal.style.display = 'block'; // Show the modal
}

// Add an event listener to open the modal when a button is clicked
document.querySelector('.notifications-btn').addEventListener('click', openNotificationModal);
</script>