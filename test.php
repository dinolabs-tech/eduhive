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

// Retrieve message details using a prepared statement
$stmt = $conn->prepare("SELECT m.id, m.subject, m.message, m.from_user, m.to_user, m.status, m.date_created, s.name AS sender 
                        FROM mail m 
                        LEFT JOIN students s ON m.from_user = s.id 
                        WHERE m.id = ?");
$stmt->bind_param("i", $message_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo "Message not found.";
    exit();
}
$message = $result->fetch_assoc();
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

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
 
<?php include('head.php'); ?>
   
  </head>
  <body>
    <div class="wrapper">
      <!-- Sidebar -->
      <?php include('studentnav.php'); ?>
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
  </body>
</html>
