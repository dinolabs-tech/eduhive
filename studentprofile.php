<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// Database connection
include 'db_connection.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user ID from session
$user_id = $_SESSION['user_id'];

// Initialize message variable
$message = '';

// Handle form submission for changing password
if (isset($_POST['update_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate new password and confirm password
    if ($new_password !== $confirm_password) {
        $message = "New passwords do not match.";
    } else {
        // Update new password in the database for the logged-in student only
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("UPDATE students SET password=? WHERE id=?");
        $stmt->bind_param("ss", $new_password, $user_id);

        if ($stmt->execute()) {
            $message = "Password changed successfully!";
        } else {
            $message = "Error changing password: " . $stmt->error;
        }

        $stmt->close();
    }
}


// Handle form submission for updating profile
if (isset($_POST['update_profile'])) {
     $mobile = $_POST['mobile'];
     $email = $_POST['email'];
     $address = $_POST['address'];

      // Update email and mobile in the database for the logged-in student only
      $user_id = $_SESSION['user_id'];
      $stmt = $conn->prepare("UPDATE students SET studentmobile=?, email=?, address=? WHERE id=?");
      $stmt->bind_param("ssss", $mobile, $email, $address, $user_id);

      if ($stmt->execute()) {
          $message = "Profile Updated successfully!";
      } else {
          $message = "Error Updating Profile: " . $stmt->error;
      }

      $stmt->close();
  }


// Fetch student details from the database
$student_details = [];
$sql = "SELECT address, studentmobile, email FROM students WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->bind_result($student_details['address'], $student_details['studentmobile'], $student_details['email']);
    $stmt->fetch();
    $stmt->close();
} else {
    die("Failed to fetch student details: " . $conn->error);
}


// Fetch the logged-in student's name
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name FROM students WHERE id=?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($student_name);
$stmt->fetch();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
  <?php include('head.php');?>
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
                <h3 class="fw-bold mb-3">Profile</h3>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="students.php">Home</a></li>
                  <li class="breadcrumb-item active">Profile</li>
              </ol>
              </div>
           
            </div>

              <div class="message">
                  <?php 
                    if (!empty($message)) { 
                        echo '<div class="alert alert-info">' . htmlspecialchars($message) . '</div>'; 
                    } 
                  ?>
                </div>
                          <br>

            <div class="row">
              <div class="col-md-4">
                <div class="card card-round">
                  <div class="card-header">
                    <div class="card-head-row card-tools-still-right">
                      <div class="card-title">Change Password</div>
                      <div class="card-tools">
                        <div class="dropdown">
                         
                          
                        </div>
                      </div>


                    </div>
                    <div class="card-list py-4">
                    <div class="card-body p-0">

                          <form method="POST" action="">
                          <div class="form-group">
                              <input type="password" placeholder="New Password" id="new_password" name="new_password" class="form-control form-control" required>
                          </div>
                          <div class="form-group">
                              <input type="password" placeholder="Confirm New Password" id="confirm_password" name="confirm_password" class="form-control form-control" required>
                          </div>
                          <div class="form-group">
                              <button class="btn btn-success" name="update_password" type="submit">Change Password</button>
                          </div>
                          </form>
                         
                
                  </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-8">
                <div class="card card-round">
                  <div class="card-body">
                    <div class="card-head-row card-tools-still-right">
                      <div class="card-title">Update Details</div>
                    </div>
                  </div>
                  <div class="card-body p-0">
                      <form action="" method="post">

                      <div class="form-group"> 
                        <input
                          type="text"
                          class="form-control"
                          id="mobile"
                          name="mobile"
                          placeholder="Enter Mobile"
                          value="<?php echo htmlspecialchars($student_details['studentmobile']); ?>"
                        />
                        <small id="emailHelp2" class="form-text text-muted">Your Mobile will only be shared with your class Peers.</small>
                      </div>

                      <div class="form-group"> 
                        <input
                          type="email"
                          class="form-control"
                          id="email2"
                          name="email"
                          placeholder="Enter Email"
                          value="<?php echo htmlspecialchars($student_details['email']); ?>"
                        />
                        <small id="emailHelp2" class="form-text text-muted">Your E-mail will only be shared with your class Peers.</small>
                      </div>

                      <div class="form-group"> 
                        <textarea
                          type="text"
                          class="form-control"
                          id="address"
                          name="address"
                          placeholder="Enter Address"
                          rows="5"
                        > <?php echo htmlspecialchars($student_details['address']); ?> </textarea>

                          <div class="form-group">
                          <button class="btn btn-success" type="submit" name="update_profile"> Update </button>
                          </div>

                      </form>
                         
                  
                      
                    </div>
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
