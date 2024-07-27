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

// Function to generate a secure token
function generateToken($employee_id) {
    return bin2hex(random_bytes(16)) . $employee_id;
}

// Function to store token
function storeToken($token, $employee_id) {
    global $conn;
    $expiry = date("Y-m-d H:i:s", strtotime("+1 hour")); // Token expires in 1 hour
    $sql = "INSERT INTO tokens (token, employee_id, expiry) VALUES ('$token', '$employee_id', '$expiry')";
    $conn->query($sql);
}

// Function to validate token
function validateToken($token, $employee_id) {
    global $conn;
    $sql = "SELECT * FROM tokens WHERE token='$token' AND employee_id='$employee_id' AND expiry > NOW()";
   
    $result = $conn->query($sql);
    return $result->num_rows > 0;
}
?>
