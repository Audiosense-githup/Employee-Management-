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

$report_id = null;

// Check if the report_id is passed via GET
if (isset($_GET['report_id'])) {
    $report_id = $_GET['report_id'];
}

if (!$report_id) {
    die("No work report ID provided. Please ensure the ID is being passed correctly.");
}

// Fetch work report details from the database
$sql = "SELECT wr.report_file, e.username, wr.description 
        FROM work_reports wr 
        JOIN employees e ON wr.employee_id = e.employee_id 
        WHERE wr.report_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $report_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $report_file = $row['report_file'];
    $username = $row['username'];
    $description = $row['description'];
} else {
    die("No work report found with the provided ID.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Report Details</title>
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
        .btn-secondary, .btn-primary {
            margin: 5px;
        }
        .img-fluid {
            max-width: 100%;
            height: auto;
        }
        iframe {
            border: none;
        }
        .preview h4 {
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center">Work Report Details</h2>
     <a href="workreport.php" class="btn btn-secondary mb-3"><i class="fas fa-home"></i> Home</a> 
    <p><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></p>
    <p><strong>Report File:</strong> <?php echo htmlspecialchars($report_file); ?></p>
    <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($description)); ?></p>
    <a href="<?php echo htmlspecialchars($report_file); ?>" class="btn btn-primary" download onclick="return navigateAfterDownload()">Download Report</a>
    <div class="preview mt-3">
        <h4>File Preview:</h4>
        <div id="file-preview">
            <?php
            $file_info = pathinfo($report_file);
            $file_ext = strtolower($file_info['extension']);
            if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                echo "<img src='".htmlspecialchars($report_file)."' class='img-fluid' />";
            } elseif (in_array($file_ext, ['pdf'])) {
                echo "<iframe src='".htmlspecialchars($report_file)."' width='100%' height='600px'></iframe>";
            } else {
                echo "<p>Preview not available for this file type</p>";
            }
            ?>
        </div>
    </div>
</div>
<script>
function navigateAfterDownload() {
    setTimeout(function() {
        window.location.href = 'workreport.php';
    }, 1000);
    return true;
}
</script>
</body>
</html>
