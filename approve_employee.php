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

// Start a transaction
$conn->begin_transaction();

try {
    // Move all data from pending_employees to employees
    $sql_insert = "
        INSERT INTO employees (
            username,
            email,
            password,
            role,
            designation,
            qualification,
            address,
            contact_number,
            resume,
            degree_certificate,
            aadhaar_card,
            pan_card,
            bank_passbook,
            live_image
        )
        SELECT 
            username,
            email,
            password,
            role,
            designation,
            qualification,
            address,
            contact_number,
            resume,
            degree_certificate,
            aadhaar_card,
            pan_card,
            bank_passbook,
            live_image
        FROM pending_employees
    ";

    if (!$conn->query($sql_insert)) {
        throw new Exception("Error moving data to employees: " . $conn->error);
    }

    // Handle specific employee approval if an employee_id is provided
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['employee_id'])) {
        $employee_id = $conn->real_escape_string($_POST['employee_id']);

        // Fetch the employee details from pending_employees
        $sql_select = "SELECT * FROM pending_employees WHERE employee_id = '$employee_id'";
        $result = $conn->query($sql_select);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Ensure all required fields have values
            $fields = ['designation', 'qualification', 'address', 'contact_number', 'resume', 'degree_certificate', 'aadhaar_card', 'pan_card', 'live_image', 'bank_passbook'];
            foreach ($fields as $field) {
                if (empty($row[$field])) {
                    throw new Exception("Error: $field is required and cannot be empty.");
                }
            }

            // Prepare and execute statement to insert into employees
            $stmt = $conn->prepare("
                INSERT INTO employees (
                    username, email, password, role, designation, qualification, address,
                    contact_number, resume, degree_certificate, aadhaar_card, pan_card, bank_passbook, live_image
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            if ($stmt) {
                $stmt->bind_param(
                    "ssssssssssssss",
                    $row['username'],
                    $row['email'],
                    $row['password'],
                    $row['role'],
                    $row['designation'],
                    $row['qualification'],
                    $row['address'],
                    $row['contact_number'],
                    $row['resume'],
                    $row['degree_certificate'],
                    $row['aadhaar_card'],
                    $row['pan_card'],
                    $row['bank_passbook'],
                    $row['live_image']
                );

                if (!$stmt->execute()) {
                    throw new Exception("Error inserting employee into employees: " . $stmt->error);
                }

                // Delete from pending_employees
                $sql_delete = "DELETE FROM pending_employees WHERE employee_id = '$employee_id'";
                if (!$conn->query($sql_delete)) {
                    throw new Exception("Error deleting employee from pending_employees: " . $conn->error);
                }
                
                echo "Employee approved successfully";
            } else {
                throw new Exception("Error preparing statement.");
            }

            $stmt->close();
        } else {
            throw new Exception("No pending employee found with this ID.");
        }
    }

    // Commit the transaction
    $conn->commit();
} catch (Exception $e) {
    // Rollback the transaction if an error occurs
    $conn->rollback();
    echo "Transaction failed: " . $e->getMessage();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$conn->close();
?>
