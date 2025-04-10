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

// Handle AJAX fetch for student details
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
  if ($_POST['action'] == 'fetch') {
      $student_id = $conn->real_escape_string($_POST['date']);
      $sql = "SELECT id, name, class, arm, term, gender, session, date, depositor, mobile, amount, narration FROM prebursary WHERE date = '$student_id'";
      $result = $conn->query($sql);

      if ($result->num_rows > 0) {
          $row = $result->fetch_assoc();
          echo json_encode($row);
      } else {
          echo json_encode(null);
      }
      $conn->close();
      exit; // Stop further PHP execution as we've responded with JSON
  }
  
 // Handle approval action
elseif ($_POST['action'] == 'approve') {
  // Sanitize the provided date (used here as unique identifier)
  $student_date = $conn->real_escape_string($_POST['date']);

  // Update the prebursary record to set its status to approved (1)
  $sql_update = "UPDATE prebursary SET status = 1 WHERE date = '$student_date'";
  if ($conn->query($sql_update) === TRUE) {
      // Fetch the student details to get the ef_id and amount deposited
      $sql_student = "SELECT id, amount FROM prebursary WHERE date = '$student_date'";
      $result = $conn->query($sql_student);
      if ($result->num_rows > 0) {
          $student = $result->fetch_assoc();
          $ef_id = $student['id'];
          $amount = $student['amount'];
          
          // Insert into payments with remarks "school fees"
          $remarks = "school fees";
          $sql_insert = "INSERT INTO payments (ef_id, amount, remarks) VALUES ('$ef_id', '$amount', '$remarks')";
          if ($conn->query($sql_insert) === TRUE) {
              // Return a JSON response instead of a redirect
              echo json_encode([
                  "status" => "success",
                  "message" => "Transaction Approved!"
              ]);
              $conn->close();
              exit;
          } else {
              echo json_encode([
                  "status" => "error",
                  "message" => "Error inserting payment record: " . $conn->error
              ]);
              $conn->close();
              exit;
          }
      } else {
          echo json_encode([
              "status" => "error",
              "message" => "Student details not found for the provided date."
          ]);
          $conn->close();
          exit;
      }
  } else {
      echo json_encode([
          "status" => "error",
          "message" => "Error updating record: " . $conn->error
      ]);
      $conn->close();
      exit;
  }
}


// Handle AJAX request to fetch updated table rows
elseif ($_POST['action'] == 'fetch_table') {
  $students = $conn->query("SELECT * FROM prebursary WHERE status = 0");
  $html = '';
  while ($row = $students->fetch_assoc()) {
      $html .= '<tr>';
      $html .= '<td>' . $row['id'] . '</td>';
      $html .= '<td>' . $row['name'] . '</td>';
      $html .= '<td>' . $row['gender'] . '</td>';
      $html .= '<td>' . $row['class'] . '</td>';
      $html .= '<td>' . $row['arm'] . '</td>';
      $html .= '<td>' . $row['term'] . '</td>';
      $html .= '<td>' . $row['session'] . '</td>';
      $html .= '<td>' . $row['depositor'] . '</td>';
      $html .= '<td>' . $row['mobile'] . '</td>';
      $html .= '<td>' . $row['amount'] . '</td>';
      $html .= '<td>' . $row['narration'] . '</td>';
      $html .= '<td>';
      $html .= '<a href="?date=' . $row['date'] . '" class="btn btn-sm btn-warning">Edit</a> ';
      $html .= '<a href="?delete_id=' . $row['date'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to delete this student?\')">Delete</a>';
      $html .= '</td>';
      $html .= '</tr>';
  }
  echo $html;
  $conn->close();
  exit;
}

}

// Fetch all students for display
$students = $conn->query("SELECT * FROM prebursary WHERE status = 0");

// Get student details if editing
$student = null;
if (isset($_GET['date'])) {
  $id = $_GET['date'];
  $result = $conn->query("SELECT * FROM prebursary WHERE date='$id'");
  $student = $result->fetch_assoc();
}

