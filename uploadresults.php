<?php
include('components/admin_logic.php');


// Fetch current term and session
$term_result = $conn->query("SELECT cterm FROM currentterm WHERE id=1");
$current_term = $term_result->fetch_assoc()['cterm'];

$session_result = $conn->query("SELECT csession FROM currentsession WHERE id=1");
$current_session = $session_result->fetch_assoc()['csession'];


// Initialize filter variables
$selected_subject = isset($_POST['subject']) ? $_POST['subject'] : '';
$selected_class = isset($_POST['class']) ? $_POST['class'] : '';
$selected_arm = isset($_POST['arm']) ? $_POST['arm'] : '';

// Initialize messages
$class_messages = [];
$average = [];

// Helper function to fetch average for a given term
function fetch_term_average($conn, $id, $selected_subject, $selected_class, $selected_arm, $term, $current_session) {
    $average = null; // Initialize the variable to avoid the warning

    $stmt = $conn->prepare("SELECT average FROM mastersheet WHERE ID=? AND Term=? AND Subject=? AND csession=? AND Class=? AND Arm=?");
    if ($stmt === false) {
        die('MySQL prepare error: ' . $conn->error);
    }

    $stmt->bind_param("ssssss", $id, $term, $selected_subject, $current_session, $selected_class, $selected_arm);
    if (!$stmt->execute()) {
        die('Execute error: ' . $stmt->error);
    }

    $stmt->bind_result($average);
    $stmt->fetch();
    $stmt->close();

    // Check if $average is null before applying ceil()
    return $average !== null ? ceil($average) : 0;
}



// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        if (isset($_POST['bulk_submit']) && isset($_FILES["csv_file"])) {
            // Handle CSV upload for bulk results
            if (($handle = fopen($_FILES["csv_file"]["tmp_name"], "r")) !== FALSE) {
                // Skip the header row
                fgetcsv($handle);

                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    // Assuming columns are: ID, Name, CA1, CA2, Exam
                    if (count($data) == 5) {
                        

                        $id = $data[0];
                        $name = $data[1];
                        $ca1 = $data[2];
                        $ca2 = $data[3];
                        $exam = $data[4];

                        // Calculate total
                        $total = ceil($ca1 + $ca2 + $exam);

                        // Fetch last cumulative average and calculate current average
                        $lastcum = 0;
                        if ($current_term == '2nd Term') {
                            $lastcum = fetch_term_average($conn, $id, $selected_subject, $selected_class, $selected_arm, '1st Term', $current_session);
                        } elseif ($current_term == '3rd Term') {
                            $lastcum = fetch_term_average($conn, $id, $selected_subject, $selected_class, $selected_arm, '2nd Term', $current_session);
                        }
                       // If lastcum is 0, bring the total forward; otherwise, calculate the average normally.
                        if ($lastcum == 0) {
                            $average = $total;
                        } else {
                            $average = ceil(($total + $lastcum) / 2);
                        }

                        // Determine grade and remark - old
                        //list($grade, $remark) = calculate_grade_remark($average);
                          // Determine grade and remark
                          if ($average >= 75 && $average <= 100) {
                            $grade = 'A';
                            $remark = 'EXCELLENT';
                        } elseif ($average >= 65 && $average <= 74) {
                            $grade = 'B';
                            $remark = 'VERY GOOD';
                        } elseif ($average >= 50 && $average <= 64) {
                            $grade = 'C';
                            $remark = 'GOOD';
                        } elseif ($average >= 45 && $average <= 49) {
                            $grade = 'D';
                            $remark = 'FAIR';
                        } elseif ($average >= 40 && $average <= 44) {
                            $grade = 'E';
                            $remark = 'POOR';
                        } else { // For averages below 40
                            $grade = 'F';
                            $remark = 'VERY POOR';
                        }
                        


                        // Placeholder for position, needs actual implementation for position logic
                        $position = 1;

                        // Insert or update record in mastersheet
                        $stmt = $conn->prepare("INSERT INTO mastersheet (id, name, ca1, ca2, exam, total, average, grade, subject, csession, class, arm, term, remark, position, lastcum) 
                                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                        
                        if ($stmt === false) {
                            die('MySQL prepare error: ' . $conn->error);
                        }

                        $stmt->bind_param("ssssssssssssssss", $id, $name, $ca1, $ca2, $exam, $total, $average, $grade, $selected_subject, $current_session, $selected_class, $selected_arm, $current_term, $remark, $position, $lastcum);

                        if ($stmt->execute()) {
                            $class_messages[] = "Record for ID $id processed successfully.";
                        } else {
                            $class_messages[] = "Error processing record for ID $id: " . $stmt->error;
                        }
                    }
                }
                fclose($handle);
            }
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Helper function to fetch unique values from a specific column in a table
function fetch_unique_values($conn, $column) {
    $query = "SELECT DISTINCT $column FROM mastersheet";
    $result = $conn->query($query);
    
    if ($result === false) {
        die('Error fetching unique values: ' . $conn->error);
    }

    $values = [];
    while ($row = $result->fetch_assoc()) {
        $values[] = $row[$column];
    }

    return $values;
}


