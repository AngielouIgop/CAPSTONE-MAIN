<?php
$conn = new mysqli("localhost", "root", "", "capstone");
$result = $conn->query("SELECT userID, username FROM `current_user` LIMIT 1");
if ($row = $result->fetch_assoc()) {
    echo json_encode([
        'userID' => $row['userID'],
        'username' => $row['username']
    ]);
} else {
    echo json_encode(['error' => 'No user logged in']);
}
$conn->close();
?>