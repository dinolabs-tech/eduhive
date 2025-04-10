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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $date = date("m/d/Y", strtotime($_POST['date'])); // Format date to MM/dd/yyyy
  $title = $_POST['title'];
  $description = $_POST['description'];

  $stmt = $conn->prepare("INSERT INTO calendar (date, title, description) VALUES (?, ?, ?)");
  $stmt->bind_param("sss", $date, $title, $description);
  $stmt->execute();
  $stmt->close();
}

// Handle delete request
if (isset($_GET['delete_id'])) {
  $id = $_GET['delete_id'];
  $stmt = $conn->prepare("DELETE FROM calendar WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->close();
}

// Fetch events
$sql = "SELECT id, date, title, description FROM calendar";
$result = $conn->query($sql);
$events = [];
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
      $events[] = $row;
  }
}



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
                <nts class="fw-bold mb-3">Calendar</h3>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                  <li class="breadcrumb-item active">Calendar</li>
              </ol>
              </div>
           
            </div>

            <!-- BULK UPLOAD ============================ -->
            <div class="row">
             
             <div class="col-md-12">
               <div class="card card-round">
                 <div class="card-header">
                   <div class="card-head-row">
                     <div class="card-title">Academic Calendar</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">
                    
                    <form method="POST">

                      <input class="form-control" type="date" id="date" name="date" required>
<br>
                      <input class="form-control" type="text" id="title" name="title" placeholder="Title" required>
<br>
                      <textarea class="form-control" id="description" name="description" rows="4" placeholder="Description..." required></textarea>
<br>
                      <button class="btn btn-success" type="submit"><span class="btn-label">
                      <i class="fa fa-save"></i> Add Event</button>
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
                     <div class="card-title">Register</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">
                    
                    <div class="table-responsive"> 
                      <table
                        id="multi-filter-select"
                        class="display table table-striped table-hover">
                          
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($event['date']); ?></td>
                                <td><?php echo htmlspecialchars($event['title']); ?></td>
                                <td><?php echo htmlspecialchars($event['description']); ?></td>
                                <td class="actions">
                                    <button class="btn btn-warning" onclick="editEvent(<?php echo $event['id']; ?>, '<?php echo htmlspecialchars($event['date']); ?>', '<?php echo htmlspecialchars($event['title']); ?>', '<?php echo htmlspecialchars($event['description']); ?>')"><span class="btn-label">
                                    <i class="fa fa-edit"></i></button>
                                    <button class="btn btn-danger" onclick="confirmDelete(<?php echo $event['id']; ?>)"><span class="btn-label">
                                    <i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                      </div>

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
  
  
   <script>
    function editEvent(id, date, title, description) {
        document.getElementById('date').value = new Date(date).toISOString().slice(0, 10);
        document.getElementById('title').value = title;
        document.getElementById('description').value = description;

        const form = document.querySelector('form');
        form.action = `?edit_id=${id}`;
        form.querySelector('button[type="submit"]').textContent = 'Update Event';
    }

    function confirmDelete(id) {
        if (confirm("Are you sure you want to delete this event?")) {
            window.location.href = `?delete_id=${id}`;
        }
    }
</script>

  </body>
</html>
