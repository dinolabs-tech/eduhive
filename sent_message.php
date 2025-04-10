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


// Now fetch sent messages for the logged-in user
$sql = "SELECT 
            m.id, 
            m.subject, 
            m.message, 
            m.to_user, 
            m.status, 
            m.date_created, 
            COALESCE(s.name, l.username) AS recipient 
        FROM mail m 
        LEFT JOIN students s ON m.to_user = s.id 
        LEFT JOIN login l ON m.to_user = l.id 
        WHERE m.from_user = '$user_id'
        ORDER BY m.date_created DESC";
$result = $conn->query($sql);

$messages = array();
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
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
                <h3 class="fw-bold mb-3">Sent Messages</h3>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="students.php">Home</a></li>
                  <li class="breadcrumb-item active">Sent Messages</li>
                </ol>
              </div>
            </div>

            <div class="col-md-12">
              <div class="card">
                <div class="card-header">
                  <h4 class="card-title">Sent Messages</h4>   
                </div>
                <div class="card-body">
                  <?php if(count($messages) > 0): ?>
                    <div class="table-responsive">
                    <table id="basic-datatables" class="table table-striped">
                      <thead>
                        <tr>
                          <th>Subject</th>
                          <th>To</th>
                          <th>Date</th>
                          <th>Status</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach($messages as $message): ?>
                          <tr>
                            <td><?php echo htmlspecialchars($message['subject']); ?></td>
                            <td><?php echo htmlspecialchars($message['recipient']); ?></td>
                            <td><?php echo htmlspecialchars($message['date_created']); ?></td>
                            <td>
                              <?php if($message['status'] == 0): ?>
                                <span class="badge badge-warning">Unread</span>
                              <?php else: ?>
                                <span class="badge badge-success">Read</span>
                              <?php endif; ?>
                            </td>
                            <td>
                              <!-- Instead of a link, we add a button that triggers a modal -->
                              <button 
                                type="button" 
                                class="btn btn-primary btn-sm view-message"
                                data-subject="<?php echo htmlspecialchars($message['subject'], ENT_QUOTES); ?>"
                                data-recipient="<?php echo htmlspecialchars($message['recipient'], ENT_QUOTES); ?>"
                                data-date="<?php echo htmlspecialchars($message['date_created'], ENT_QUOTES); ?>"
                                data-status="<?php echo $message['status']; ?>"
                                data-message="<?php echo htmlspecialchars($message['message'], ENT_QUOTES); ?>"
                              >
                                View
                              </button>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                    </div>
                  <?php else: ?>
                    <p>No sent messages found.</p>
                  <?php endif; ?>
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
    
    <!-- Bootstrap Modal to display message details -->
    <div class="modal fade" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="messageModalLabel">Message Details</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <h4 id="modalSubject"></h4>
            <p><strong>To:</strong> <span id="modalRecipient"></span></p>
            <p><strong>Date:</strong> <span id="modalDate"></span></p>
            <p><strong>Status:</strong> <span id="modalStatus"></span></p>
            <hr>
            <div id="modalMessage"></div>
          </div>
          <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>

          </div>
        </div>
      </div>
    </div>
    
    <script>
      // When a view button is clicked, populate and show the modal with message details
      $(document).on('click', '.view-message', function(){
        var subject = $(this).data('subject');
        var recipient = $(this).data('recipient');
        var date = $(this).data('date');
        var status = $(this).data('status') == 0 ? 'Unread' : 'Read';
        var message = $(this).data('message');
        
        $('#modalSubject').text(subject);
        $('#modalRecipient').text(recipient);
        $('#modalDate').text(date);
        $('#modalStatus').text(status);
        $('#modalMessage').html(message);
        
        $('#messageModal').modal('show');
      });
    </script>
  </body>
</html>
