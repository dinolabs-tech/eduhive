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


// Handle AJAX request for fetching student details
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'fetch') {
  $student_id = $conn->real_escape_string($_POST['id']);
  $sql = "SELECT id, name, class, gender, session FROM students WHERE id = '$student_id'";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      echo json_encode($row);
  } else {
      echo json_encode(null);
  }
  exit; // Stop further PHP execution as we've responded with JSON
}

// Handle form submission for adding or editing a student
if (isset($_POST['submit'])) {
  $regno = $_POST['regno'];
  $studentname = $_POST['studentname'];
  $sex = $_POST['sex'];
  $studentclass = $_POST['studentclass'];
  $csession = $_POST['csession'];
  $vbalance = $_POST['vbalance'];
  $passcode = $_POST['passcode'];

  if (isset($_POST['edit_id'])) {
      // Update existing student
      $stmt = $conn->prepare("UPDATE tuck SET studentname = ?, sex = ?, studentclass = ?, csession = ?, vbalance = ?, passcode = ? WHERE regno = ?");
      $stmt->bind_param("sssssis", $studentname, $sex, $studentclass, $csession, $vbalance, $passcode, $regno);
      $action_message = "Student updated successfully!";
  } else {
      // Add new student
      $stmt = $conn->prepare("INSERT INTO tuck (regno, studentname, sex, studentclass, csession, vbalance, passcode) VALUES (?, ?, ?, ?, ?, ?, ?)");
      $stmt->bind_param("sssssis", $regno, $studentname, $sex, $studentclass, $csession, $vbalance, $passcode);
      $action_message = "Student added successfully!";
  }

  if ($stmt->execute()) {
      $success = $action_message;
     // Redirect back to the page after processing
  header("Location: " . $_SERVER['PHP_SELF']);
  exit();
  } else {
      $error = "Error: " . $stmt->error;
  }
  $stmt->close();
}

// Handle delete request
if (isset($_GET['delete_id'])) {
  $id = $_GET['delete_id'];
  $conn->query("DELETE FROM tuck WHERE regno='$id'");
  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}

// Get student details if editing
$student = null;
if (isset($_GET['regno'])) {
  $id = $_GET['regno'];
  $result = $conn->query("SELECT * FROM tuck WHERE regno='$id'");
  $student = $result->fetch_assoc();
}

// Fetch all students for display
$students = $conn->query("SELECT * FROM tuck");

