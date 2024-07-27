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

$response = array('status' => 'error', 'message' => 'An error occurred.');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if all required POST fields are set
    if (isset($_POST['username'], $_POST['email'], $_POST['password'], $_POST['role'], $_POST['designation'], $_POST['qualification'], $_POST['address'], $_POST['contact_number'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];
        $designation = $_POST['designation'];
        $qualification = $_POST['qualification'];
        $address = $_POST['address'];
        $contact_number = $_POST['contact_number'];

        // Handle file uploads
        $upload_dir = 'uploads/';
        $resume = isset($_FILES['resume']['name']) ? $_FILES['resume']['name'] : '';
        $degree_certificate = isset($_FILES['degree_certificate']['name']) ? $_FILES['degree_certificate']['name'] : '';
        $aadhaar_card = isset($_FILES['aadhaar_card']['name']) ? $_FILES['aadhaar_card']['name'] : '';
        $pan_card = isset($_FILES['pan_card']['name']) ? $_FILES['pan_card']['name'] : '';
        $bank_passbook = isset($_FILES['bank_passbook']['name']) ? $_FILES['bank_passbook']['name'] : '';
        $live_image_data = isset($_POST['live_image_data']) ? $_POST['live_image_data'] : '';

        // Upload files if they exist
        if ($resume) move_uploaded_file($_FILES['resume']['tmp_name'], $upload_dir . $resume);
        if ($degree_certificate) move_uploaded_file($_FILES['degree_certificate']['tmp_name'], $upload_dir . $degree_certificate);
        if ($aadhaar_card) move_uploaded_file($_FILES['aadhaar_card']['tmp_name'], $upload_dir . $aadhaar_card);
        if ($pan_card) move_uploaded_file($_FILES['pan_card']['tmp_name'], $upload_dir . $pan_card);
        if ($bank_passbook) move_uploaded_file($_FILES['bank_passbook']['tmp_name'], $upload_dir . $bank_passbook);

        // Process live image
        $live_image = '';
        if ($live_image_data) {
            $live_image = 'live_image_' . time() . '.png';
            $live_image_path = $upload_dir . $live_image;
            $image_data = str_replace('data:image/png;base64,', '', $live_image_data);
            $image_data = base64_decode($image_data);
            file_put_contents($live_image_path, $image_data);
        }

        if ($role === 'employee') {
            // Insert into pending_employees for approval
            $stmt = $conn->prepare("INSERT INTO pending_employees (username, email, password, role, designation, qualification, address, contact_number, resume, degree_certificate, aadhaar_card, pan_card, bank_passbook, live_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        } elseif ($role === 'admin') {
            // Directly insert into employees without approval
            $stmt = $conn->prepare("INSERT INTO employees (username, email, password, role, designation, qualification, address, contact_number, resume, degree_certificate, aadhaar_card, pan_card, bank_passbook, live_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        } else {
            $response['message'] = 'Invalid role.';
            echo json_encode($response);
            $conn->close();
            exit();
        }

        if ($stmt) {
            $stmt->bind_param('ssssssssssssss', $username, $email, $password, $role, $designation, $qualification, $address, $contact_number, $resume, $degree_certificate, $aadhaar_card, $pan_card, $bank_passbook, $live_image);

            if ($stmt->execute()) {
                $response = array('status' => 'success', 'message' => $role === 'employee' ? 'Please wait for admin approval.' : 'You can log in now.');
            } else {
                $response['message'] = 'Error: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $response['message'] = 'Error preparing statement.';
        }
        $conn->close();
    } else {
        $response['message'] = 'Missing required fields.';
    }
    
    echo json_encode($response);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <style>
        body {
            background: url('plain-banner.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 700px;
            margin-top: 50px;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 40px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        h2 {
            color: #343a40;
            text-align: center;
            margin-bottom: 30px;
        }
        .form-group label {
            font-weight: bold;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }
        .btn-primary:focus {
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.5);
        }
        .btn {
            min-width: 150px;
        }
        .home-btn {
            margin-bottom: 20px;
        }
        #capturedImage {
            width: 100%;
            height: auto;
            border: 1px solid #ccc;
            margin-top: 10px;
            display: none; /* Initially hidden */
        }
        #cameraSection {
            display: none; /* Initially hidden */
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
        }
        #cameraSection video {
            width: 100%;
            height: auto;
            border: 1px solid #ccc;
        }
        #cameraSection canvas {
            display: none;
        }
        .camera-controls {
            margin-top: 10px;
        }
        .btn-container {
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Signup</h2>
    <!-- <a href="index.php" class="btn btn-primary home-btn"><i class="fas fa-home"></i> Home</a> -->
    <form id="signupForm" method="post" enctype="multipart/form-data">
        <!-- Form fields -->
        <div class="form-group">
            <label for="employeeid">EmployeeID:</label>
            <input type="text" class="form-control" id="employeeid" name="employeeid" required>
        </div>
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="role">Role:</label>
            <select class="form-control" id="role" name="role" required>
                <option value="employee">Employee</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <div class="form-group">
            <label for="designation">Designation:</label>
            <input type="text" class="form-control" id="designation" name="designation" required>
        </div>
        <div class="form-group">
            <label for="qualification">Qualification:</label>
            <select class="form-control" id="qualification" name="qualification" required>
                <option value="post_graduate">Post Graduate</option>
                <option value="under_graduate">Under Graduate</option>
                <option value="diploma">Diploma</option>
                <option value="hsc">HSC</option>
                <option value="sslc">SSLC</option>
                
            </select>
        </div>
        <div class="form-group">
            <label for="address">Address:</label>
            <textarea class="form-control" id="address" name="address" required></textarea>
        </div>
        <div class="form-group">
            <label for="contact_number">Contact Number:</label>
            <input type="text" class="form-control" id="contact_number" name="contact_number" required>
        </div>
        <div class="form-group">
            <label for="resume">Resume:</label>
            <input type="file" class="form-control-file" id="resume" name="resume" accept="*/*" required>
        </div>
        <div class="form-group">
            <label for="degree_certificate">Degree Certificate:</label>
            <input type="file" class="form-control-file" id="degree_certificate" name="degree_certificate" accept="*/*" required>
        </div>
        <div class="form-group">
            <label for="aadhaar_card">Aadhaar Card:</label>
            <input type="file" class="form-control-file" id="aadhaar_card" name="aadhaar_card" accept="*/*" required>
        </div>
        <div class="form-group">
            <label for="pan_card">PAN Card:</label>
            <input type="file" class="form-control-file" id="pan_card" name="pan_card" accept="*/*" required>
        </div>
        <div class="form-group">
            <label for="bank_passbook">Bank Passbook:</label>
            <input type="file" class="form-control-file" id="bank_passbook" name="bank_passbook" accept="*/*" required>
        </div>
        <div class="form-group">
    <label for="live_image">Live Image:</label>
    <input type="hidden" id="live_image_data" name="live_image_data" required>
    <div id="cameraSection">
        <video id="video" autoplay></video>
        <canvas id="canvas"></canvas>
        <div class="camera-controls">
            <button type="button" id="captureButton" class="btn btn-primary">Capture</button>
            <button type="button" id="retakeButton" class="btn btn-secondary">Retake</button>
        </div>
        <img id="capturedImage" alt="Captured Image">
    </div>
    <button type="button" id="startCameraButton" class="btn btn-primary">Start Camera</button>
</div>
<div class="btn-container">
        <a href="index.php" class="btn btn-danger ">close</a>
        <button type="submit" class="btn btn-success ">Signup</button>
    </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const startCameraButton = document.getElementById('startCameraButton');
    const cameraSection = document.getElementById('cameraSection');
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const captureButton = document.getElementById('captureButton');
    const retakeButton = document.getElementById('retakeButton');
    const capturedImage = document.getElementById('capturedImage');
    const liveImageData = document.getElementById('live_image_data');
    const signupForm = document.getElementById('signupForm');

    startCameraButton.addEventListener('click', function () {
        cameraSection.style.display = 'flex';
        startCameraButton.style.display = 'none';
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => {
                video.srcObject = stream;
            })
            .catch(err => {
                console.error("Error accessing the camera: " + err);
            });
    });

    captureButton.addEventListener('click', function () {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
        const imageDataURL = canvas.toDataURL('image/png');
        capturedImage.src = imageDataURL;
        capturedImage.style.display = 'block';
        liveImageData.value = imageDataURL;
    });

    retakeButton.addEventListener('click', function () {
        capturedImage.style.display = 'none';
        liveImageData.value = '';
    });

    signupForm.addEventListener('submit', function (e) {
        // Prevent default form submission
        e.preventDefault();
        
        // Check if live_image_data is empty
        if (!liveImageData.value) {
            Swal.fire({
                icon: 'warning',
                title: 'Warning!',
                text: 'Please capture a live image before submitting the form.',
            });
            return; // Stop form submission
        }
        
        const formData = new FormData(signupForm);

        fetch(signupForm.action, {
            method: signupForm.method,
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                }).then(() => {
                    window.location.href = 'index.php';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message,
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'An error occurred while submitting the form.',
            });
        });
    });
});
</script>
    
</body>
</html>
