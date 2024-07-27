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
?>
