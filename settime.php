<?php include('components/admin_logic.php');

// ADD QUESTION ==============================
// Fetch classes
$class_options = "";
$class_result = $conn->query("SELECT class FROM class");
if ($class_result) {
    while ($row = $class_result->fetch_assoc()) {
        $class_options .= "<option value='" . htmlspecialchars($row['class']) . "'>" . htmlspecialchars($row['class']) . "</option>";
    }
} else {
    die("Error fetching class: " . $conn->error);
}

// Fetch arms
$arm_options = "";
$arm_result = $conn->query("SELECT arm FROM arm");
if ($arm_result) {
    while ($row = $arm_result->fetch_assoc()) {
        $arm_options .= "<option value='" . htmlspecialchars($row['arm']) . "'>" . htmlspecialchars($row['arm']) . "</option>";
    }
} else {
    die("Error fetching arm: " . $conn->error);
}

// Fetch current term
$term_options = "";
$term_result = $conn->query("SELECT cterm FROM currentterm WHERE id=1");
if ($term_result) {
    $row = $term_result->fetch_assoc();
    $term_options = "<option value='" . htmlspecialchars($row['cterm']) . "'>" . htmlspecialchars($row['cterm']) . "</option>";
} else {
    die("Error fetching current term: " . $conn->error);
}

// Fetch current session
$session_options = "";
$session_result = $conn->query("SELECT csession FROM currentsession WHERE id=1");
if ($session_result) {
    $row = $session_result->fetch_assoc();
    $session_options = "<option value='" . htmlspecialchars($row['csession']) . "'>" . htmlspecialchars($row['csession']) . "</option>";
} else {
    die("Error fetching current session: " . $conn->error);
}

