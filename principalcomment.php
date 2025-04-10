<?php include('components/admin_logic.php');


// PRINCIPAL'S COMMENTS =====================================

// Fetching data for dropdowns
$classes = $conn->query("SELECT DISTINCT class FROM class");
$arms = $conn->query("SELECT DISTINCT arm FROM arm");
$terms = $conn->query("SELECT DISTINCT cterm FROM currentterm");
$sessions = $conn->query("SELECT DISTINCT csession FROM currentsession");

// Function to generate a unique ID
function generateUniqueId() {
    return 'CMT-' . uniqid();
}

// Initialize variables for the update form
$successMessage = '';
$errorMessage = '';
$id = $name = $comment = $class = $arm = $term = $session = '';

// Handle form submission for adding/updating records
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['csv_upload'])) {
        // Handle CSV upload
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
            $csvClass = $_POST['csv_class'];
            $csvArm = $_POST['csv_arm'];
            $file = fopen($_FILES['csv_file']['tmp_name'], "r");
            while (($row = fgetcsv($file)) !== FALSE) {
                $id = !empty($row[0]) ? $row[0] : generateUniqueId();
                $name = $row[1];
                $comment = $row[2];

                $sql = "INSERT INTO principalcomments (id, name, comment, class, arm, term, csession)
                        VALUES ('$id', '$name', '$comment', '$csvClass', '$csvArm', '$_POST[csv_term]', '$_POST[csv_session]')
                        ON DUPLICATE KEY UPDATE 
                        name=VALUES(name), 
                        comment=VALUES(comment), 
                        class=VALUES(class), 
                        arm=VALUES(arm), 
                        term=VALUES(term), 
                        csession=VALUES(csession)";

                if ($conn->query($sql) === FALSE) {
                    $errorMessage = "Error: " . $conn->error;
                }
            }
            fclose($file);
            $successMessage = "CSV file uploaded and records saved successfully!";
        }
    } elseif (isset($_POST['update'])) {
        // Handle individual record update
        $id = $_POST['id'];
        $name = $_POST['name'];
        $comment = $_POST['comment'];
        $class = $_POST['class'];
        $arm = $_POST['arm'];
        $term = $_POST['term'];
        $session = $_POST['session'];

        $query = "UPDATE principalcomments SET name='$name', comment='$comment', class='$class', arm='$arm', term='$term', csession='$session' WHERE id='$id'";
        if ($conn->query($query) === TRUE) {
            $successMessage = "Record updated successfully!";
        } else {
            $errorMessage = "Error updating record: " . $conn->error;
        }
    }
}

// Handle record deletion
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $conn->query("DELETE FROM principalcomments WHERE id='$delete_id'");
}

// Fetch the record to edit if an ID is passed
$editRecord = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM principalcomments WHERE id='$edit_id'");
    if ($result->num_rows > 0) {
        $editRecord = $result->fetch_assoc();
        $id = $editRecord['id'];
        $name = $editRecord['name'];
        $comment = $editRecord['comment'];
        $class = $editRecord['class'];
        $arm = $editRecord['arm'];
        $term = $editRecord['term'];
        $session = $editRecord['csession'];
    }
}

