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

// Redirect if not an admin
if (!isset($_SESSION['employee_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Reports</title>
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
            color: #fff; /* Set text color to white for readability */
        }
        .table th, .table td {
            padding: 15px;
            text-align: center;
            background-color: transparent; /* Remove background color from table cells */
            color: #fff; /* Set text color to white for readability */
        }
        .btn-info, .btn-primary {
            margin: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center">Work Reports</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Report ID</th>
                <th>Username</th>
                <th>Report File</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Updated SQL query to exclude employee_id
            $sql_work_reports = "
                SELECT wr.report_id, e.username, wr.report_file, wr.created_at
                FROM work_reports wr
                JOIN employees e ON wr.employee_id = e.employee_id
            ";
            $result_work_reports = $conn->query($sql_work_reports);

            if ($result_work_reports && $result_work_reports->num_rows > 0) {
                while($row = $result_work_reports->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['report_id']}</td>
                            <td>{$row['username']}</td>
                            <td>{$row['report_file']}</td>
                            <td>{$row['created_at']}</td>
                            <td>
                                <a href='reportDownload.php?report_id={$row['report_id']}' class='btn btn-info'>View Report</a>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No work reports available</td></tr>";
            }
            ?>
        </tbody>
    </table>
    <a href="admin_dashboard.php" class="btn btn-primary">Back to Dashboard</a>
</div>
</body>
</html>