// Handle delete request
if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    $subject = $_POST['subject'];
    $class = $_POST['class'];
    $arm = $_POST['arm'];
    
    $stmt = $conn->prepare("DELETE FROM mastersheet WHERE subject=? AND class=? AND arm=? AND csession=? AND term=?");
    $stmt->bind_param("sssss", $subject, $class, $arm, $current_session, $current_term);
    
    if ($stmt->execute()) {
        $class_messages[] = "Record for $subject in $class $arm deleted successfully.";
    } else {
        $Class_messages[] = "Error deleting record for $subject in $class $arm: " . $stmt->error;
    }
    $stmt->close();
  }




// Initialize $subjects_records to avoid "undefined variable" error
$subjects_records = [];

// Fetch unique values for filters
$subjects = fetch_unique_values($conn, "subject");
$classes = fetch_unique_values($conn, "class");
$arms = fetch_unique_values($conn, "arm");

// Fetch filtered subjects from mastersheet
$where_clauses = ["term = ?", "csession = ?"];
$params = [$current_term, $current_session];
$types = "ss";

if ($selected_class) {
    $where_clauses[] = "class = ?";
    $params[] = $selected_class;
    $types .= "s";
}

if ($selected_arm) {
    $where_clauses[] = "arm = ?";
    $params[] = $selected_arm;
    $types .= "s";
}

$where_clause = implode(" AND ", $where_clauses);

// Prepare the SQL query dynamically based on filters
$query = "SELECT * FROM mastersheet WHERE $where_clause GROUP BY subject";
$stmt = $conn->prepare($query);

if ($stmt) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $subjects_records = [];
$stmt->store_result(); // Store the result
$meta = $stmt->result_metadata(); // Get result metadata
$fields = $meta->fetch_fields(); // Fetch all fields

// Dynamically bind variables for each column
$bindVars = [];
$row = [];
foreach ($fields as $field) {
    $bindVars[] = &$row[$field->name];
}
call_user_func_array([$stmt, 'bind_result'], $bindVars);

