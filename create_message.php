<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start the session to maintain user state
session_start();

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
include 'db_connection.php';

// Check connection to the database
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Assume the logged in user’s id is stored in session under 'user_id'
$loggedInUserId = $_SESSION['user_id'];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  
  // Sanitize input data
  $subject   = $conn->real_escape_string($_POST['subject']);
  // Get the message from TinyMCE (HTML content)
  $message   = $conn->real_escape_string($_POST['message']);
  // Use the logged in user id for from_user (no need to post it from the form)
  $from_user = $loggedInUserId;
  
  // Retrieve the hidden field with recipient id
  $to_user   = $conn->real_escape_string($_POST['to_user_id']);
  // Set default status (e.g., 0 for unread)
  $status    = 0;
  
  // Insert the new message record using the id's
  $sql = "INSERT INTO mail (subject, message, from_user, to_user, status)
          VALUES ('$subject', '$message', '$from_user' ,'$to_user', '$status')";
  
  if ($conn->query($sql) === TRUE) {
    echo '<script>alert("Message sent successfully!");</script>';

  } else {
    echo "<p>Error: " . $conn->error . "</p>";
  }
  
}


$role=isset($_SESSION['role']) ? $_SESSION['role']:'';
//set the appropriate url based on the user role
if ($role ==='Student') {
  // Fetch the logged-in student's name
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name FROM students WHERE id=?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($student_name);
$stmt->fetch();
$stmt->close();

}elseif ($role ==='Administrator'){
  // Fetch the logged-in Staff name
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT staffname FROM login WHERE id=?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($student_name);
$stmt->fetch();
$stmt->close();
}elseif ($role ==='Store'){
  // Fetch the logged-in Staff name
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT staffname FROM login WHERE id=?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($student_name);
$stmt->fetch();
$stmt->close();
}elseif ($role ==='Library'){
  // Fetch the logged-in Staff name
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT staffname FROM login WHERE id=?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($student_name);
$stmt->fetch();
$stmt->close();
}elseif ($role ==='Tuckshop'){
  // Fetch the logged-in Staff name
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT staffname FROM login WHERE id=?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($student_name);
$stmt->fetch();
$stmt->close();
}elseif ($role ==='Teacher'){
  // Fetch the logged-in Staff name
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT staffname FROM login WHERE id=?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($student_name);
$stmt->fetch();
$stmt->close();
}elseif ($role ==='Bursary'){
  // Fetch the logged-in Staff name
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT staffname FROM login WHERE id=?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($student_name);
$stmt->fetch();
$stmt->close();
}elseif ($role ==='Admission'){
  // Fetch the logged-in Staff name
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT staffname FROM login WHERE id=?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($student_name);
$stmt->fetch();
$stmt->close();
}elseif ($role==='Alumni') {
    // Fetch the logged-in student's name
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name FROM students WHERE id=?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($student_name);
$stmt->fetch();
$stmt->close();
}






$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<?php include('head.php');?>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- TinyMCE -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>
    <script>
     

      // jQuery function for AJAX-based student search
      $(document).ready(function(){
        $('#to_user').on('input', function(){
          var query = $(this).val();
          if(query.length > 1){
            $.ajax({
              url: 'search_students.php',
              type: 'GET',
              data: { query: query },
              success: function(data){
                $('#studentList').fadeIn();
                $('#studentList').html(data);
              }
            });
          } else {
            $('#studentList').fadeOut();
          }
        });

        // When a student name is clicked, set both the visible input and the hidden recipient id field
        $(document).on('click', '.student', function(){
          var username = $(this).text();
          var studentId = $(this).data('id');
          $('#to_user').val(username);
          $('#to_user_id').val(studentId);
          $('#studentList').fadeOut();
        });
      });
    </script>
    <style>
      /* Simple styling for the dropdown list */
      #studentList {
        border: 1px solid #ccc;
        max-height: 150px;
        overflow-y: auto;
        display: none;
        position: absolute;
        background: #fff;
        width: 90%;
        z-index: 1000;
      }
      .student {
        padding: 5px;
        cursor: pointer;
      }
      .student:hover {
        background-color: #f0f0f0;
      }
    </style>
  
  
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
      }elseif ($role ==='Teacher'){
        include('adminnav.php'); 
      }elseif ($role ==='Admission'){
        include('adminnav.php'); 
      }elseif ($role ==='Bursary'){
        include('adminnav.php'); 
      }elseif ($role ==='Store'){
        include('adminnav.php'); 
      }elseif ($role ==='Tuckshop'){
        include('adminnav.php'); 
      }elseif ($role ==='Library'){
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
            <?php include('logo_header.php'); ?>
            <!-- End Logo Header -->
          </div>
          <!-- Navbar Header -->
          <?php include('navbar.php'); ?>
          <!-- End Navbar -->
        </div>

        <div class="container">
          <div class="page-inner">
            <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
              <div>
                <h3 class="fw-bold mb-3">New Mail</h3>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="students.php">Home</a></li>
                  <li class="breadcrumb-item active">Create Mail</li>
                </ol>
              </div>
            </div>

            <div class="col-md-12">
              <div class="card">
                <div class="card-header">
                  <h4 class="card-title">Create New Message</h4>   
                </div>
                <div class="card-body">
                  <form method="post" action="">
                    <!-- From field is now hidden because it comes from the session -->
                    <input type="hidden" name="from_user" value="<?php echo $loggedInUserId; ?>">
                    
                    <div class="form-group">
                      <label for="to_user">To:</label>
                      <input class="form-control" type="text" id="to_user" name="to_user" autocomplete="off" required>
                      <!-- Hidden field to store the recipient's id -->
                      <input type="hidden" id="to_user_id" name="to_user_id">
                      <div id="studentList"></div>
                    </div>
                    <br>
                    
                    <div class="form-group">
                      <label for="subject">Subject:</label>
                      <input class="form-control" type="text" id="subject" name="subject" required>
                    </div>
                    <br>
                    
                    <div class="form-group">
                      <label for="message">Message:</label>
                      <textarea class="form-control" id="message" name="message"></textarea>
                    </div>
                    <br>
                    
                    <input class="btn btn-success" type="submit" value="Send Message">
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
     
        <?php include('footer.php'); ?>
      </div>

      <!-- Custom template | don't include it in your project! -->
      <?php include('cust-color.php'); ?>
      <!-- End Custom template -->
    </div>
    <?php include('scripts.php'); ?>

    <script>
    tinymce.init({
      selector: '#message',
      menubar: false,
      toolbar: 'undo redo | formatselect | bold italic underline superscript subscript | alignleft aligncenter alignright | bullist numlist outdent indent | table',
      plugins: 'lists',
      branding: false
    });
  </script>

  </body>
</html>
