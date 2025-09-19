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
  <div id="notification-modal" class="modal" style="display:none;">
    <div class="modal-content">
      <div class="modal-header">
        <h4>Notifications</h4>
        <button class="clear-btn">Clear All</button>
        <button class="modal-close-btn">&times;</button>
      </div>
      <div class="modal-body">
        <ul id="notification-list"></ul>
      </div>
    </div>
  </div>


  <!-- Include JS -->
  <script src="js/notifModal.js"></script>

</body>
</html>