// Fetch all records
$principalrecords = $conn->query("SELECT * FROM principalcomments");



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
                <h3 class="fw-bold mb-3">Principal's Comments</h3>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                  <li class="breadcrumb-item active">Results</li>
                  <li class="breadcrumb-item active">Principal's Comments</li>
              </ol>
              </div>
           
            </div>

            <!-- BULK UPLOAD ============================ -->
            <div class="row">
             
             <div class="col-md-6">
               <div class="card card-round">
                 <div class="card-header">
                   <div class="card-head-row">
                     <div class="card-title">Comments</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">
                    <p> 
                  
                    <form method="POST" action="" class="form-container">
                      <input class="form-control" type="hidden" name="id" id="id" value="<?php echo $id; ?>">
                      <div class="form-group">
                          <input class="form-control" type="text" name="name" id="name" value="<?php echo $name; ?>" required placeholder="Name">
                      </div>
                      <div class="form-group">
                          <input class="form-control" type="text" name="comment" id="comment" value="<?php echo $comment; ?>" required placeholder="Comments">
                      </div>
                      <div class="form-group">
                          <input class="form-control" type="text" name="class" id="class" value="<?php echo $class; ?>" required placeholder="Class">
                      </div>
                      <div class="form-group">
                          <input class="form-control" type="text" name="arm" id="arm" value="<?php echo $arm; ?>" required placeholder="Arm">
                      </div>
                      <div class="form-group">
                          <input class="form-control" type="text" name="term" id="term" value="<?php echo $term; ?>" required placeholder="Term">
                      </div>
                      <div class="form-group">
                          <input class="form-control" type="text" name="session" id="session" value="<?php echo $session; ?>" required Placeholder="Session">
                      </div>
                      
                      <button type="submit" name="update" class="btn btn-success">
                      <span class="btn-label">
                      <i class="fa fa-sync-alt"></i>Update Record</button>
                      <button type="button" class="btn btn-secondary" onclick="window.location.href='principalcomment.php';">
                      <span class="btn-label">
                      <i class="fa fa-undo"></i>Reset</button>
                  </form>
    
                </p>
                   </div>
                 </div>
               </div>
             </div>

              <!-- FILTER UPLOADED ============================ -->
             <div class="col-md-6">
               <div class="card card-round">
                 <div class="card-header">
                   <div class="card-head-row">
                     <div class="card-title">Bulk Upload CSV</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">
                  
                   <form method="post" enctype="multipart/form-data" style="margin-top: 30px;">
                          <input type="file" name="csv_file" class="form-control" id="csv_file" accept=".csv" required>
                          <br>
                          <select name="csv_class" id="csv_class" class="form-control form-select" required>
                              <?php
                              // Rewind to start to fetch classes again
                              $classes->data_seek(0);
                              while($row = $classes->fetch_assoc()): ?>
                                  <option value="<?php echo $row['class']; ?>"><?php echo $row['class']; ?></option>
                              <?php endwhile; ?>
                          </select>
                          <br>
                          <select name="csv_arm" id="csv_arm" class="form-control form-select" required>
                              <?php
                              // Rewind to start to fetch arms again
                              $arms->data_seek(0);
                              while($row = $arms->fetch_assoc()): ?>
                                  <option value="<?php echo $row['arm']; ?>"><?php echo $row['arm']; ?></option>
                              <?php endwhile; ?>
                          </select>
                          <br>
                          <select name="csv_term" id="csv_term" class="form-control form-select" required>
                              <?php
                              // Rewind to start to fetch terms again
                              $terms->data_seek(0);
                              while($row = $terms->fetch_assoc()): ?>
                                  <option value="<?php echo $row['cterm']; ?>"><?php echo $row['cterm']; ?></option>
                              <?php endwhile; ?>
                          </select>
                          <br>
                          <select name="csv_session" id="csv_session" class="form-control form-select" required>
                              <?php
                              // Rewind to start to fetch sessions again
                              $sessions->data_seek(0);
                              while($row = $sessions->fetch_assoc()): ?>
                                  <option value="<?php echo $row['csession']; ?>"><?php echo $row['csession']; ?></option>
                              <?php endwhile; ?>
                          </select>
                          <br>
                          <button type="submit" class="btn btn-warning" name="csv_upload">
                          <span class="btn-label">
                          <i class="fa fa-cloud-upload-alt"></i>Upload CSV</button>
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
                      <div class="card-title">Uploaded Comments</div>
                    </div>
                  </div>
                  <div class="card-body pb-0">
                    <div class="mb-4 mt-2">
                  
                    <div class="table-responsive"> 
                         <!-- Display subjects -->
                         <table
                        id="multi-filter-select"
                        class="display table table-striped table-hover">
                                  <thead>
                                    <tr>
                                      <th>ID</th>
                                      <th>Name</th>
                                      <th>Comment</th>
                                      <th>Class</th>
                                      <th>Arm</th>
                                      <th>Term</th>
                                      <th>Session</th>
                                      <th>Actions</th>
                                    </tr>
                                  </thead>
                          <tbody>
                              <?php while($row = $principalrecords->fetch_assoc()): ?>
                                  <tr>
                                      <td><?php echo $row['id']; ?></td>
                                      <td><?php echo $row['name']; ?></td>
                                      <td><?php echo $row['comment']; ?></td>
                                      <td><?php echo $row['class']; ?></td>
                                      <td><?php echo $row['arm']; ?></td>
                                      <td><?php echo $row['term']; ?></td>
                                      <td><?php echo $row['csession']; ?></td>
                                      <td>
                                          <a href="?edit=<?php echo $row['id']; ?>" class="btn btn-warning"><span class="btn-label">
                                          <i class="fa fa-edit"></i></a>
                                          <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-danger"><span class="btn-label">
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
  </body>
</html>
