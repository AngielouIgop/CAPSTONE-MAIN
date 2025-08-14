<?php
// Connect to the database
$conn = mysqli_connect("localhost", "root", "", "capstone");

// Check if the date filter is set
if (isset($_POST['date'])) {
  $date = $_POST['date'];
} else {
  $date = date('Y-m-d');
}

// Query the database to retrieve the data for the chart
$query = "SELECT * FROM wasteentry WHERE dateDeposited = '$date'";
$result = mysqli_query($conn, $query);

// Initialize an array to store the data for the chart
$data = array();

// Loop through the results and add the data to the array
while ($row = mysqli_fetch_assoc($result)) {
  $data[] = $row['quantity'];
}

// Close the database connection
mysqli_close($conn);

// Return the data for the chart
echo json_encode($data);
?>