// Fetch rows and store in an associative array
while ($stmt->fetch()) {
    $tempRow = [];
    foreach ($row as $key => $value) {
        $tempRow[$key] = $value;
    }
    $subjects_records[] = $tempRow;
}

    $stmt->close();
} else {
    echo "Error preparing query: " . $conn->error;
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
                <h3 class="fw-bold mb-3">Upload</h3>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                  <li class="breadcrumb-item active">Results</li>
                  <li class="breadcrumb-item active">Upload</li>
              </ol>
              </div>
           
            </div>

            <!-- BULK UPLOAD ============================ -->
            <div class="row">
             
             <div class="col-md-6">
               <div class="card card-round">
                 <div class="card-header">
                   <div class="card-head-row">
                     <div class="card-title">Upload Scores</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">
                 
                   <?php if (!empty($class_messages)) : ?>
    <div>
        <?php foreach ($class_messages as $class_message) : ?>
            <p><?php echo $class_message; ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
<select name="subject" id="subject" class="form-control form-select">
    <?php foreach ($subjects as $subject) : ?>
        <option value="<?php echo htmlspecialchars($subject); ?>"><?php echo htmlspecialchars($subject); ?></option>
    <?php endforeach; ?>
</select>
<br>
  <select name="class" id="class" class="form-control form-select">
    <?php foreach ($classes as $class) : ?>
        <option value="<?php echo htmlspecialchars($class); ?>"><?php echo htmlspecialchars($class); ?></option>
    <?php endforeach; ?>
</select>
<br>
  <select name="arm" id="arm" class="form-control form-select">
    <?php foreach ($arms as $arm) : ?>
        <option value="<?php echo htmlspecialchars($arm); ?>"><?php echo htmlspecialchars($arm); ?></option>
    <?php endforeach; ?>
</select>
<br>
    <input type="file" name="csv_file" id="csv_file" accept=".csv" class="form-control" required>
    <br>
    <button type="submit" name="bulk_submit" class="btn btn-success">
    <span class="btn-label">
    <i class="fa fa-cloud-upload-alt"></i>  
    Upload CSV</button>
    <br>
</form>
                   
                   </div>
                 </div>
               </div>
             </div>

              <!-- FILTER UPLOADED ============================ -->
             <div class="col-md-6">
               <div class="card card-round">
                 <div class="card-header">
                   <div class="card-head-row">
                     <div class="card-title">Filter Uploaded Scores</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                 <a href="download_result_template.php"><button style="float:right;margin-top:10px;" type="submit" name="bulk_upload" class="btn btn-primary">
                 <span class="btn-label">
                 <i class="fa fa-cloud-download-alt"></i>   
                 Download Score Template</button><br></a><br>
                   <div class="mb-4 mt-2">
                  <!-- Form for filtering -->
                    <form method="POST" action="">
                    <br>
                      <select name="class" id="class" class="form-control form-select">
                    <?php foreach ($classes as $class) : ?>
                        <option value="<?php echo htmlspecialchars($class); ?>"><?php echo htmlspecialchars($class); ?></option>
                    <?php endforeach; ?>
                </select>
                  <br>
                    <select name="arm" id="arm" class="form-control form-select">
                    <?php foreach ($arms as $arm) : ?>
                        <option value="<?php echo htmlspecialchars($arm); ?>"><?php echo htmlspecialchars($arm); ?></option>
                    <?php endforeach; ?>
                </select>
                <br>
                        <button type="submit" class="btn btn-secondary">
                        <span class="btn-label">
                          <i class="fa fa-filter"></i>  
                        Filter</button>
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
                      <div class="card-title">Uploaded Scores</div>
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
                                  <th>Subject</th>
                                  <th>Class</th>
                                  <th>Arm</th>
                                  <th>Term</th>
                                  <th>Session</th>
                                  <th>Actions</th>
                              </tr>
                          </thead>
                          <tbody>
                              <?php if (count($subjects_records) > 0): ?>
                                  <?php foreach ($subjects_records as $record): ?>
                                      <tr>
                                          <td><?= htmlspecialchars($record['subject']) ?></td>
                                          <td><?= htmlspecialchars($record['class']) ?></td>
                                          <td><?= htmlspecialchars($record['arm']) ?></td>
                                          <td><?= htmlspecialchars($record['term']) ?></td>
                                          <td><?= htmlspecialchars($record['csession']) ?></td>
                                          <td>
                                              <form method="POST" style="display:inline;">
                                                  <input type="hidden" name="subject" value="<?= htmlspecialchars($record['subject']) ?>">
                                                  <input type="hidden" name="class" value="<?= htmlspecialchars($record['class']) ?>">
                                                  <input type="hidden" name="arm" value="<?= htmlspecialchars($record['arm']) ?>">
                                                  <input type="hidden" name="id" value="<?= htmlspecialchars($record['id']) ?>">
                                                  <button type="submit" name="delete" class="btn btn-danger">
                                                  <span class="btn-label">
                                                    <i class="fa fa-trash"></i>  
                                                  </button>
                                              </form>
                                          </td>
                                      </tr>
                                  <?php endforeach; ?>
                              <?php else: ?>
                                  <tr>
                                      <td colspan="6">No subjects found.</td>
                                  </tr>
                              <?php endif; ?>
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
