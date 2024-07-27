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

// Initialize the variable
$result_pending_employees = null;

// Fetch pending employees
$sql_pending_employees = "SELECT * FROM pending_employees";
if ($result_pending_employees = $conn->query($sql_pending_employees)) {
    // Query executed successfully
} else {
    $message = "Error fetching pending employees: " . $conn->error;
    $alert_type = 'error';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if the necessary POST variables are set
    if (isset($_POST['action']) && isset($_POST['employee_id'])) {
        $employee_id = $_POST['employee_id'];
        $action = $_POST['action'];

        if (empty($employee_id) || empty($action)) {
            $message = 'Employee ID or action not set.';
            $alert_type = 'error';
        } else {
            if ($action === 'approve') {
                $sql = "SELECT * FROM pending_employees WHERE employee_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $employee_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $username = $row['username'];
                    $email = $row['email'];
                    $password = $row['password']; // Use hashed password

                    $sql_insert = "INSERT INTO employees (username, email, password, attendance) VALUES (?, ?, ?, 'Absent')";
                    $stmt_insert = $conn->prepare($sql_insert);
                    $stmt_insert->bind_param("sss", $username, $email, $password);
                    if ($stmt_insert->execute()) {
                        $sql_delete = "DELETE FROM pending_employees WHERE employee_id = ?";
                        $stmt_delete = $conn->prepare($sql_delete);
                        $stmt_delete->bind_param("i", $employee_id);
                        $stmt_delete->execute();
                        $message = 'Employee approved successfully';
                        $alert_type = 'success';
                    } else {
                        $message = "Error: " . $sql_insert . "<br>" . $conn->error;
                        $alert_type = 'error';
                    }
                } else {
                    $message = 'No pending employee found with this ID.';
                    $alert_type = 'error';
                }
                $stmt->close();
            } elseif ($action === 'decline') {
                $sql_delete = "DELETE FROM pending_employees WHERE employee_id = ?";
                $stmt_delete = $conn->prepare($sql_delete);
                $stmt_delete->bind_param("i", $employee_id);
                if ($stmt_delete->execute()) {
                    $message = 'Employee declined successfully';
                    $alert_type = 'success';
                } else {
                    $message = "Error: " . $sql_delete . "<br>" . $conn->error;
                    $alert_type = 'error';
                }
            } else {
                $message = 'Invalid action specified.';
                $alert_type = 'error';
            }
        }
    } else {
        $message = 'Action or employee ID not set in POST request.';
        $alert_type = 'error';
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <style>
        body, html {
            height: 100%;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: url('bglogin.jpg') no-repeat center center fixed;
            background-size: cover;
        }
        .container {
            background: rgba(255, 255, 255, 0.9);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
            margin: 20px auto;
        }
        .container h2, .container h3 {
            text-align: center;
            margin-bottom: 30px;
        }
        .form-group .btn {
            margin-right: 10px;
        }
        .btn-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .table th, .table td {
            text-align: center;
        }
        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Admin Dashboard</h2>
    <div class="form-group btn-container">
        <a href="index.php" class="btn btn-primary"><i class="fas fa-home"></i> Home</a>
        <a href="assignTask.php" class="btn btn-success">Assign Task</a>
    </div>

    <hr>
    <h3>Pending Employee Approvals</h3>
    <table class="table table-striped">
        <thead class="thead-dark">
            <tr>
                
                <th>Username</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result_pending_employees && $result_pending_employees->num_rows > 0) {
                while($row = $result_pending_employees->fetch_assoc()) {
                    echo "<tr>
                           
                            <td>{$row['username']}</td>
                            <td>{$row['email']}</td>
                            <td>
                                <form method='post' action='' class='d-inline'>
                                    <input type='hidden' name='employee_id' value='{$row['employee_id']}'>
                                    <button type='submit' name='action' value='approve' class='btn btn-success'>Approve</button>
                                </form>
                                <form method='post' action='' class='d-inline'>
                                    <input type='hidden' name='employee_id' value='{$row['employee_id']}'>
                                    <button type='submit' name='action' value='decline' class='btn btn-danger'>Decline</button>
                                </form>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No pending employee approvals</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <hr>

    <div class="form-group btn-container">
        <a href="EmployeeList.php" class="btn btn-info">Employee List</a>
        <a href="attendance.php" class="btn btn-info">Employee Attendance</a>
        <a href="workreport.php" class="btn btn-info">Work Report</a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php
        if (isset($message) && isset($alert_type)) {
            echo "Swal.fire('Notification', '$message', '$alert_type');";
        }
        ?>
    });
</script>

</body>
</html>
