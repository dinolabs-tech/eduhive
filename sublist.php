<?php include('components/students_logic.php');


$loginid = $_SESSION['user_id']; // Get the student ID
$student_class = $_SESSION['user_class']; // Get the student class
$student_arm = $_SESSION['user_arm']; // Get the student arm

// Prepare SQL for students with additional columns: class and arm
$stmt1 = $conn->prepare("SELECT id, password, class, arm FROM students WHERE id=? AND password=?");
$stmt1->bind_param("ss", $user, $pass);
$stmt1->execute();
$stmt1->store_result();

// Fetch current term and session
$current_term_result = $conn->query("SELECT cterm FROM currentterm WHERE id=1");
if (!$current_term_result) {
    die("Error fetching current term: " . $conn->error);
}
$current_term = $current_term_result->fetch_assoc()['cterm'];

$current_session_result = $conn->query("SELECT csession FROM currentsession WHERE id=1");
if (!$current_session_result) {
    die("Error fetching current session: " . $conn->error);
}
$current_session = $current_session_result->fetch_assoc()['csession'];

// Check if the candidate has been authorized for any questions
$stmt11 = $conn->prepare("SELECT * FROM question WHERE class = ? AND arm = ? AND term = ? AND session = ?");
$stmt11->bind_param("ssss", $student_class, $student_arm, $current_term, $current_session);
$stmt11->execute();
$stmt11->store_result(); // Store the result set

if ($stmt11->num_rows == 0) {
    echo '<script type="text/javascript">
    alert("You have not been authorized for any questions yet. Kindly wait for your time");
    window.location="students.php";
    </script>';
    exit();
}

$stmt11->close();

// Fetch the scheduled test date for this student’s class and arm from the cbtadmin table
$stmt_date = $conn->prepare("SELECT testdate FROM cbtadmin WHERE class = ? AND arm = ? LIMIT 1");
$stmt_date->bind_param("ss", $student_class, $student_arm);
$stmt_date->execute();
$stmt_date->bind_result($testdate);
$stmt_date->fetch();
$stmt_date->close();

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Dashboard</title>
    <meta
      content="width=device-width, initial-scale=1.0, shrink-to-fit=no"
      name="viewport"
    />
    <link
      rel="icon"
      href="assets/img/kaiadmin/favicon.ico"
      type="image/x-icon"
    />

    <!-- Fonts and icons -->
    <script src="assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
      WebFont.load({
        google: { families: ["Public Sans:300,400,500,600,700"] },
        custom: {
          families: [
            "Font Awesome 5 Solid",
            "Font Awesome 5 Regular",
            "Font Awesome 5 Brands",
            "simple-line-icons",
          ],
          urls: ["assets/css/fonts.min.css"],
        },
        active: function () {
          sessionStorage.fonts = true;
        },
      });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/plugins.min.css" />
    <link rel="stylesheet" href="assets/css/kaiadmin.min.css" />

    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link rel="stylesheet" href="assets/css/demo.css" />
    <link rel="stylesheet" href="components/students_style.css" />

  </head>
  <body>
    <div class="wrapper">
      <!-- Sidebar -->
      <?php 
      
      $role=isset($_SESSION['role']) ? $_SESSION['role']:'';
      //set the appropriate url based on the user role
      if ($role ==='Student') {
        include('studentnav.php'); 
      }elseif ($role ==='Administrator'){
        include('adminnav.php'); 
      }elseif ($role==='Alumni') {
        include('alumninav.php'); 
      }

      ?>
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
                <h3 class="fw-bold mb-3">Take Exam</h3>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="students.php">Home</a></li>
                  <li class="breadcrumb-item active">CBT</li>
                  <li class="breadcrumb-item active">Take Exam</li>
              </ol>
              </div>
           
            </div>

         
              
          
            <div class="col-md-12">
              <div class="card">
                  <div class="card-header">
                      <h4 class="card-title"> Select Your Subject(s) </h4>   
                  </div>
                  <div class="card-body">
                      <div class="list-group">
                          <?php
                          $query = "SELECT * FROM question GROUP BY subject";
                          $result = $conn->query($query);
                          $today = date("Y-m-d");

                          while ($row = $result->fetch_row()) {
                              $subject = $row[1]; // Assuming $row[1] is the subject name

                              // Check if the student has already taken this subject
                              $stmt_check = $conn->prepare("SELECT * FROM mst_result WHERE login = ? AND subject = ?");
                              $stmt_check->bind_param("ss", $loginid, $subject);
                              $stmt_check->execute();
                              $stmt_check->store_result();
                              $already_taken = $stmt_check->num_rows > 0;
                              $stmt_check->close();

                              if ($already_taken) {
                                  // If the subject has been taken, create a disabled link with an alert
                                  echo "<a href='#' class='list-group-item list-group-item-action fs-5 text-muted' 
                                        onclick=\"alert('You have already taken the " . htmlspecialchars($subject) . " exam.'); return false;\">" 
                                      . htmlspecialchars($subject) . " (Already Taken)</a>";
                              } elseif ($testdate < $today) {
                                  // If the test date has passed, show a missed test notification
                                  echo "<a href='#' class='list-group-item list-group-item-action fs-5 text-muted' 
                                        onclick=\"alert('Sorry, you missed your test date on " . htmlspecialchars($testdate) . "'); return false;\">" 
                                      . htmlspecialchars($subject) . " (Missed Test Date)</a>";
                              } elseif ($testdate > $today) {
                                  // If the scheduled test date is in the future, show notification with the test date
                                  echo "<a href='#' class='list-group-item list-group-item-action fs-5 text-muted' 
                                        onclick=\"alert('Sorry, you do not have a test scheduled for today. Please come back on " . htmlspecialchars($testdate) . "'); return false;\">" 
                                      . htmlspecialchars($subject) . " (Test Not Today)</a>";
                              } else {
                                  // If test is scheduled for today and exam not yet taken, allow navigation to quiz.php
                                  echo "<a href='quiz.php?subid=" . urlencode($subject) . "' class='list-group-item list-group-item-action fs-5'>" 
                                      . htmlspecialchars($subject) . "</a>";
                              }
                          }
                          ?>
                      </div>
                  </div>
              </div>
          </div>

        </div>
     
        <?php include('footer.php');?>
      </div>

      <!-- Custom template | don't include it in your project! -->
      <?php include('cust-color.php');?>
      <!-- End Custom template -->
    </div>
   <?php include('scripts.php');?>
  </body>
</html>