// Process form submissions for insert, update and delete
if (isset($_POST['action'])) {
    if ($_POST['action'] == 'insert') {
        // Insert new record
        // $id       = $_POST['id'];
        $class    = $_POST['class'];
        $arm      = $_POST['arm'];
        $term     = $_POST['term'];
        $session  = $_POST['session'];
        $testdate = $_POST['testdate'];
        $testtime = $_POST['testtime'];
        
        $sql = "INSERT INTO cbtadmin (class, arm, term, session, testdate, testtime)
                VALUES ('$class', '$arm', '$term', '$session', '$testdate', '$testtime')";
        if ($conn->query($sql) === TRUE) {
            $msg = "Record inserted successfully.";
        } else {
            $msg = "Error inserting record: " . $conn->error;
        }
    } elseif ($_POST['action'] == 'update') {
        // Update existing record
        $id       = $_POST['id'];
        $class    = $_POST['class'];
        $arm      = $_POST['arm'];
        $term     = $_POST['term'];
        $session  = $_POST['session'];
        $testdate = $_POST['testdate'];
        $testtime = $_POST['testtime'];
        
        $sql = "UPDATE cbtadmin 
                SET class='$class', arm='$arm', term='$term', session='$session', testdate='$testdate', testtime='$testtime'
                WHERE id='$id'";
        if ($conn->query($sql) === TRUE) {
            $msg = "Record updated successfully.";
        } else {
            $msg = "Error updating record: " . $conn->error;
        }
    } elseif ($_POST['action'] == 'delete') {
        // Delete record
        $id = $_POST['id'];
        $sql = "DELETE FROM cbtadmin WHERE id='$id'";
        if ($conn->query($sql) === TRUE) {
            $msg = "Record deleted successfully.";
        } else {
            $msg = "Error deleting record: " . $conn->error;
        }
    }
}

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
                <h3 class="fw-bold mb-3">Set Exam Time</h3>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                  <li class="breadcrumb-item active">CBT</li>
                  <li class="breadcrumb-item active">Set Exam Time</li>
              </ol>
              </div>
           
            </div>

            <!-- BULK UPLOAD ============================ -->
            <div class="row">
             
             <div class="col-md-12">
               <div class="card card-round">
                 <div class="card-header">
                   <div class="card-head-row">
                     <div class="card-title">Set Exam Time</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">
                   
                   <?php if(isset($msg)) { ?>
                    <div class="alert alert-info"><?php echo $msg; ?></div>
                  <?php } ?>

                  <?php
                  // Check if we are editing a record – if so, display the update form
                  if (isset($_GET['edit'])) {
                      $edit_id = $_GET['edit'];
                      $sql = "SELECT * FROM cbtadmin WHERE id='$edit_id'";
                      $result = $conn->query($sql);
                      if ($result && $result->num_rows > 0) {
                          $row = $result->fetch_assoc();
                          ?>
                          <h2>Edit Record</h2>
                          <form method="post" action="">
                            <!-- Hidden fields to indicate update -->
                            <div class="row g-3 mb-3">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            
                            <div class="col-md-2">
                              <input type="text" name="class" class="form-control" value="<?php echo $row['class']; ?>" required>
                            </div>
                            <div class="col-md-2">
                              <input type="text" name="arm" class="form-control" value="<?php echo $row['arm']; ?>" required>
                            </div>
                            <div class="col-md-2">
                              <select name="term" class="form-control form-select" required>
                                <option value="1st Term" <?php if($row['term'] == "1st Term") echo 'selected'; ?>>1st Term</option>
                                <option value="2nd Term" <?php if($row['term'] == "2nd Term") echo 'selected'; ?>>2nd Term</option>
                                <option value="3rd Term" <?php if($row['term'] == "3rd Term") echo 'selected'; ?>>3rd Term</option>
                            </select>

                            </div>
                            <div class="col-md-2">
                              <input type="text" name="session" class="form-control" value="<?php echo $row['session']; ?>" required>
                            </div>
                            <div class="col-md-2">
                              <input type="date" id="testdate" name="testdate" class="form-control" value="<?php echo $row['testdate']; ?>" required>

                            </div>
                            <div class="col-md-2">
                              <input type="number" name="testtime" class="form-control" value="<?php echo $row['testtime']; ?>" required>
                            </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Update</button>
                            <a href="settime.php" class="btn btn-secondary">Cancel</a>
                          </form>
                          <?php
                      } else {
                         
                      }
                  } else {
                      // Otherwise, display the insertion form
                      ?>
                      <form method="post" action="">
                        <!-- Hidden field to indicate insert -->
                        <input type="hidden" name="action" value="insert">
                        <div class="row g-3 mb-3">
                          <div class="col-md-2">
                            <select class="form-select" id="class" name="class" required>
                              <option value="">Select Class</option>
                              <?= $class_options ?>
                            </select>
                          </div>
                          <div class="col-md-2">
                            <select class="form-select" id="arm" name="arm" required>
                              <option value="">Select Arm</option>
                              <?= $arm_options ?>
                            </select>
                          </div>
                          <div class="col-md-2">
                            <select class="form-select" id="term" name="term" required>
                              <option value="1st Term">1st Term</option>
                              <option value="2nd Term">2nd Term</option>
                              <option value="3rd Term">3rd Term</option>
                            </select>

                          </div>
                          <div class="col-md-2">
                            <select class="form-select" id="session" name="session" required>
                              <?= $session_options ?>
                            </select>
                          </div>

                          <div class="col-md-2">
                            <input type="date" id="testdate" name="testdate" class="form-control">
                          </div>
                          <div class="col-md-2">
                            <input type="number" name="testtime" class="form-control" placeholder="Test Time (Mins)">
                          </div>
                        </div>

                        <div class="row g-3 mb-3">
                          
                        </div>
                        <button type="submit" class="btn btn-success"><span class="btn-label">
                        <i class="fa fa-save"></i> Save</button>
                      </form>
                      <?php
                  }
                  ?>

                   </div>
                 </div>
               </div>
             </div>

  
             <div class="col-md-12">
               <div class="card card-round">
                 <div class="card-header">
                   <div class="card-head-row">
                     <div class="card-title">Records</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">
                   
                   <div class="table-responsive">
                      <table id="multi-filter-select" class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                          <tr>
                            <!-- <th>ID</th> -->
                            <th>Class</th>
                            <th>Arm</th>
                            <th>Term</th>
                            <th>Session</th>
                            <th>Test Date</th>
                            <th>Test Time</th>
                            <th>Actions</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                          // Retrieve and display all records
                          $sql = "SELECT * FROM cbtadmin";
                          $result = $conn->query($sql);
                          if ($result && $result->num_rows > 0) {
                              while ($row = $result->fetch_assoc()) {
                                  echo "<tr>";
                                  // echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                  echo "<td>" . htmlspecialchars($row['class']) . "</td>";
                                  echo "<td>" . htmlspecialchars($row['arm']) . "</td>";
                                  echo "<td>" . htmlspecialchars($row['term']) . "</td>";
                                  echo "<td>" . htmlspecialchars($row['session']) . "</td>";
                                  echo "<td>" . htmlspecialchars($row['testdate']) . "</td>";
                                  echo "<td>" . htmlspecialchars($row['testtime']) . "</td>";
                                  echo "<td>";
                                  echo "<a href='?edit=" . $row['id'] . "' class='btn btn-sm btn-warning me-2'>Edit</a>";
                                  echo "<form style='display:inline;' method='post' action='' onsubmit=\"return confirm('Are you sure you want to delete this record?');\">";
                                  echo "<input type='hidden' name='action' value='delete'>";
                                  echo "<input type='hidden' name='id' value='" . $row['id'] . "'>";
                                  echo "<button type='submit' class='btn btn-sm btn-danger'>Delete</button>";
                                  echo "</form>";
                                  echo "</td>";
                                  echo "</tr>";
                              }
                          } else {
                              echo "<tr><td colspan='8' class='text-center'>No records found.</td></tr>";
                          }
                          ?>
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


  </body>
</html>
