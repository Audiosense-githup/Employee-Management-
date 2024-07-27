<?php
session_start();
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

$is_logged_in = isset($_SESSION['employee_id']);
$user_role = $is_logged_in ? $_SESSION['role'] : null;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Employee Management System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: url('bg.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 800px;
            margin-top: 76px;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        .logo img {
            max-width: 200px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #dc3545;
        }
        .header h2 {
            color: #343a40;
        }
        .lead {
            margin-bottom: 30px;
        }
        .btn-group {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        .btn {
            min-width: 150px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="logo text-center mb-4">
        <img src="AS.png" class="img-fluid" alt="Employee Management System Logo">
    </div>
    <div class="header">
        <h1>AUDIO SENSE PRIVATE LIMITED</h1>
        <h2>EMPLOYEE MANAGEMENT SYSTEM</h2>
        <p class="lead">Manage tasks and employees efficiently.</p>
    </div>

    <div class="btn-group">
        <?php if ($is_logged_in): ?>
            <?php if ($user_role === 'admin'): ?>
                <a href="admin_dashboard.php" class="btn btn-primary">
                    <i class="bi bi-speedometer2"></i> Admin Dashboard
                </a>
                <a href="signup.php" class="btn btn-success">
                    <i class="bi bi-plus"></i> Add Employee
                </a>
            <?php else: ?>
                <a href="user_dashboard.php" class="btn btn-primary">
                    <i class="bi bi-person"></i> User Dashboard
                </a>
            <?php endif; ?>
            <a href="logout.php" class="btn btn-danger" id="logoutButton">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        <?php else: ?>
            <a href="login.php" class="btn btn-primary">
                <i class="bi bi-box-arrow-in-right"></i> Login
            </a>
            <a href="signup.php" class="btn btn-success">
                <i class="bi bi-person-plus"></i> Sign Up
            </a>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const logoutButton = document.getElementById('logoutButton');

    if (logoutButton) {
        logoutButton.addEventListener('click', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Are you sure?',
                text: "You want to log out?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, log out!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = logoutButton.href;
                }
            });
        });
    }
});
</script>

</body>
</html>
