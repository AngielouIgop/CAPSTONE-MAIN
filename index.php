<?php
session_start();
echo "<title>B.A.S.U.R.A. Rewards</title>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/uni-sidebar.css">
</head>
<body>

  <!-- Header -->
  <div>
    <?php include_once("view/header.php"); ?>
  </div>

  <!-- Sidebar -->
  <div>
    <?php include_once("view/sidebar.php"); ?>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <?php
      include_once("controller/controller.php");
      $controller = new Controller;
      $controller->getWeb();
    ?>
  </div>

  <!-- Footer -->
  <div>
    <?php include_once("view/footer.php"); ?>
  </div>

<script src="js/toggle.js"></script>

</body>
</html>