// Handle delete request
if (isset($_GET['delete_id'])) {
  $id = $_GET['delete_id'];
  $conn->query("DELETE FROM prebursary WHERE date='$id'");
  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
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
<style>
     
     #imageSection {
       display: flex;
       justify-content: center;
       align-items: center;
       min-height: 50vh; /* Full viewport height */
     }
    
     /* Ensure the image maintains its maximum dimensions */
     #studentImage {
       max-width: 300px;
       max-height: 300px;
       display: none; /* Hide by default; shown when a student is selected */
     }
     </style>
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
                <nts class="fw-bold mb-3">Approve Payments</h3>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                  <li class="breadcrumb-item active">Bursary</li>
                  <li class="breadcrumb-item active">Approve Payments</li>
              </ol>
              </div>
           
            </div>

 

            <!-- BULK UPLOAD ============================ -->
            <div class="row">
             
             <div class="col-md-12">
               <div class="card card-round">
                 <div class="card-header">
                   <div class="card-head-row">
                     <div class="card-title">Payments</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">

                      <p> 

                      <p>
                              <!-- Image Section -->
                        <div id="imageSection" style="margin-left: 15%;">
                          <!-- The image will only be shown if a student is selected -->
                          <img id="studentImage" src="" alt="Student Image">
                        </div>
                      </p>
                          <!-- Registration Form -->
                          <form method="post" class="row g-3" id="studentForm" onreset="setTimeout(() => { 
                                    document.getElementById('date').value = ''; 
                                    document.getElementById('studentImage').style.display = 'none'; 
                                  }, 0);">


                          <div class="col-md-2">
                          <input class="form-control" type="text" id="id" name="id" required onkeyup="fetchStudentDetails(this.value)" placeholder="Student ID">
                          </div>

                          <div class="col-md-6">
                          <input class="form-control" type="text" id="name" name="name" readonly placeholder="Students Name">
                          </div>

                          <div class="col-md-2">
                          <input class="form-control" type="text" id="class" name="class" readonly placeholder="Class">
                          </div>

                          <div class="col-md-2">
                          <input class="form-control" type="text" id="arm" name="arm" readonly placeholder="Arm">
                          </div>

                          <div class="col-md-2">
                          <input class="form-control" type="text" id="term" name="term" readonly placeholder="Term">
                          </div>

                          <div class="col-md-4">
                          <input class="form-control" type="text" id="gender" name="gender" readonly placeholder="Gender">
                          </div>

                          <div class="col-md-4">
                          <input class="form-control" type="text" id="session" name="session" readonly placeholder="Academic Session">
                          </div>

                          <div class="col-md-8">
                          <input class="form-control" type="text" id="depositor" name="depositor" required readonly placeholder="Depositor's Name">
                          </div>

                          <div class="col-md-2">
                          <input class="form-control" type="text" id="mobile" name="mobile" required readonly placeholder="Mobile">
                          </div>

                          <div class="col-md-2">
                          <input class="form-control" type="text" id="amount" name="amount" required readonly placeholder="Amount">
                          </div>

                          <div class="col-md-2">
                          <input class="form-control" type="text" id="narration" name="narration" required readonly placeholder="Narration">
                          </div>

                          <input class="form-control" type="hidden" id="date" name="date" value="<?= isset($student) ? $student['date'] : '' ?>" required onchange="fetchStudentDetails(this.value)"><br>

                          <input class="form-control" type="hidden" name="action" value="approve">

                              <button type="submit" class="btn btn-success"> <span class="btn-label">
                              <i class="fa fa-check-circle"></i>Confirm Deposit</button>

                              <button type="reset" class="btn btn-dark"><span class="btn-label">
                              <i class="fa fa-undo"></i>Reset</button>
                          </form>
                    </p>
                 
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
                     <div class="card-title">Deposits</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">
                    <div class="table-responsive">
                   <table
                        id="multi-filter-select"
                        class="display table table-striped table-hover">
                          <thead class="bg-primary text-white">
                              <tr>
                                  <th>Reg No</th>
                                  <th>Name</th>
                                  <th>Sex</th>
                                  <th>Class</th>
                                  <th>Arm</th>
                                  <th>Term</th>
                                  <th>Session</th>
                                  <th>Depositor</th>
                                  <th>Mobile</th>
                                  <th>Amount</th>
                                  <th>Narration</th>
                                  <th>Actions</th>
                              </tr>
                          </thead>
                          <tbody>
                              <?php while ($row = $students->fetch_assoc()): ?>
                                  <tr>
                                      <td><?= $row['id'] ?></td>
                                      <td><?= $row['name'] ?></td>
                                      <td><?= $row['gender'] ?></td>
                                      <td><?= $row['class'] ?></td>
                                      <td><?= $row['arm'] ?></td>
                                      <td><?= $row['term'] ?></td>
                                      <td><?= $row['session'] ?></td>
                                      <td><?= $row['depositor'] ?></td>
                                      <td><?= $row['mobile'] ?></td>
                                      <td><?= $row['narration'] ?></td>
                                      <td><?= $row['amount'] ?></td>
                                      
                                      <td>
                                          <a href="?date=<?= $row['date'] ?>" class="btn btn-sm btn-warning"><span class="btn-label">
                                          <i class="fa fa-edit"></i></a>
                                          <a href="?delete_id=<?= $row['date'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this student?')"><span class="btn-label">
                                          <i class="fa fa-trash"></i></a>
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
  const studentImage = document.getElementById('studentImage');

  studentImage.addEventListener('click', () => {
    if (studentImage.requestFullscreen) {
      studentImage.requestFullscreen();
    } else if (studentImage.webkitRequestFullscreen) { /* Safari */
      studentImage.webkitRequestFullscreen();
    } else if (studentImage.msRequestFullscreen) { /* IE11 */
      studentImage.msRequestFullscreen();
    }
  });
