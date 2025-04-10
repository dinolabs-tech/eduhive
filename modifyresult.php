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


// Handle Edit Request for result
if (isset($_POST['edit'])) {
    $id      = $_POST['id'];
    $subject = $_POST['subject'];
    $term    = $_POST['term'];
    $session = $_POST['session'];

    $query = "SELECT * FROM mastersheet 
              WHERE id='$id' 
                AND subject='$subject' 
                AND term='$term' 
                AND csession='$session'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        echo "Record not found.";
    }
}



// Handle Update Request
if (isset($_POST['update'])) {
  $id = $_POST['id'];
  $subject = $_POST['subject'];
  $term = $_POST['term'];
  $session = $_POST['session'];
  $exam = $_POST['exam'];

  // Calculate total and average
  $ca1 = $_POST['ca1'];
  $ca2 = $_POST['ca2'];
  $lastcum = $_POST['lastcum'];

  $total = $ca1 + $ca2 + $exam;

  // Adjust average calculation
  if ($lastcum == 0) {
      $average = $total; // Bring total score forward
  } else {
      $average = ($lastcum + $total) / 2;
  }

  // Determine grade
  if ($average >= 75) {
      $grade = 'A';
      $remark = 'EXCELLENT';
  } elseif ($average >= 65) {
      $grade = 'B';
      $remark = 'VERY GOOD';
  } elseif ($average >= 50) {
      $grade = 'C';
      $remark = 'GOOD';
  } elseif ($average >= 45) {
      $grade = 'D';
      $remark = 'FAIR';
  } elseif ($average >= 40) {
      $grade = 'E';
      $remark = 'POOR';
  } else {
      $grade = 'F';
      $remark = 'VERY POOR';
  }

  // Update the record based on id, subject, term, and session
  $conn->query("UPDATE mastersheet SET 
      ca1='$ca1', 
      ca2='$ca2', 
      exam='$exam', 
      total='$total', 
      average='$average', 
      grade='$grade', 
      remark='$remark' 
      WHERE id='$id' AND subject='$subject' AND term='$term' AND csession='$session'");

  echo "<p style='color: green; text-align: center;'>Record updated successfully!</p>";
   // Redirect back to refresh the page (you can pass $message via session or GET if needed)
   header("Location: " . $_SERVER['PHP_SELF']);
   exit;
} else {
//    echo "Error updating record: " . $conn->error;
}

// Handle Search Request
$searchResults = null;
if (isset($_POST['search'])) {
  $searchTerm = $conn->real_escape_string($_POST['search_term']);
  $searchResults = $conn->query("SELECT * FROM mastersheet WHERE id LIKE '%$searchTerm%' OR name LIKE '%$searchTerm%'");
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
                <h3 class="fw-bold mb-3">Modify</h3>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                  <li class="breadcrumb-item active">Results</li>
                  <li class="breadcrumb-item active">Modify</li>
              </ol>
              </div>
           
            </div>

            <!-- SEARCH STUDENTS ============================ -->
            <div class="row">
             
              <div class="col-md-12">
                <div class="card card-round">
                  <div class="card-header">
                    <div class="card-head-row">
                      <div class="card-title">Modify</div>
                    </div>
                    </div>
                  <div class="card-body pb-0">
                    <div class="mb-4 mt-2">
                    
                    
                     <!-- Edit Form -->
        <?php if (isset($_POST['edit'])) { ?>
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="<?php echo $row['name']; ?>"class="form-control" disabled>
                </div>

                <div class="form-group">
                    <label for="ca1">CA</label>
                    <input class="form-control" type="text" id="ca1" name="ca1" value="<?php echo $row['ca1']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="ca2">Assignment</label>
                    <input class="form-control" type="text" id="ca2" name="ca2" value="<?php echo $row['ca2']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="exam">Exam</label>
                    <input class="form-control" type="text" id="exam" name="exam" value="<?php echo $row['exam']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="lastcum">LastCum</label>
                    <input class="form-control" type="text" id="lastcum" name="lastcum" value="<?php echo $row['lastcum']; ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="total">Total</label>
                    <input class="form-control" type="text" id="total" name="total" value="<?php echo $row['total']; ?>" disabled>
                </div>

                <div class="form-group">
                    <label for="average">Average</label>
                    <input class="form-control" type="text" id="average" name="average" value="<?php echo $row['average']; ?>" disabled>
                </div>

                <div class="form-group">
                    <label for="grade">Grade</label>
                    <input class="form-control" type="text" id="grade" name="grade" value="<?php echo $row['grade']; ?>" disabled>
                </div>

                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input class="form-control" type="text" id="subject" name="subject" value="<?php echo $row['subject']; ?>" disabled>
                </div>

                <div class="form-group">
                    <label for="remark">Remark</label>
                    <input class="form-control" type="text" id="remark" name="remark" value="<?php echo $row['remark']; ?>" disabled>
                </div>

                    <!--hidden inputs-->
                    <input type="hidden" name="subject" value="<?php echo $row['subject']; ?>">
                    <input type="hidden" name="term" value="<?php echo $row['term']; ?>">
                    <input type="hidden" name="session" value="<?php echo $row['csession']; ?>">
                    <!--hidden inputs ends here-->

                <button type="submit" name="update" class="btn btn-success">Save Changes</button>
            </form>
        <?php } ?>

        <br/>
        <!-- Search Form -->
        <form method="POST">
            <div class="form-group col-md-6">
                <input type="text" id="search_term" name="search_term" placeholder="Enter ID or Name" class="form-control" required>
            </div>
            <button type="submit" name="search" class="btn btn-success">
            <span class="btn-label">
            <i class="fa fa-search"></i>
            Search</button>
            
        </form>

        <!-- Search Results -->
        <?php if (isset($searchResults)) { ?>
            <div class="table-responsive"> <br>
            <table
                        id="multi-filter-select"
                        class="display table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Subject</th>
                        <th>CA</th>
                        <th>Assign.</th>
                        <th>Exam</th>
                        <th>LastCum</th>
                        <th>Total</th>
                        <th>AVG.</th>
                        <th>Grade</th>
                        <th>Remark</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($record = $searchResults->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $record['id']; ?></td>
                            <td><?php echo $record['name']; ?></td>
                            <td><?php echo $record['subject']; ?></td>
                            <td><?php echo $record['ca1']; ?></td>
                            <td><?php echo $record['ca2']; ?></td>
                            <td><?php echo $record['exam']; ?></td>
                            <td><?php echo $record['lastcum']; ?></td>
                            <td><?php echo $record['total']; ?></td>
                            <td><?php echo $record['average']; ?></td>
                            <td><?php echo $record['grade']; ?></td>
                            <td><?php echo $record['remark']; ?></td>
                            <td>
                                <form style="display: inline;" method="POST">
                                <input type="hidden" name="id" value="<?php echo $record['id']; ?>">
    <input type="hidden" name="subject" value="<?php echo $record['subject']; ?>">
    <input type="hidden" name="term" value="<?php echo $record['term']; ?>">
    <input type="hidden" name="session" value="<?php echo $record['csession']; ?>">
                                    <button type="submit" name="edit" class="btn btn-warning">
                                    <span class="btn-label">
                                    <i class="fa fa-edit"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            </div>
        <?php } ?>  
                  

                    </div>
                  </div>
                </div>
              
              </div>
            </div>

          
           
           
          </div>
        </div>
        <script> 
    document.querySelector('button[type="reset"]').addEventListener('click', function() {
    document.getElementById('myForm').reset();
});

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
