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


$studentname = ""; // Placeholder for student name
$cashier = "Admin"; // Placeholder for cashier (you can replace this dynamically)
$transactiondate = date('m/d/Y H:i:s'); // Current date and time


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
try {
    $productname = $_POST['productname'];
    $location = $_POST['location'];
    $unitprice = $_POST['unitprice'];
    $sellprice = $_POST['sellprice'];
    $qty = $_POST['qty'];
    $total = $sellprice * $qty;
    $description = $_POST['description'];
    $transdescription = 'Product Updated';
    $reorder_level = $_POST['reorder_level'];
    $reorder_qty = $_POST['reorder_qty'];
    $profit = $sellprice - $unitprice;

    if (isset($_POST['productid']) && !empty($_POST['productid'])) {
        $productid = $_POST['productid'];
        $stmt = $conn->prepare("UPDATE product SET productname=?, location=?, unitprice=?, sellprice=?, qty=?, total=?, description=?, reorder_level=?, reorder_qty=?, profit=? WHERE productid=?");
        if (!$stmt) {
            die("SQL error: " . $conn->error);
        }
        $stmt->bind_param('ssdddssdddi', $productname, $location, $unitprice, $sellprice, $qty, $total, $description, $reorder_level, $reorder_qty, $profit, $productid);
    } else {
        $stmt = $conn->prepare("INSERT INTO product (productname, location, unitprice, sellprice, qty, total, description, reorder_level, reorder_qty, profit) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            die("SQL error: " . $conn->error);
        }
        $stmt->bind_param('ssdddssddd', $productname, $location, $unitprice, $sellprice, $qty, $total, $description, $reorder_level, $reorder_qty, $profit);
    }

     if ($stmt->execute()) {
    echo "<p class='success'>Record saved successfully.</p>";

    // Record transaction in transactiondetails table
    $transactionStmt = $conn->prepare("INSERT INTO transactiondetails (studentname, productname, description, units, amount, transactiondate, profit, cashier) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $transactionStmt->bind_param('sssddsss', $studentname, $productname, $transdescription, $qty, $total, $transactiondate, $profit, $cashier);

    if ($transactionStmt->execute()) {
        //echo "<p class='success'>Transaction recorded successfully.</p>";
    } else {
        echo "<p class='error'>Error recording transaction: " . $transactionStmt->error . "</p>";
    }

    $transactionStmt->close();
} else {
    echo "<p class='error'>Error: " . $stmt->error . "</p>";
}

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
}


// Handle delete request
if (isset($_GET['delete'])) {
$productid = intval($_GET['delete']);
$stmt = $conn->prepare("DELETE FROM product WHERE productid = ?");
$stmt->bind_param('i', $productid);

if ($stmt->execute()) {
    echo "<p class='success'>Product deleted successfully.</p>";
     // Redirect back to the same page to refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
} else {
    echo "<p class='error'>Error: " . $stmt->error . "</p>";
}

$stmt->close();
}

// Fetch products

$sql = "SELECT * FROM product";
$result = $conn->query($sql);
// Convert result set into an array so it can be looped over safely later
$students = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}

$studentname = "Unknown"; // Placeholder for student name
$cashier = "Admin"; // Placeholder for cashier (you can replace this dynamically)
$transactiondate = date('m/d/Y H:i:s'); // Current date and time




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
                <nts class="fw-bold mb-3">Tuck Shop</h3>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                  <li class="breadcrumb-item active">Tuck Shop</li>
              </ol>
              </div>
           
            </div>

            <!-- BULK UPLOAD ============================ -->
            <div class="row">
             
             <div class="col-md-12">
               <div class="card card-round">
                 <div class="card-header">
                   <div class="card-head-row">
                     <div class="card-title">Add Products</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">

                   <p>
                          

                          <div class="form-container">
                              <form method="POST">
                                  <input class="form-control" type="hidden" name="productid" id="productid">
                                  <br>
                                  <input class="form-control" type="text" name="productname" id="productname" placeholder="Product Name" required>
                                  <br>
                                  <input class="form-control" type="text" name="location" id="location" placeholder="Location" required>
                                  <br>
                                  <input class="form-control" type="number" step="0.01" name="unitprice" id="unitprice" placeholder="Unit Price" required>
                                  <br>
                                  <input class="form-control" type="number" step="0.01" name="sellprice" id="sellprice" placeholder="Sell Price" required>
                                  <br>
                                  <input class="form-control" type="number" name="qty" id="qty" placeholder="Quantity" required>
                                  <br>
                                  <input class="form-control" type="text" name="description" id="description" placeholder="Description">
                                  <br>
                                  <input class="form-control" type="number" name="reorder_level" id="reorder_level" placeholder="Reorder Level" required>
                                  <br>
                                  <input class="form-control" type="number" name="reorder_qty" id="reorder_qty" placeholder="Reorder Quantity" required>
                                  <br>
                                  <button type="submit" class="btn btn-success"><span class="btn-label">
                                  <i class="fa fa-save"></i> Save Product</button>
                                  <button type="reset" class="btn btn-dark"> <span class="btn-label">
                                  <i class="fa fa-undo"></i>Reset</button>
                              </form>
                          </div>
        
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
                     <div class="card-title">Inventory</div>
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
                              <th>Product ID</th>
                              <th>Product Name</th>
                              <th>Location</th>
                              <th>Unit Price</th>
                              <th>Sell Price</th>
                              <th>Quantity</th>
                              <!--<th>Total</th>-->
                              <th>Description</th>
                              <th>Reorder Level</th>
                              <th>Reorder Quantity</th>
                              <!--<th>Profit</th>-->
                              <th>Actions</th>
                          </tr>
                      </thead>
                      <tbody>
                      <?php if (!empty($students)): ?>
                        <?php foreach ($students as $student): ?>
                          <tr>
                              <td><?php echo $student['productid']; ?></td>
                              <td><?php echo $student['productname']; ?></td>
                              <td><?php echo $student['location']; ?></td>
                              <td><?php echo $student['unitprice']; ?></td>
                              <td><?php echo $student['sellprice']; ?></td>
                              <td><?php echo $student['qty']; ?></td>
                              <!--<td><?php echo $student['total']; ?></td>-->
                              <td><?php echo $student['description']; ?></td>
                              <td><?php echo $student['reorder_level']; ?></td>
                              <td><?php echo $student['reorder_qty']; ?></td>
                              <!--<td><?php echo $student['profit']; ?></td>-->
                              <td>
                                  <a href="javascript:void(0);" onclick="editProduct(<?php echo htmlspecialchars(json_encode($student)); ?>)" class="btn btn-warning"><span class="btn-label">
                                  <i class="fa fa-edit"></i></a>
                                  <a class="btn btn-danger" href="?delete=<?php echo $student['productid']; ?>" onclick="return confirm('Are you sure you want to delete this product?');"><span class="btn-label">
                                  <i class="fa fa-trash"></i></a>
                              </td>
                          </tr>
                          <?php endforeach; ?>
                                    <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No records found</td>
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
  
  
   <script>
        function editProduct(product) {
            document.getElementById('productid').value = product.productid;
            document.getElementById('productname').value = product.productname;
            document.getElementById('location').value = product.location;
            document.getElementById('unitprice').value = product.unitprice;
            document.getElementById('sellprice').value = product.sellprice;
            document.getElementById('qty').value = product.qty;
            document.getElementById('description').value = product.description;
            document.getElementById('reorder_level').value = product.reorder_level;
            document.getElementById('reorder_qty').value = product.reorder_qty;
        }
    </script>
  </body>
</html>
