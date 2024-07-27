<?php
include 'functions.php';

// Fetch employees
$sql_employees = "SELECT * FROM employees";
$result_employees = $conn->query($sql_employees);

// Handle employee deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_employee_id'])) {
    $employee_id = $_POST['delete_employee_id'];
    $sql_delete = "DELETE FROM employees WHERE employee_id='$employee_id'";
    
    if ($conn->query($sql_delete) === TRUE) {
        $message = "Employee deleted successfully.";
        $alert_type = "success";
    } else {
        $message = "Error deleting employee: " . $conn->error;
        $alert_type = "danger";
    }
    // Do not close connection here
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee List</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
            color: rgba(0, 0, 0, 0.8); /* Adjust text color with opacity */
        }
        .table th, .table td {
            padding: 15px;
            text-align: center;
            background-color: transparent; /* Remove background color from table cells */
            color: rgba(255, 255, 255, 0.7); /* Adjust text color with opacity */
        }
        .btn-info, .btn-danger, .btn-primary {
            margin: 5px;
        }
        .alert {
            margin: 10px 0;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center">Employee List</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result_employees->num_rows > 0) {
                while($row = $result_employees->fetch_assoc()) {
                    $token = generateToken($row['employee_id']);
                    storeToken($token, $row['employee_id']); // Store the token
                    echo "<tr>
                            <td>{$row['username']}</td>
                            <td>{$row['email']}</td>
                            <td>
                                <a href='user_dashboard.php?admin_access=true&employee_id={$row['employee_id']}&token={$token}' class='btn btn-info'>View Dashboard</a>
                                <button class='btn btn-danger delete-btn' data-employee-id='{$row['employee_id']}'>Delete</button>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No employees found</td></tr>";
            }
            ?>
        </tbody>
    </table>
    <a href="admin_dashboard.php" class="btn btn-primary">Back to Dashboard</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-btn');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            const employeeId = this.getAttribute('data-employee-id');
            
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to delete this employee?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create a form element and submit it
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '';
                    
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'delete_employee_id';
                    input.value = employeeId;
                    
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });

    <?php if (isset($message) && isset($alert_type)): ?>
    Swal.fire(
        'Notification',
        '<?php echo $message; ?>',
        '<?php echo $alert_type; ?>'
    );
    <?php endif; ?>
});
</script>
</body>
</html>
