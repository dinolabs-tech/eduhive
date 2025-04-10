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

// Get the logged-in user's id
$user_id = $_SESSION['user_id'];

// Ensure a message id is provided
if (!isset($_GET['id'])) {
    echo "No message id provided.";
    exit();
}
$message_id = $_GET['id'];

$stmt = $conn->prepare("
    SELECT 
        m.id, 
        m.subject, 
        m.message, 
        m.from_user, 
        m.to_user, 
        m.status, 
        m.date_created, 
        COALESCE(s.name, l.username) AS sender 
    FROM mail m 
    LEFT JOIN students s ON m.from_user = s.id 
    LEFT JOIN login l ON m.from_user = l.id 
    WHERE m.id = ?
");
$stmt->bind_param("i", $message_id);
$stmt->execute();

// Instead of get_result(), use store_result() and bind_result()
$stmt->store_result();
if ($stmt->num_rows == 0) {
    echo "Message not found.";
    exit();
}

// Bind each column to a variable
$stmt->bind_result(
    $id, 
    $subject, 
    $messageContent, 
    $from_user, 
    $to_user, 
    $status, 
    $date_created, 
    $sender
);
$stmt->fetch();

// Create an associative array similar to before
$message = [
    'id'           => $id,
    'subject'      => $subject,
    'message'      => $messageContent,
    'from_user'    => $from_user,
    'to_user'      => $to_user,
    'status'       => $status,
    'date_created' => $date_created,
    'sender'       => $sender,
];

$stmt->close();

// Ensure the logged-in user is the recipient
if ($message['to_user'] != $user_id) {
    echo "You are not authorized to view this message.";
    exit();
}


// If the message is unread, update its status to read (assuming status 0=unread, 1=read)
if ($message['status'] == 0) {
    $update_stmt = $conn->prepare("UPDATE mail SET status = 1 WHERE id = ?");
    $update_stmt->bind_param("i", $message_id);
    $update_stmt->execute();
    $update_stmt->close();
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
 
<?php include('head.php'); ?>
 

  
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
                <h3 class="fw-bold mb-3">View Message</h3>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="students.php">Home</a></li>
                  <li class="breadcrumb-item"><a href="inbox.php">Inbox</a></li>
                  <li class="breadcrumb-item active">View Message</li>
                </ol>
              </div>
            </div>

            <div class="col-md-12">
              <div class="card">
                <div class="card-header">
                  <h4 class="card-title"><?php echo htmlspecialchars($message['subject']); ?></h4>
                </div>
                <div class="card-body">
                  <p><strong>From:</strong> <?php echo htmlspecialchars($message['sender']); ?></p>
                  <p><strong>Date:</strong> <?php echo htmlspecialchars($message['date_created']); ?></p>
                  <hr>
                  <div>
                    <?php echo $message['message']; ?>
                  </div>
                </div>
                <div class="card-footer">
                  <a href="inbox.php" class="btn btn-secondary">Back to Inbox</a>
                  <!-- Reply button triggers the reply modal -->
                  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#replyModal">
                    Reply
                  </button>
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

    <!-- Reply Modal -->
    <div class="modal fade" id="replyModal" tabindex="-1" role="dialog" aria-labelledby="replyModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <form action="reply_message.php" method="post" novalidate>
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="replyModalLabel">Reply Message</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <!-- Hidden fields -->
              <input type="hidden" name="from_user" value="<?php echo $user_id; ?>">
              <!-- Recipient will be the original sender -->
              <input type="hidden" name="to_user" value="<?php echo htmlspecialchars($message['from_user']); ?>">
              <!-- Optional: original message id reference -->
              <input type="hidden" name="reply_to" value="<?php echo $message_id; ?>">
              
              <div class="form-group">
                <label for="subject">Subject</label>
                <!-- Pre-populate subject with "Re:" -->
                <input type="text" class="form-control" id="subject" name="subject" value="Re: <?php echo htmlspecialchars($message['subject']); ?>" required>
              </div>
              <div class="form-group">
                <label for="reply_message">Message</label>
                <textarea class="form-control" id="reply_message" name="message" required></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-success">Send Reply</button>
            </div>
          </div>
        </form>
      </div>
    </div>

  

    <!-- Initialize TinyMCE for the reply textarea -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>

<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<script>
  // When the modal is shown, initialize TinyMCE if needed (if the modal is hidden on page load, this can be a factor)
  $('#replyModal').on('shown.bs.modal', function () {
    if (!tinymce.get('reply_message')) {
      tinymce.init({
        selector: '#reply_message',
        menubar: false,
        toolbar: 'undo redo | formatselect | bold italic underline superscript subscript | alignleft aligncenter alignright | bullist numlist outdent indent | table',
        plugins: 'lists',
        branding: false
      });
    }
  });

  // Trigger TinyMCE to save its content back to the textarea when the form is submitted
  $('#replyModal form').on('submit', function() {
    tinymce.triggerSave();
  });
</script>


  </body>
</html>
