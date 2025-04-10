<?php include('components/admin_logic.php');

// CLASS TEACHER'S COMMENT ==============================

// Fetch dropdown data
$classes = $conn->query("SELECT DISTINCT class FROM class");
$arms = $conn->query("SELECT DISTINCT arm FROM arm");
$terms = $conn->query("SELECT DISTINCT cterm FROM currentterm");
$sessions = $conn->query("SELECT DISTINCT csession FROM currentsession");

// Function to handle database queries
function executeClassQuery($conn, $sql) {
    if ($conn->query($sql) === TRUE) {
        return true;
    } else {
        return "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle CSV upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csv_upload'])) {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === 0) {
        $csvClass = $_POST['csv_class'];
        $csvArm = $_POST['csv_arm'];
        $file = fopen($_FILES['csv_file']['tmp_name'], "r");

        fgetcsv($file); // Skip header row
        while (($row = fgetcsv($file)) !== FALSE) {
            $id = $row[0];
            $name = $row[1];
            $comment = $row[2];
            $schlopen = $row[3];
            $dayspresent = $row[4];
            $daysabsent = $row[5];

            $sql = "INSERT INTO classcomments (id, name, comment, schlopen, dayspresent, daysabsent, class, arm, term, csession) 
                    VALUES ('$id', '$name', '$comment', '$schlopen', '$dayspresent', '$daysabsent', '$csvClass', '$csvArm', '{$_POST['csv_term']}', '{$_POST['csv_session']}')";
            executeClassQuery($conn, $sql);
        }
        fclose($file);
        echo "CSV file uploaded and records saved successfully!";
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission for adding/updating records
    $id = $_POST['id'];
    $name = $_POST['name'];
    $comment = $_POST['comment'];
    $schlopen = $_POST['schlopen'];
    $dayspresent = $_POST['dayspresent'];
    $daysabsent = $_POST['daysabsent'];
    $class = $_POST['class'];
    $arm = $_POST['arm'];
    $term = $_POST['term'];
    $session = $_POST['session'];

    if (empty($id)) {
        $sql = "INSERT INTO classcomments (name, comment, schlopen, dayspresent, daysabsent, class, arm, term, session) 
                VALUES ('$name', '$comment', '$schlopen', '$dayspresent', '$daysabsent', '$class', '$arm', '$term', '$session')";
    } else {
        $sql = "UPDATE classcomments SET name='$name', comment='$comment', schlopen='$schlopen', dayspresent='$dayspresent', 
                daysabsent='$daysabsent', class='$class', arm='$arm', term='$term', csession='$session' WHERE id='$id'";
    }

    $result = executeClassQuery($conn, $sql);
    echo $result === true ? "Record saved successfully!" : $result;
}

// Handle record deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM classcomments WHERE id='$id'";
    executeClassQuery($conn, $sql);
}

// Fetch all records
$records = $conn->query("SELECT * FROM classcomments");


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
                <h3 class="fw-bold mb-3">Class Teacher's Comments</h3>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                  <li class="breadcrumb-item active">Results</li>
                  <li class="breadcrumb-item active">Class Teacher's Comments</li>
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
                  
                      <form method="post">
                        <input type="text" name="id" id="id" class="form-control" placeholder="ID" required>
                        <br>
                        <input type="text" name="name" id="name" required placeholder="Name" class="form-control">
                        <br>
                        <textarea name="comment" id="comment" required Placeholder="Comments" class="form-control"></textarea>
                        <br>
                        <input type="number" name="schlopen" id="schlopen" required placeholder="Days School Opened" class="form-control">
                        <br>
                        <input type="number" name="dayspresent" id="dayspresent" required placeholder="Days Present" class="form-control">
                        <br>
                        <input type="number" name="daysabsent" id="daysabsent" required placeholder="Days Absent" class="form-control">
                        <br>
                        <select name="class" id="class" required class="form-control form-control">
                            <?php while($row = $classes->fetch_assoc()): ?>
                                <option value="<?php echo $row['class']; ?>"><?php echo $row['class']; ?></option>
                            <?php endwhile; ?>
                        </select>
                        <br>
                        <select name="arm" id="arm" required class="form-control form-select">
                            <?php while($row = $arms->fetch_assoc()): ?>
                                <option value="<?php echo $row['arm']; ?>"><?php echo $row['arm']; ?></option>
                            <?php endwhile; ?>
                        </select>
                        <br>
                        <select name="term" id="term" required class="form-control form-select" form-select>
                            <?php while($row = $terms->fetch_assoc()): ?>
                                <option value="<?php echo $row['cterm']; ?>"><?php echo $row['cterm']; ?></option>
                            <?php endwhile; ?>
                        </select>
                        <br>
                        <select name="session" id="session" required class="form-control form-select">
                            <?php while($row = $sessions->fetch_assoc()): ?>
                                <option value="<?php echo $row['csession']; ?>"><?php echo $row['csession']; ?></option>
                            <?php endwhile; ?>
                        </select>
                        <br>
                        <button type="submit" class="btn btn-success">
                        <span class="btn-label">
                        <i class="fa fa-sync-alt"></i>Update</button>
                        <button type="reset" class="btn btn-secondary">
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
                          <input type="file" name="csv_file" id="csv_file" accept=".csv" class="form-control" required>
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
                          <button type="submit" class="btn btn-primary" name="csv_upload">
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
                <th>School Open</th>
                <th>Days Present</th>
                <th>Days Absent</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $records->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['comment']; ?></td>
                    <td><?php echo $row['schlopen']; ?></td>
                    <td><?php echo $row['dayspresent']; ?></td>
                    <td><?php echo $row['daysabsent']; ?></td>
                    <td>
                        <a href="javascript:void(0);" onclick="editClassCommentRecord('<?php echo $row['id']; ?>', '<?php echo $row['name']; ?>', '<?php echo $row['comment']; ?>', '<?php echo $row['schlopen']; ?>', '<?php echo $row['dayspresent']; ?>', '<?php echo $row['daysabsent']; ?>', '<?php echo $row['class']; ?>', '<?php echo $row['arm']; ?>', '<?php echo $row['term']; ?>', '<?php echo $row['csession']; ?>')" class="btn btn-warning"><span class="btn-label">
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