</script>
<script>
  // Handle form submission via AJAX for approval
  $("#studentForm").on('submit', function(e) {
    e.preventDefault(); // Prevent full page reload

    $.ajax({
      type: "POST",
      url: "", // Same page
      data: $(this).serialize(), // Serialize the form including action=approve
      success: function(response) {
        try {
          var data = JSON.parse(response);
          if(data.status === "success") {
            alert(data.message);
            // Clear the form and hide the image
            $("#studentForm")[0].reset();
            $('#studentImage').hide();
            // Refresh the table to remove the approved record
            refreshTable();
          } else {
            alert("Error: " + data.message);
          }
        } catch (e) {
          alert("An unexpected error occurred.");
        }
      },
      error: function(xhr, status, error) {
        alert("AJAX error: " + error);
      }
    });
  });

  // Function to refresh the table via AJAX
  function refreshTable() {
    $.ajax({
      type: "POST",
      url: "",
      data: { action: 'fetch_table' },
      success: function(response) {
        $("table tbody").html(response);
      },
      error: function(xhr, status, error) {
        console.log("Error refreshing table: " + error);
      }
    });
  }
</script>




  <script>
    function fetchStudentDetails(studentId) {
      if (studentId.length > 0) {
        $.ajax({
          type: 'POST',
          url: '', // Same page, no need for a separate file
          data: { date: studentId, action: 'fetch' },
          success: function(response) {
            var student = JSON.parse(response);
            if (student) {
              $('#id').val(student.id);
              $('#name').val(student.name);
              $('#class').val(student.class);
              $('#arm').val(student.arm);
              $('#term').val(student.term);
              $('#gender').val(student.gender);
              $('#session').val(student.session);
              $('#date').val(student.date);
              $('#depositor').val(student.depositor);
              $('#mobile').val(student.mobile);
              $('#amount').val(student.amount);
              $('#narration').val(student.narration);
              $('#studentImage').attr('src', 'bursary/' + student.date).show();
            } else {
              $('#id, #name, #class, #arm, #term, #gender, #session, #date, #depositor, #mobile, #amount, #narration').val('');
              $('#studentImage').hide();
            }
          }
        });
      } else {
        $('#id, #name, #class, #arm, #term, #gender, #session, #date, #depositor, #mobile, #amount, #narration').val('');
        $('#studentImage').hide();
      }
    }

    $(document).ready(function() {
      var date = $('#date').val();
      if (date) {
        fetchStudentDetails(date);
      }
    });
  </script>

  </body>
</html>
