<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['employee_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

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

// Handle task assignment
$msg = "";
$alertType = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_id = $_POST['employee_id'];
    $task_description = $_POST['task_description'];
    $due_date = $_POST['due_date'];
    
    $sql = "INSERT INTO tasks (employee_id, task_description, due_date, status) VALUES (?, ?, ?, 'pending')";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("iss", $employee_id, $task_description, $due_date);
        if ($stmt->execute()) {
            $task_id = $conn->insert_id;
            $message = "You have been assigned a new task: $task_description";
            $sql_notify = "INSERT INTO notifications (employee_id, message, task_id) VALUES (?, ?, ?)";
            
            if ($stmt_notify = $conn->prepare($sql_notify)) {
                $stmt_notify->bind_param("isi", $employee_id, $message, $task_id);
                if ($stmt_notify->execute()) {
                    $msg = "Task assigned and notification created successfully.";
                    $alertType = "success";
                } else {
                    $msg = "Error creating notification: " . $conn->error;
                    $alertType = "error";
                }
            }
        } else {
            $msg = "Error: " . $conn->error;
            $alertType = "error";
        }
    }
}

// Fetch completed tasks for notifications
$notifications = [];
$sql = "SELECT t.*, e.username FROM tasks t JOIN employees e ON t.employee_id = e.employee_id WHERE t.status = 'completed'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $employee_id = $row['employee_id'];
        $message = "Task completed: " . $row['task_description'];
        
        $sql_notify = "INSERT INTO notifications (employee_id, message) VALUES (?, ?)";
        if ($stmt_notify = $conn->prepare($sql_notify)) {
            $stmt_notify->bind_param("is", $employee_id, $message);
            $stmt_notify->execute();
            
            $notifications[] = $row;
        }
    }
}

// Fetch pending tasks
$pending_tasks = [];
$sql_pending = "SELECT t.*, e.username FROM tasks t JOIN employees e ON t.employee_id = e.employee_id WHERE t.status = 'pending'";
$result_pending = $conn->query($sql_pending);
if ($result_pending->num_rows > 0) {
    while ($row = $result_pending->fetch_assoc()) {
        $pending_tasks[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Task</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.js"></script>
    <style>
        body, html {
            height: 100%;
            margin: 0;
            background: url('bglogin.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #333;
            font-family: Arial, sans-serif;
        }
        .container {
            background: rgba(255, 255, 255, 0.9);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 900px;
            margin: 20px auto;
        }
        .container h2, .container h3 {
            text-align: center;
            margin-bottom: 30px;
        }
        .btn-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .table th, .table td {
            text-align: center;
        }
        .list-group-item {
            display: flex;
            justify-content: space-between;
        }
        .alert {
            margin-top: 20px;
        }
        .highlight {
            font-weight: bold;
            color: #000000;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Assign Task</h2>
    <div class="btn-container">
        <a href="admin_dashboard.php" class="btn btn-primary"><i class="fas fa-home"></i> Home</a>
    </div>

    <?php if ($msg != "") { ?>
        <script>
            Swal.fire({
                icon: '<?php echo $alertType; ?>',
                title: '<?php echo $msg; ?>'
            });
        </script>
    <?php } ?>

    <form method="post" action="">
        <div class="form-group">
            <label for="employee_id">Employee:</label>
            <select class="form-control" id="employee_id" name="employee_id" required>
                <?php
                // Fetch employees
                $conn = new mysqli($servername, $username, $password, $dbname);
                $sql = "SELECT * FROM employees";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . $row['employee_id'] . "'>" . $row['username'] . "</option>";
                    }
                }
                $conn->close();
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="task_description">Task Description:</label>
            <textarea class="form-control" id="task_description" name="task_description" required></textarea>
        </div>
        <div class="form-group">
            <label for="due_date">Due Date:</label>
            <input type="date" class="form-control" id="due_date" name="due_date" required>
        </div>
        <button type="submit" class="btn btn-success">Assign Task</button>
    </form>

    <hr>

    <h3>Pending Tasks</h3>
    <table class="table table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Username</th>
                <th>Task Description</th>
                <th>Due Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pending_tasks as $task) { ?>
                <tr>
                    <td><?php echo $task['username']; ?></td>
                    <td><?php echo $task['task_description']; ?></td>
                    <td><?php echo $task['due_date']; ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <hr>

    <h3>Completed Task Notifications</h3>
    <ul class="list-group">
        <?php foreach ($notifications as $notification) { ?>
            <li class="list-group-item">
                <?php echo "<span class='highlight'>Username: " . $notification['username'] . "</span>  Task completed: " . $notification['task_description']; ?>
            </li>
        <?php } ?>
    </ul>
</div>
</body>
</html>

