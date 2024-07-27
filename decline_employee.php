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
session_start();

if (!isset($_SESSION['employee_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['employee_id'])) {
    $employee_id = $_POST['employee_id'];

    // Delete from pending_employees
    $sql_delete = "DELETE FROM pending_employees WHERE employee_id = '$employee_id'";
    if ($conn->query($sql_delete) === TRUE) {
        echo "Employee declined successfully";
    } else {
        echo "Error: " . $conn->error;
    }
}

$conn->close();
?>
