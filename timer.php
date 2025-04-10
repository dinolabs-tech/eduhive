<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect to login if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
require_once 'db_connection.php'; // Use require_once for critical dependency

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the login ID
$loginid = $_SESSION['user_id'];
$loginclass= $_SESSION['user_class'];
$loginarm = $_SESSION['user_arm'];


// Fetch or initialize the student's timer
$stmt = $conn->prepare("SELECT timer FROM timer WHERE studentid = ?");
$stmt->bind_param("s", $loginid);
$stmt->execute();
$result = $stmt->get_result();
$timer_row = $result->fetch_assoc();
$stmt->close();

// Set start time with fallback
$start_time = $timer_row['timer'] ?? date('H:i:s');
if (!$timer_row) {
    $stmt = $conn->prepare("INSERT INTO timer (studentid, timer) VALUES (?, ?)");
    $stmt->bind_param("ss", $loginid, $start_time);
    $stmt->execute();
    $stmt->close();
}

// Fetch expected exam duration from cbtadmin
$stmt = $conn->prepare("SELECT testtime FROM cbtadmin WHERE class = ? and arm = ?");
$stmt->bind_param("ss", $loginclass,$loginarm);
$stmt->execute();
$result = $stmt->get_result();
$exam_row = $result->fetch_assoc();
$stmt->close();

$exam_duration_minutes = $exam_row['testtime'] ?? 45; // Default to 45 minutes
$exam_duration_seconds = (int)$exam_duration_minutes * 60;

// Calculate elapsed time
$current_time = date('H:i:s');
[$start_h, $start_m, $start_s] = array_map('intval', explode(':', $start_time));
[$current_h, $current_m, $current_s] = array_map('intval', explode(':', $current_time));

$start_seconds = $start_h * 3600 + $start_m * 60 + $start_s;
$current_seconds = $current_h * 3600 + $current_m * 60 + $current_s;
$elapsed_seconds = max(0, $current_seconds - $start_seconds); // Prevent negative values
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Exam Timer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        #timerDisplay {
            font-size: 3rem;
            font-family: 'Tahoma', sans-serif;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-1">
        <div class="card mx-auto shadow" style="max-width: 400px;">
            <div class="card-header text-center bg-primary text-white">
                <h4 class="mb-0">Exam Timer</h4>
            </div>
            <div class="card-body text-center">
                <p class="lead">
                    <i>Start Time:</i>
                    <strong><?php echo htmlspecialchars($start_time); ?></strong>
                </p>
                <input type="hidden" value="<?php echo $elapsed_seconds; ?>" id="elapsedSeconds">
                <input type="hidden" value="<?php echo $exam_duration_seconds; ?>" id="examDuration">
                <div id="timerDisplay" class="my-4">00 : 00 : 00</div>
            </div>
        </div>
    </div>

    <script>
 $(document).ready(function() {
    const elapsed = parseInt($('#elapsedSeconds').val()) || 0;
    const duration = parseInt($('#examDuration').val()) || 2700; // 45 minutes default
    let timeRemaining = duration - elapsed; // Start with remaining time

    function updateTimer() {
        if (timeRemaining <= 0) {
            clearInterval(timerInterval);
            window.location = 'close.php';
            return;
        }

        const hours = Math.floor(timeRemaining / 3600);
        const minutes = Math.floor((timeRemaining % 3600) / 60);
        const seconds = timeRemaining % 60;

        if (timeRemaining <= duration / 2 && timeRemaining > 60) {
            $('#timerDisplay').css('color', 'rgba(69, 69, 255, 0.77)');
        } else if (timeRemaining <= 60) {
            $('#timerDisplay').css('color', 'red');
        }

        const formattedTime = 
            String(hours).padStart(2, '0') + ' : ' +
            String(minutes).padStart(2, '0') + ' : ' +
            String(seconds).padStart(2, '0');
        $('#timerDisplay').text(formattedTime);

        timeRemaining--; // Decrease time
    }

    if (timeRemaining > 0) {
        const timerInterval = setInterval(updateTimer, 1000);
        updateTimer(); // Initial call to avoid 1-second delay
    } else {
        console.log('Time already up on load');
        window.location = 'close.php';
    }
});
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
