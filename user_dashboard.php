<?php
include 'functions.php';

session_start();

// Check if the admin is accessing this page
if (isset($_GET['admin_access']) && $_GET['admin_access'] == 'true' && isset($_GET['employee_id']) && isset($_GET['token'])) {
    $employee_id = $_GET['employee_id'];
    $token = $_GET['token'];
    
    if (!validateToken($token, $employee_id)) {
        die("Invalid token");
    }
} else {
    // Normal user access
    if (!isset($_SESSION['employee_id']) || $_SESSION['role'] !== 'employee') {
        header("Location: login.php");
        exit;
    }
    $employee_id = $_SESSION['employee_id'];
}

// Database connection
$conn = new mysqli($servername, $username, $password, $dbname); // Add your database connection here

// Fetch the username
$sql_username = "SELECT username FROM employees WHERE employee_id='$employee_id'";
$result_username = $conn->query($sql_username);
if ($result_username->num_rows > 0) {
    $row_username = $result_username->fetch_assoc();
    $username = $row_username['username'];
} else {
    $username = "Unknown";
}

// Fetch tasks for the logged-in employee
$sql_tasks = "SELECT * FROM tasks WHERE employee_id='$employee_id'";
$result_tasks = $conn->query($sql_tasks);

// Handle work report upload
$uploadSuccess = false;
$uploadMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['report_file'])) {
    $file_name = $_FILES['report_file']['name'];
    $file_tmp = $_FILES['report_file']['tmp_name'];
    $file_path = "uploads/" . basename($file_name);
    $description = $conn->real_escape_string($_POST['description']); // Get the description from POST data

    if (move_uploaded_file($file_tmp, $file_path)) {
        $sql_upload = "INSERT INTO work_reports (employee_id, report_file, description) VALUES ('$employee_id', '$file_path', '$description')";
        
        if ($conn->query($sql_upload) === TRUE) {
            $uploadSuccess = true;
            $uploadMessage = "Work report uploaded successfully.";
        } else {
            $uploadMessage = "Error: " . $conn->error;
        }
    } else {
        $uploadMessage = "Failed to upload file.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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
            color: #fff;
        }
        .table th, .table td {
            padding: 15px;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.2); /* Semi-transparent background */
        }
        .table thead th {
            background-color: rgba(255, 255, 255, 0.3); /* Slightly more opaque for headers */
        }
        .btn-primary, .btn-success, .btn-secondary {
            margin: 5px;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }
        .preview {
            margin-top: 20px;
        }
        .preview img, .preview iframe, .preview div {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center">User Dashboard</h2>
  <h3>  <p><strong>WelcomeBack !</strong> <?php echo htmlspecialchars($username); ?></p></h3>
    <a href="index.php" class="btn btn-secondary mb-3"><i class="fas fa-home"></i> Home</a>
    <hr>

    <h3>My Tasks</h3>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
               
                <th>Task Description</th>
                <th>Status</th>
                <th>Due Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result_tasks->num_rows > 0) {
                while ($row = $result_tasks->fetch_assoc()) {
                    echo "<tr>
                           
                            <td>{$row['task_description']}</td>
                            <td>{$row['status']}</td>
                            <td>{$row['due_date']}</td>
                            <td>
                                <button class='btn btn-success complete-task-btn' data-task-id='{$row['id']}'>Complete</button>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No tasks assigned</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <hr>

    <h3>Upload Work Report</h3>
    <form method="post" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label for="report_file">Select Work Report:</label>
            <input type="file" class="form-control" id="report_file" name="report_file" required>
        </div>
        <div class="form-group">
            <label for="description">Report Description:</label>
            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Upload Report</button>
    </form>

    <div class="preview">
        <h4>File Preview:</h4>
        <div id="file-preview"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('report_file').addEventListener('change', function(event) {
    const file = event.target.files[0];
    const previewContainer = document.getElementById('file-preview');
    previewContainer.innerHTML = ''; // Clear any previous preview

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const fileURL = e.target.result;
            const fileType = file.type;
            const fileName = file.name;

            if (fileType.startsWith('image/')) {
                const img = document.createElement('img');
                img.src = fileURL;
                previewContainer.appendChild(img);
            } else if (fileType === 'application/pdf') {
                const iframe = document.createElement('iframe');
                iframe.src = fileURL;
                iframe.style.height = '600px';
                previewContainer.appendChild(iframe);
            } else if (fileType.startsWith('text/')) {
                const div = document.createElement('div');
                div.textContent = e.target.result;
                previewContainer.appendChild(div);
            } else {
                // Display the file name and type for unsupported preview types
                const div = document.createElement('div');
                div.textContent = `File name: ${fileName}\nFile type: ${fileType}`;
                previewContainer.appendChild(div);

                // Optionally, provide a link to download the file
                const downloadLink = document.createElement('a');
                downloadLink.href = fileURL;
                downloadLink.download = fileName;
                downloadLink.textContent = 'Download File';
                previewContainer.appendChild(downloadLink);
            }
        };
        reader.readAsDataURL(file);
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const completeTaskButtons = document.querySelectorAll('.complete-task-btn');
    completeTaskButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            const taskId = this.getAttribute('data-task-id');
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to mark this task as complete?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, complete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('complete_task.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `task_id=${taskId}`
                    })
                    .then(response => response.text())
                    .then(data => {
                        if (data === 'success') {
                            Swal.fire(
                                'Completed!',
                                'Task has been marked as complete.',
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Error!',
                                'There was an error completing the task.',
                                'error'
                            );
                        }
                    })
                    .catch(error => {
                        Swal.fire(
                            'Error!',
                            'There was an error completing the task.',
                            'error'
                        );
                    });
                }
            });
        });
    });

    <?php if ($uploadSuccess): ?>
    Swal.fire({
        title: 'Success!',
        text: "<?php echo $uploadMessage; ?>",
        icon: 'success',
        confirmButtonText: 'OK'
    });
    <?php elseif (!empty($uploadMessage)): ?>
    Swal.fire({
        title: 'Error!',
        text: "<?php echo $uploadMessage; ?>",
        icon: 'error',
        confirmButtonText: 'OK'
    });
    <?php endif; ?>
});
</script>
</body>
</html>
