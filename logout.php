<?php
date_default_timezone_set('Asia/Kolkata'); // Set the time zone for Salem, Tamil Nadu, India
session_start();

// Update last logout time and set attendance to absent
if (isset($_SESSION['employee_id'])) {
    $servername = "audiosenseems.com";
    $username = "SoftTeam";
    $password = "Softthings@123";
    $dbname = "employee_system";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $employee_id = $_SESSION['employee_id'];
    $current_time = date('Y-m-d H:i:s');
    $sql_update = "UPDATE employees SET last_logout='$current_time', attendance='Absent' WHERE employee_id=$employee_id";
    $conn->query($sql_update);

    $conn->close();
}

// Destroy session and redirect to home page
session_destroy();
header("Location: index.php");
exit;
?>