// Handle top-up (update balance and insert transaction)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['amount'])) {
  $regno = $_POST['regno'];
  $studentname = $_POST['studentname'];
  $amount = $_POST['amount'];
  
  // Fetch current balance
  if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['amount'])) {
      $regno = $_POST['regno'];
      $studentname = $_POST['studentname'];
      $amount = (float)$_POST['amount']; // cast amount as float
      
      // Fetch current balance
      $result = $conn->query("SELECT vbalance FROM tuck WHERE regno='$regno'");
      if ($result && $result->num_rows > 0) {
          $student = $result->fetch_assoc();
          $currentBalance = (float)$student['vbalance']; // ensure proper type
          $newBalance = $currentBalance + $amount;
  
          // Update balance in the tuck table (use "d" for double if vbalance is decimal)
          $updateStmt = $conn->prepare("UPDATE tuck SET vbalance=? WHERE regno=?");
          $updateStmt->bind_param("ds", $newBalance, $regno);
          $updateStmt->execute();
          $updateStmt->close();
  
          // Insert transaction record in transactiondetails table
          $transactionStmt = $conn->prepare("INSERT INTO transactiondetails (transactionID, studentname, productname, description, units, amount, transactiondate, cashier) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)");
          $productname = 'Top-Up';  
          $description = 'Balance Top-Up';  
          $units = 1;  
          $cashier = 'Admin'; 
          $transactionStmt->bind_param("ssssdis", $regno, $studentname, $productname, $description, $units, $amount, $cashier);
          $transactionStmt->execute();
          $transactionStmt->close();
      } else {
          exit("Student not found for regno: " . htmlspecialchars($regno));
      }
      
      // Redirect back to the page after processing
      header("Location: " . $_SERVER['PHP_SELF']);
      exit();
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
                <nts class="fw-bold mb-3">Tuck Shop</h3>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                  <li class="breadcrumb-item active">Tuck Shop</li>
              </ol>
              </div>
           
            </div>

            <!-- BULK UPLOAD ============================ -->
            <div class="row">
             
             <div class="col-md-12">
               <div class="card card-round">
                 <div class="card-header">
                   <div class="card-head-row">
                     <div class="card-title">Register Students</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">

                   <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
                            <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

                            <form method="POST" class="p-3 border rounded bg-white" id="studentForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="text" name="regno" id="id" class="form-control" placeholder="Reg No" value="<?= isset($student) ? $student['regno'] : '' ?>" required onkeyup="fetchStudentDetails(this.value)">
                                        <br>
                                        <input type="text" name="studentname" id="name" class="form-control" placeholder="Student Name" value="<?= isset($student) ? $student['studentname'] : '' ?>" required>
                                        <br>
                                        <select name="sex" id="gender" class="form-control form-select" required readonly>
                                            <option value="">Select Sex</option>
                                            <option value="Male" <?= isset($student) && $student['sex'] == 'Male' ? 'selected' : '' ?>>Male</option>
                                            <option value="Female" <?= isset($student) && $student['sex'] == 'Female' ? 'selected' : '' ?>>Female</option>
                                        </select>
                                        <br>
                                        <input type="text" name="studentclass" id="class" class="form-control" placeholder="Class" value="<?= isset($student) ? $student['studentclass'] : '' ?>" required>
                                    </div>
                                    <br>
                                    <div class="col-md-6">
                                        <input type="text" name="csession" id="session" class="form-control" placeholder="Session" value="<?= isset($student) ? $student['csession'] : '' ?>" >
                                        <br>
                                        <input type="text" name="vbalance" class="form-control" placeholder="Balance" value="<?= isset($student) ? $student['vbalance'] : '' ?>" <?= isset($student) ? 'readonly' : '' ?> required>
                                        <br>
                                        <input type="hidden" name="passcode" class="form-control" placeholder="Passcode" value="<?= isset($student) ? $student['passcode'] : '' ?>" >
                                    </div>
                                </div>
                                <br>
                                <button type="submit" name="submit" class="btn btn-success"><?= isset($student) ? 'Update Student' : 'Register Student' ?> </button> &nbsp;
                                <button type="reset" class="btn btn-secondary"> <span class="btn-label">
                                <i class="fa fa-undo"></i>Reset</button>
                                <?php if (isset($student)): ?>
                                    <input type="hidden" name="edit_id" value="<?= $student['regno'] ?>">
                                <?php endif; ?>
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
                     <div class="card-title">Registered Students</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">

                   <div class="table-responsive"> 
                         <!-- Display subjects -->
                         <table
                        id="multi-filter-select"
                        class="display table table-striped table-hover">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Reg No</th>
                                <th>Name</th>
                                <th>Sex</th>
                                <th>Class</th>
                                <th>Session</th>
                                <th>Balance</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $students->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['regno'] ?></td>
                                    <td><?= $row['studentname'] ?></td>
                                    <td><?= $row['sex'] ?></td>
                                    <td><?= $row['studentclass'] ?></td>
                                    <td><?= $row['csession'] ?></td>
                                    <td><?= $row['vbalance'] ?></td>
                                    <td>
                                        <a href="?regno=<?= $row['regno'] ?>" class="btn btn-sm btn-warning"><span class="btn-label">
                                        <i class="fa fa-edit"></i></a>
                                        <a href="?delete_id=<?= $row['regno'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this student?')"><span class="btn-label">
                                        <i class="fa fa-trash"></i></a>
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#topupModal" data-regno="<?= $row['regno'] ?>" data-studentname="<?= $row['studentname'] ?>"><span class="btn-label">
                                        <i class="fa fa-wallet"></i></button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                 </div>
                 
                   </div>
                 </div>
               </div>
             </div>
            </div>

                       <!-- Modal for Top-Up -->
            <div class="modal fade" id="topupModal" tabindex="-1" aria-labelledby="topupModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="topupModalLabel">Top-Up Student Balance</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="topupForm" method="POST">
                                <input type="hidden" id="regno" name="regno">
                                <input type="hidden" id="studentname" name="studentname">
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Amount to Top-Up</label>
                                    <input type="number" class="form-control" id="amount" name="amount" required>
                                </div>
                                <button type="submit" class="btn btn-primary"><span class="btn-label">
                                <i class="fa fa-wallet"></i>Recharge</button>
                            </form>
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
                // Set values in the modal when Top-Up button is clicked
                $(document).ready(function() {
            $('#topupModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget); // Button that triggered the modal
                var regno = button.data('regno');      // Extract info from data-* attributes
                var studentname = button.data('studentname');
                var modal = $(this);
                modal.find('#regno').val(regno);
                modal.find('#studentname').val(studentname);
            });
            });

            </script>

            <script>
                                    function fetchStudentDetails(studentId) {
                                        if (studentId.length > 0) {
                                            $.ajax({
                                                type: 'POST',
                                                url: '', 
                                                data: {id: studentId, action: 'fetch'},
                                                success: function(response) {
                                                    var student = JSON.parse(response);
                                                    if (student) {
                                                        $('#name').val(student.name);
                                                        $('#class').val(student.class);
                                                        $('#gender').val(student.gender);
                                                        $('#session').val(student.session);
                                                    } else {
                                                        $('#name, #class, #gender, #session').val('');
                                                    }
                                                }
                                            });
                                        } else {
                                            $('#name, #class, #gender, #session').val('');
                                        }
                                    }
            </script>

  </body>
</html>
