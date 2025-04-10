<?php include('components/admin_logic.php');

// ADD QUESTION ==============================
if (isset($_POST['delete'])) {
  // SQL to delete all records from mst_result
  $sql = "DELETE FROM mst_result";
  
  // SQL to delete all records from mst_useranswer
  $sql0 = "DELETE FROM mst_useranswer";
  
  // SQL to delete all records from timer
  $sql1 = "DELETE FROM timer";
  
  // Execute the queries and check if all are successful
  if ($conn->query($sql) === TRUE &&
      $conn->query($sql0) === TRUE &&
      $conn->query($sql1) === TRUE) {
    echo '<script type="text/javascript">
      alert("Exam Initiated successfully!\nStudents can take their exams");
      </script>';
  } else {
    echo "Error Initiating Exams: " . $conn->error;
  }
}


  

// Process deletion if a delete button was clicked
if (isset($_POST['delete_subject'])) {
  // Retrieve the class, arm, and subject values directly from POST
  $class   = $_POST['class'];
  $arm     = $_POST['arm'];
  $subject = $_POST['subject'];

  // Prepare the DELETE statement including the subject in the WHERE clause
  $stmt = $conn->prepare("DELETE FROM question WHERE class = ? AND arm = ? AND subject = ?");
  if ($stmt === false) {
      echo "<p style='color: red;'>Error preparing statement: " . htmlspecialchars($conn->error) . "</p>";
  } else {
      // Bind the parameters as strings ("sss")
      $stmt->bind_param("sss", $class, $arm, $subject);
      if ($stmt->execute()) {
          echo "<p style='color: green;'>Subjects deleted for Class: " . htmlspecialchars($class) . ", Arm: " . htmlspecialchars($arm) . ", Subject: " . htmlspecialchars($subject) . ".</p>";
      } else {
          echo "<p style='color: red;'>Error deleting subjects: " . htmlspecialchars($stmt->error) . "</p>";
      }
      $stmt->close();
  }
}




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
                <h3 class="fw-bold mb-3">Add Question</h3>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                  <li class="breadcrumb-item active">CBT</li>
                  <li class="breadcrumb-item active">Add Question</li>
              </ol>
              </div>
           
            </div>

            <!-- BULK UPLOAD ============================ -->
            <div class="row">
             
             <div class="col-md-12">
               <div class="card card-round">
                 <div class="card-header">
                   <div class="card-head-row">
                     <div class="card-title">Upload Questions</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                 <form method="post" action="">
                    <div style="float: right;"><button type="submit" name="delete" class="btn btn-secondary"><span class="btn-label">
                    <i class="fa fa-play-circle"></i>Initiate Exam</button></div>
                    </form>
                   <div class="mb-4 mt-2">
                   
                   <h5>Bulk Upload Questions via CSV</h5>
                  <form id="csvUploadForm" action="upload_csv.php" method="post" enctype="multipart/form-data">
                    
                    <!-- CSV File Upload -->
                    <div class="row">
                      <div class="col-md-12">
                        <label for="csvFile" class="form-label">Select CSV File</label>
                        <input type="file" class="form-control" id="csvFile" name="csvFile" accept=".csv">
                        <div class="form-text">
                          CSV file should include: <strong>ID, Questions, Option 1, Option 2, Option 3, Option 4, Correct Answer</strong>
                        </div>
                      </div>
                    </div>

                    <!-- Dropdowns & Subject Input -->
                    <div class="row align-items-end g-2 mt-3">
                      <div class="col-md-2">
                        <label for="class" class="form-label">Class</label>
                        <select class="form-control" id="class" name="class" required>
                          <option value="">Select Class</option>
                          <?= $class_options ?>
                        </select>
                      </div>
                      
                      <div class="col-md-2">
                        <label for="arm" class="form-label">Arm</label>
                        <select class="form-control" id="arm" name="arm" required>
                          <option value="">Select Arm</option>
                          <?= $arm_options ?>
                        </select>
                      </div>
                      
                      <div class="col-md-2">
                        <label for="term" class="form-label">Term</label>
                        <select class="form-control" id="term" name="term" required>
                          <?= $term_options ?>
                        </select>
                      </div>
                      
                      <div class="col-md-2">
                        <label for="session" class="form-label">Session</label>
                        <select class="form-control" id="session" name="session" required>
                          <?= $session_options ?>
                        </select>
                      </div>
                      
                      <div class="col-md-4">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject" required>
                      </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="row mt-3">
                      <div class="col-md-8">
                        <button type="submit" class="btn btn-success"><span class="btn-label">
                        <i class="fa fa-cloud-upload-alt"></i>Upload CSV</button>
                      </div>
                    </div>

                    <!-- Download Template Link -->
                    <div class="mt-2">
                      <a href="download_template.php" class="btn btn-warning"><span class="btn-label">
                      <i class="fa fa-cloud-download-alt"></i>Download CSV Template</a>
                    </div>
                  </form>

                  <div id="errorMsg" class="alert alert-danger d-none"></div>
            
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
                     <div class="card-title">Uploaded Questions</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">
                  
                   <p>
                      <?php
                      // Query to get distinct class, arm, and subject values
                      $query  = "SELECT DISTINCT class, arm, subject FROM question ORDER BY class, arm, subject";
                      $result = mysqli_query($conn, $query);

                      $current_class = '';
                      while ($row = mysqli_fetch_assoc($result)) {
                          // Start a new section when class changes
                          if ($current_class != $row['class']) {
                              if ($current_class != '') {
                                  echo '</div>';
                              }
                              $current_class = $row['class'];
                              echo '<div class="mb-3">';
                              echo '<h6 class="border-bottom pb-1">' . htmlspecialchars($current_class) . ' - ' . htmlspecialchars($row['arm']) . '</h6>';
                          }
                          
                          // Display the arm and subject with a delete button on the far right.
                          // The form sends the class and arm values (used for deletion) when the button is clicked.
                          echo '<p class="ml-3" style="display: flex; justify-content: space-between; align-items: center;">';
                          echo '<span>' . htmlspecialchars($row['subject']) . '</span>';
                          echo '<form method="post" action="" style="margin: 0;">';
                          echo '<input type="hidden" name="class" value="' . htmlspecialchars($row['class']) . '">';
                          echo '<input type="hidden" name="arm" value="' . htmlspecialchars($row['arm']) . '">';
                          echo '<input type="hidden" name="subject" value="' . htmlspecialchars($row['subject']) . '">';  // Include subject here
                          echo '<button type="submit" name="delete_subject" class="btn btn-danger"><span class="btn-label">
                      <i class="fa fa-trash"></i></button>';
                          echo '</form>';
                          
                          echo '</p>';
                      }

                      // Close the last class section div if needed
                      if ($current_class != '') {
                          echo '</div>';
                      }

                      // Close the database connection
                      $conn->close();
                      ?>
                    </p>
            
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
