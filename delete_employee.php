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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_id = $_POST['employee_id'];

    // Delete the employee
    $sql = "DELETE FROM employees WHERE employee_id='$employee_id'";
    if ($conn->query($sql) === TRUE) {
        echo "Employee deleted successfully";
    } else {
        echo "Error deleting employee: " . $conn->error;
    }
}
$conn->close();
header("Location: admin_dashboard.php");
exit;
?>
