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


// Save or Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['save'])) {
      $id = $_POST['id'];
      $product = $_POST['product'];
      $companyname = $_POST['companyname'];
      $phone = $_POST['phone'];
      $address = $_POST['address'];

      if (empty($id)) {
          // Insert new record
          $stmt = $conn->prepare("INSERT INTO suppliers (product, companyname, phone, address) VALUES (?, ?, ?, ?)");
          $stmt->bind_param("ssss", $product, $companyname, $phone, $address);
      } else {
          // Update existing record
          $stmt = $conn->prepare("UPDATE suppliers SET product=?, companyname=?, phone=?, address=? WHERE id=?");
          $stmt->bind_param("ssssi", $product, $companyname, $phone, $address, $id);
      }

      $stmt->execute();
      $stmt->close();
  }

  // Delete
  if (isset($_POST['delete'])) {
      $id = $_POST['id'];
      $stmt = $conn->prepare("DELETE FROM suppliers WHERE id=?");
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $stmt->close();
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
                <h3 class="fw-bold mb-3">Suppliers</h3>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                  <li class="breadcrumb-item active">Tuck Shop</li>
                  <li class="breadcrumb-item active">Suppliers</li>
              </ol>
              </div>
           
            </div>

            <!-- BULK UPLOAD ============================ -->
            <div class="row">
             
             <div class="col-md-12">
               <div class="card card-round">
                 <div class="card-header">
                   <div class="card-head-row">
                     <div class="card-title">Add Suppliers</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">

                    <p>
                            

                      <form action="" method="POST">
                        <input class="form-control" type="hidden" name="id" id="id">
                        <br>
                        <input class="form-control" type="text" name="product" id="product" required placeholder="Product">
                        <br>
                        <input class="form-control" type="text" name="companyname" id="companyname" required placeholder="Business Name">
                        <br>
                        <input class="form-control" type="text" name="phone" id="phone" required placeholder="Mobile">
                        <br>
                        <input class="form-control" type="text" name="address" id="address" required placeholder="Address">
                        <br>
                        <button type="submit" name="save" class="btn btn-success"><span class="btn-label">
                        <i class="fa fa-save"></i> Save</button>
                        
                        <button type="reset" class="btn btn-dark"><span class="btn-label">
                        <i class="fa fa-undo"></i>Clear</button>
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
                     <div class="card-title">Suppliers List</div>
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
                                  <!-- <th>ID</th> -->
                                  <th>Product</th>
                                  <th>Company Name</th>
                                  <th>Phone</th>
                                  <th>Address</th>
                                  <th>Actions</th>
                              </tr>
                          </thead>
                          <tbody>
                              <?php
                              // Fetch records
                              $result = $conn->query("SELECT * FROM suppliers");

                              while ($row = $result->fetch_assoc()) {
                                  echo "<tr>";
                                  // echo "<td>" . $row['id'] . "</td>";
                                  echo "<td>" . $row['product'] . "</td>";
                                  echo "<td>" . $row['companyname'] . "</td>";
                                  echo "<td>" . $row['phone'] . "</td>";
                                  echo "<td>" . $row['address'] . "</td>";
                                  echo "<td>
                                          <button class='btn btn-warning' onclick=\"editRecord(" . $row['id'] . ", '" . $row['product'] . "', '" . $row['companyname'] . "', '" . $row['phone'] . "', '" . $row['address'] . "')\" >Edit</button>
                                          <form action='' method='POST' style='display:inline;'>
                                              <input type='hidden' name='id' value='" . $row['id'] . "'>
                                              <button class='btn btn-danger' type='submit' name='delete' >Delete</button>
                                          </form>
                                        </td>";
                                  echo "</tr>";
                              }

                              $conn->close();
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

 
        <?php include('footer.php');?>
      <!-- Custom template | don't include it in your project! -->
      <?php include('cust-color.php');?>
      <!-- End Custom template -->
    </div>
   <?php include('scripts.php');?>

   <script>
         function editRecord(id, product, companyname, phone, address) {
            document.getElementById('id').value = id;
            document.getElementById('product').value = product;
            document.getElementById('companyname').value = companyname;
            document.getElementById('phone').value = phone;
            document.getElementById('address').value = address;
        }
    </script>
  </body>
</html>
