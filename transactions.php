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


// Fetch records from transactiondetails table
$sql = "SELECT * FROM transactiondetails ORDER BY transactiondate DESC";
$result = $conn->query($sql);
// Convert result set into an array so it can be looped over safely later
$students = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
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
                <h3 class="fw-bold mb-3">Transactions</h3>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                  <li class="breadcrumb-item active">Tuck Shop</li>
                  <li class="breadcrumb-item active">Transactions</li>
              </ol>
              </div>
           
            </div>

            <!-- BULK UPLOAD ============================ -->
            <div class="row">
             
            

              
             
          

          <div class="row">
           <div class="col-md-12">
               <div class="card card-round">
                 <div class="card-header">
                   <div class="card-head-row">
                     <div class="card-title">Transactions</div>
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
                                  <th>Product</th>
                                  <th>Description</th>
                                  <th>Units</th>
                                  <th>Amount</th>
                                  <th>Date</th>
                                  
                                  <th>Cashier</th>

                              </tr>
                          </thead>
                          <tbody>
                          <?php if (!empty($students)): ?>
                            <?php foreach ($students as $student): ?>

                             <tr>
                             <td><?php echo $student['transactionID']; ?></td>
                              <td><?php echo $student['studentname']; ?></td>
                              <td><?php echo $student['productname']; ?></td>
                              <td><?php echo $student['description']; ?></td>
                              <td><?php echo $student['units']; ?></td>
                              <td><?php echo $student['amount']; ?></td>
                              <td><?php echo $student['transactiondate']; ?></td>
                              <td><?php echo $student['cashier']; ?></td>
                              </tr>
                              <?php endforeach; ?>
                                    <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">No records found</td>
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

 
        <?php include('footer.php');?>
      </div>

      <!-- Custom template | don't include it in your project! -->
      <?php include('cust-color.php');?>
      <!-- End Custom template -->
    </div>
   <?php include('scripts.php');?>


  </body>
</html>
