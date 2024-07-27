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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task_id = $_POST['task_id'];

    // Log the received data for debugging
    error_log("Received task_id: $task_id");

    // Update task status to 'completed'
    $sql_update_task = "UPDATE tasks SET status='completed' WHERE id='$task_id'";
    
    if ($conn->query($sql_update_task) === TRUE) {
        // Get employee_id associated with the task
        $sql_get_task = "SELECT employee_id FROM tasks WHERE id='$task_id'";
        $result = $conn->query($sql_get_task);
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $employee_id = $row['employee_id'];

            // Create a notification for the employee
            $notification_message = "Task ID $task_id has been marked as completed.";
            $sql_create_notification = "INSERT INTO notifications (employee_id, message, task_id, created_at, is_read) 
                                        VALUES ('$employee_id', '$notification_message', '$task_id', NOW(), 0)";
            if ($conn->query($sql_create_notification) === TRUE) {
                echo "success";
            } else {
                echo "Error creating notification: " . $conn->error;
            }
        }
    } else {
        echo "Error: " . $sql_update_task . "<br>" . $conn->error;
    }
}

$conn->close();
?>
