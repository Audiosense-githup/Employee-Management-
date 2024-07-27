<?php
$servername = "audiosenseems.com";
$username = "SoftTeam";
$password = "Softthings@123";
$dbname = "employee_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the current timestamp minus 24 hours
$threshold_time = date('Y-m-d H:i:s', strtotime('-24 hours'));

// Delete notifications older than 24 hours
$sql = "DELETE FROM notifications WHERE created_at < '$threshold_time'";

if ($conn->query($sql) === TRUE) {
    echo "Old notifications deleted successfully.";
} else {
    echo "Error deleting notifications: " . $conn->error;
}

$conn->close();
?>
