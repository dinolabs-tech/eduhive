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

// Fetch classes and arms from the database
$classes = $conn->query("SELECT DISTINCT class FROM class");
$arms = $conn->query("SELECT DISTINCT arm FROM arm");

$filter_class = isset($_POST['filter_class']) ? $_POST['filter_class'] : '';
$filter_arm = isset($_POST['filter_arm']) ? $_POST['filter_arm'] : '';

// Handle bulk upload CSV
if (isset($_POST['bulk_submit'])) {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
        $csvFile = fopen($_FILES['csv_file']['tmp_name'], 'r');
        while (($row = fgetcsv($csvFile)) !== FALSE) {
            $subject = $row[0];
            $class = $row[1];
            $arm = $row[2];
            $conn->query("INSERT INTO subject (subject, class, arm) VALUES ('$subject', '$class', '$arm')");
        }
        fclose($csvFile);
        echo "<script>alert('Bulk upload successful!');</script>";
    } else {
        echo "<script>alert('Please upload a valid CSV file.');</script>";
    }
}

// Handle individual subject entry
if (isset($_POST['individual_submit'])) {
    $subject = $_POST['subject'];
    $class = $_POST['class'];
    $arm = $_POST['arm'];
    $conn->query("INSERT INTO subject (subject, class, arm) VALUES ('$subject', '$class', '$arm')");
    echo "<script>alert('Subject added successfully!');</script>";
}

// Handle deleting a subject
if (isset($_POST['delete_subject'])) {
    $id = $_POST['id'];
    $conn->query("DELETE FROM subject WHERE id = '$id'");
    echo "<script>alert('Subject deleted successfully!');</script>";
}

// Fetch subjects based on the filters
$query = "SELECT * FROM subject";
if ($filter_class) {
    $query .= " WHERE class = '$filter_class'";
    if ($filter_arm) {
        $query .= " AND arm = '$filter_arm'";
    }
} elseif ($filter_arm) {
    $query .= " WHERE arm = '$filter_arm'";
}
$subjects = $conn->query($query);



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
                <nts class="fw-bold mb-3">User Control</h3>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                  <li class="breadcrumb-item active">User-Control</li>
              </ol>
              </div>
           
            </div>

            <!-- BULK UPLOAD ============================ -->
            <div class="row">
             
             <div class="col-md-6">
               <div class="card card-round">
                 <div class="card-header">
                   <div class="card-head-row">
                     <div class="card-title">Bulk Upload (CSV)</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">

                      <p> 
                          <form action="" method="post" enctype="multipart/form-data">
                              <div class="form-group">
                                  <input type="file" class="form-control" id="csv_file" name="csv_file" required>
                              </div>
                              <button type="submit" name="bulk_submit" class="btn btn-success"><span class="btn-label">
                              <i class="fa fa-cloud-upload-alt"></i>Upload</button>
                          </form>
                    </p>
                 
                   </div>
                 </div>
               </div>
             </div>

              
             <div class="col-md-6">
               <div class="card card-round">
                 <div class="card-header">
                   <div class="card-head-row">
                     <div class="card-title">Single Entry</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">

                        <p>
                      
                      <form action="" method="post">
                        <div class="form-group">
                            <input type="text" class="form-control" id="subject" name="subject" placeholder="Enter Subject" required>
                        </div>
                        <div class="form-group">
                            <select class="form-control" id="class" name="class" required>
                                <option value="">Select Class</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['class']; ?>"><?php echo $class['class']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <select class="form-control" id="arm" name="arm" required>
                                <option value="">Select Arm</option>
                                <?php foreach ($arms as $arm): ?>
                                    <option value="<?php echo $arm['arm']; ?>"><?php echo $arm['arm']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" name="individual_submit" class="btn btn-success"> <span class="btn-label">
                        <i class="fa fa-save"></i> Add Subject</button>
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
                     <div class="card-title">Filter</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">
                    
                      <form action="" method="post" class="form-inline mb-4">
                    <div class="form-group mr-2">
                        <select class="form-control" id="filter_class" name="filter_class">
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['class']; ?>" <?php echo ($filter_class == $class['class']) ? 'selected' : ''; ?>><?php echo $class['class']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mr-2">
                        <select class="form-control" id="filter_arm" name="filter_arm">
                            <option value="">Select Arms</option>
                            <?php foreach ($arms as $arm): ?>
                                <option value="<?php echo $arm['arm']; ?>" <?php echo ($filter_arm == $arm['arm']) ? 'selected' : ''; ?>><?php echo $arm['arm']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success"><span class="btn-label">
                    <i class="fa fa-filter"></i> Filter</button>
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
                     <div class="card-title">Subject List</div>
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
                                <th>Action</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php if ($subjects->num_rows == 0): ?>
                                <tr>
                                  <td colspan="4" class="text-center">No subjects found.</td>
                                </tr>
                              <?php else: ?>
                                <?php while ($subject = $subjects->fetch_assoc()): ?>
                                  <tr>
                                    <td><?php echo $subject['subject']; ?></td>
                                      <td><?php echo $subject['class']; ?></td>
                                          <td><?php echo $subject['arm']; ?></td>
                                          <td>
                                              <form action="" method="post" style="display:inline;">
                                                  <input type="hidden" name="id" value="<?php echo $subject['id']; ?>">
                                                  <button type="submit" name="delete_subject" class="btn btn-danger btn-sm"><span class="btn-label">
                                                  <i class="fa fa-trash"></i></button>
                                              </form>
                                          </td>

                                  </tr>
                                <?php endwhile; ?>
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
