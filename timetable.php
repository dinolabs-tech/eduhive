<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start the session to maintain user state
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
include 'db_connection.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$sql = "SELECT * FROM timetable";
$result = $conn->query($sql);

// Convert result set into an array so it can be looped over safely later
$students = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}



// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['day'], $_POST['class'], $_POST['arm'], $_POST['subject'], $_POST['starttime'], $_POST['endtime'])) {
    $day = $_POST['day'];
    $class = $_POST['class'];
    $arm = $_POST['arm']; 
    $subject = $_POST['subject'];
    $starttime = $_POST['starttime'];
    $endtime = $_POST['endtime'];


    $stmt = $conn->prepare("INSERT INTO timetable (day, class, arm, subject, starttime, endtime) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $day, $class, $arm, $subject, $starttime, $endtime);

    if ($stmt->execute()) {
        $message = "Record saved successfully!";
            // Redirect back to the same page to refresh
           header("Location: " . $_SERVER['PHP_SELF']);
           exit;
    } else {
        $message = "Error: " . $stmt->error;
    }

    $stmt->close();
}


// Handle deletion of a student record
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM timetable WHERE id='$id'";
    if ($conn->query($sql) === TRUE) {
         // Redirect back to the same page to refresh
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}

// Fetch unique classes and arms for the comboboxes
$classes_result = $conn->query("SELECT DISTINCT class FROM class");
$classes = [];
while ($row = $classes_result->fetch_assoc()) {
    $classes[] = $row['class'];
}

$arms_result = $conn->query("SELECT DISTINCT arm FROM arm");
$arms = [];
while ($row = $arms_result->fetch_assoc()) {
    $arms[] = $row['arm'];
}



// Filter out unwanted options
$classes = array_filter($classes, function($class) {
  return $class !== 'zxcz';
});
$arms = array_filter($arms, function($arm) {
  return $arm !== 'zxc';
});



// Fetch the logged-in Staff name
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT staffname FROM login WHERE id=?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($student_name);
$stmt->fetch();
$stmt->close();



// Close database connection
$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<?php include('head.php');?>
  <body>
    <div class="wrapper">
      <!-- Sidebar -->
     <?php include('adminnav.php');?>
      <!-- End Sidebar -->

      <div class="main-panel">
        <div class="main-header">
          <div class="main-header-logo">
            <!-- Logo Header -->
            <?php include('logo_header.php');?>
            <!-- End Logo Header -->
          </div>
          <!-- Navbar Header -->
         <?php include('navbar.php');?>
          <!-- End Navbar -->
        </div>

        <div class="container">
          <div class="page-inner">
            <div
              class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4"
            >
              <div>
                <nts class="fw-bold mb-3">Class Schedule</h3>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                  <li class="breadcrumb-item active">Class Schedule</li>
              </ol>
              </div>
           
            </div>

            <!-- BULK UPLOAD ============================ -->
            <div class="row">
             
            <div class="col-md-12">
               <div class="card card-round">
                 <div class="card-header">
                   <div class="card-head-row">
                     <div class="card-title">Create Schedule</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">
                    
                      <form method="POST">

                        <div class="mb-3">
                        <select class="form-control form-select" id="day" name="day" required>
                            <option value="">Select Day</option>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                        </select>
                        </div>

                        <div class="mb-3">
                        <select class="form-control form-select" name="class" id="class" required>
                        <option value="" disabled selected>Select Class </option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?= htmlspecialchars($class, ENT_QUOTES) ?>"><?= htmlspecialchars($class, ENT_QUOTES) ?></option>
                        <?php endforeach; ?>
                        </select>
                        </div>

                        <div class="mb-3">
                        <select class="form-control form-select" name="arm" id="arm" required>
                            <option value="" disabled selected>Select Arm </option>
                            <?php foreach ($arms as $arm): ?>
                                <option value="<?= htmlspecialchars($arm, ENT_QUOTES) ?>"><?= htmlspecialchars($arm, ENT_QUOTES) ?></option>
                            <?php endforeach; ?>
                        </select>
                        </div>

                        <div class="mb-3">
                        <input type="text" id="subject" name="subject" class="form-control form-control" placeholder="Subject" required>
                        </div>
                        <div class="mb-3">
                        <select class="form-control form-select" id="starttime" name="starttime" required>
                            <option value="">Start Time</option>
                            <option value="8:15">8:15</option>
                            <option value="9:00">9:00</option>
                            <option value="9:45">9:45</option>
                            <option value="10:30">10:30</option>
                            <option value="11:00">11:00</option>
                            <option value="11:45">11:45</option>
                            <option value="12:30">12:30</option>
                            <option value="13:15">13:15</option>
                            <option value="14:00">14:00</option>
                            <option value="14:30">14:30</option>
                            <option value="15:15">15:15</option>
                        </select>
                        </div>

                        <div class="mb-3">
                        <select class="form-control form-select" id="endtime" name="endtime" required>
                            <option value="">End Time</option>
                            <option value="9:00">9:00</option>
                            <option value="9:45">9:45</option>
                            <option value="10:30">10:30</option>
                            <option value="11:00">11:00</option>
                            <option value="11:45">11:45</option>
                            <option value="12:30">12:30</option>
                            <option value="13:15">13:15</option>
                            <option value="14:00">14:00</option>
                            <option value="14:30">14:30</option>
                            <option value="15:15">15:15</option>
                            <option value="16:00">16:00</option>
                        </select>
                        </div>


                        <button type="submit" name="submit" class="btn btn-success"><span class="btn-label">
                        <i class="fa fa-save"></i> Save</button>

                        <button type="reset" class="btn btn-warning"><span class="btn-label">
                        <i class="fa fa-undo"></i> Reset</button>
                        </form>
                       

                   </div>
                 </div>
               </div>
             </div>
           </div>

          
           <div class="row">
             
             <div class="col-md-12">
               <div class="card card-round">
                 <div class="card-header">
                   <div class="card-head-row">
                     <div class="card-title">Schedule List</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">
                    
                          <p>
                        
                          <div class="table-responsive">
                            <table
                          id="multi-filter-select"
                          class="display table table-striped table-hover">
                                <thead>
                                    <tr>
                                        
                                        <th>Day</th>
                                        <th>Class</th>
                                        <th>Arm</th>
                                        <th>Subject</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (!empty($students)): ?>
                                  <?php foreach ($students as $student): ?>
                                            
                                            <td><?php echo $student['day']; ?></td>
                                            <td><?php echo $student['class']; ?></td>
                                            <td><?php echo $student['arm']; ?></td>
                                            <td><?php echo $student['subject']; ?></td>
                                            <td><?php echo $student['starttime']; ?></td>
                                            <td><?php echo $student['endtime']; ?></td>
                                            <td>
                                                <!--<a href="?edit=<?php echo $student['id']; ?>" class="btn btn-warning btn-sm">Edit</a>-->
                                                <a href="?delete=<?php echo $student['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this record?')"><span class="btn-label">
                                                <i class="fa fa-trash"></i></a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                          <tr>
                                          <td colspan="6">No data available in table.</td>
                                        </tr>
                                      <?php endif; ?>`
                                </tbody>
                            </table>
                          </div>
                        </p>

                   </div>
                 </div>
               </div>
             </div>
           </div>



          </div>
        </div>

  </script>
        <?php include('footer.php');?>
      </div>

      <!-- Custom template | don't include it in your project! -->
      <?php include('cust-color.php');?>
      <!-- End Custom template -->
    </div>
   <?php include('scripts.php');?>
  </body>
</html>
