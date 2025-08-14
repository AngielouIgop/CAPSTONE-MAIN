<?php
session_start();
echo "<title>B.A.S.U.R.A. Rewards</title>";

echo "<div>";
    include_once("view/header.php");
echo "</div>";

echo "<div>";
    include_once("controller/controller.php");
    $controller = new Controller;
    $controller->getWeb();
echo "</div>";

echo "<div>";
    include_once("view/footer.php");
echo "</div>";

// if (isset($_SESSION['user'])){
//     if($_SESSION['user']['role'] === 'admin'){
//         include_once("view/adminSidebar.php");
//     } else {
//         include_once("view/sidebar.php");
//     }
// }
?>