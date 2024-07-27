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

// Fetch all employees with attendance status and login/logout timestamps
$sql_employees = "SELECT username, email, attendance, last_login, last_logout FROM employees";
$result_employees = $conn->query($sql_employees);

// Function to save daily attendance to a CSV file for each employee
function saveDailyAttendanceForEachEmployee($conn) {
    $currentDate = date('Y-m-d'); // e.g., "2024-07-24"
    
    // Create directory if it doesn't exist
    if (!file_exists('daily_attendance_records')) {
        mkdir('daily_attendance_records', 0777, true);
    }
    
    // Fetch all employees to generate individual files
    $sql = "SELECT employee_id, username, email, attendance, last_login, last_logout FROM employees";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $employeeId = $row['employee_id'];
            $filename = "daily_attendance_records/attendance_{$employeeId}.csv";
            
            // Open file in append mode
            $file = fopen($filename, 'a');
            
            // Check if the file is empty to write header row
            if (filesize($filename) == 0) {
                fputcsv($file, ['Date', 'Employee ID', 'Username', 'Email', 'Attendance Status', 'Last Login', 'Last Logout']);
            }
            
            // Write employee daily attendance data
            fputcsv($file, array_merge([$currentDate], array_values($row)));
            
            // Close file
            fclose($file);
        }
    }
}

// Save the daily attendance record for each employee
saveDailyAttendanceForEachEmployee($conn);

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Attendance</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: url('bglogin.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
        }
        .container {
            background-color: rgba(0, 0, 0, 0.6);
            padding: 30px;
            border-radius: 10px;
        }
        .table {
            color: rgba(255, 255, 255, 0.9); /* Adjust text color with transparency */
        }
        .table th, .table td {
            padding: 15px;
            text-align: center;
            background-color: transparent; /* Remove background color from table cells */
        }
        .btn-primary {
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center">Employee Attendance</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Attendance Status</th>
                <th>Last Login</th>
                <th>Last Logout</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result_employees->num_rows > 0) {
                while($row = $result_employees->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['username']}</td>
                            <td>{$row['email']}</td>
                            <td>{$row['attendance']}</td>
                            <td>{$row['last_login']}</td>
                            <td>{$row['last_logout']}</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No employees found</td></tr>";
            }
            ?>
        </tbody>
    </table>
    <div class="form-group">
        <a href="admin_dashboard.php" class="btn btn-primary">Back to Admin Dashboard</a>
    </div>
</div>
</body>
</html>
