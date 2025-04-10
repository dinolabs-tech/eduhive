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


// Queries to retrieve the data
$total_sales_query = "SELECT SUM(amount) FROM transactiondetails WHERE description='Sales'";
$total_profit_query = "SELECT SUM(profit) FROM transactiondetails WHERE description='Sales'";
$inventory_qty_query = "SELECT COUNT(qty) FROM product";
$inventory_sum_query = "SELECT SUM(total) FROM product";
$total_transactions_query = "SELECT COUNT(rownumber) FROM transactiondetails";
$out_of_stock_query = "SELECT * FROM product WHERE qty=0";
$tuck_students_qty_query = "SELECT COUNT(regno) FROM tuck";
$tuck_balance_query = "SELECT SUM(vbalance) FROM tuck";
$tuck_low_balance_query = "SELECT COUNT(regno) FROM tuck WHERE vbalance = 0";

// Execute the queries
$total_sales_result = $conn->query($total_sales_query);
$total_profit_result = $conn->query($total_profit_query);
$inventory_qty_result = $conn->query($inventory_qty_query);
$inventory_sum_result = $conn->query($inventory_sum_query);
$total_transactions_result = $conn->query($total_transactions_query);
$out_of_stock_result = $conn->query($out_of_stock_query);
$tuck_student_result = $conn->query($tuck_students_qty_query);
$tuck_balance_result = $conn->query($tuck_balance_query);
$tuck_low_balance_result = $conn->query($tuck_low_balance_query);

// Fetch the results
$total_sales = $total_sales_result->fetch_row()[0];
$total_profit = $total_profit_result->fetch_row()[0];
$inventory_qty = $inventory_qty_result->fetch_row()[0];
$inventory_sum = $inventory_sum_result->fetch_row()[0];
$total_transactions = $total_transactions_result->fetch_row()[0];
$out_of_stock = $out_of_stock_result->num_rows;
$total_tuck_students = $tuck_student_result->fetch_row()[0];
$total_students_balance = $tuck_balance_result->fetch_row()[0];
$total_low_balance = $tuck_low_balance_result->fetch_row()[0];



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
                <h3 class="fw-bold mb-3">Dashboard</h3>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                  <li class="breadcrumb-item active">Tuck Shop</li>
                  <li class="breadcrumb-item active">Dashboard</li>
              </ol>
              </div>
           
            </div>

            <!-- BULK UPLOAD ============================ -->
            <div class="row">
             
            <div class="col-md-3">
               <div class="card card-round card-success curves-shadow">
                 <div class="card-header">
                   <div class="card-head-row">
                   <div class="card-title">Total Students Registered</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">

                   <p class="card-title"> <?php echo $total_tuck_students; ?> </p>
                 
                   </div>
                 </div>
               </div>
             </div>

             <div class="col-md-3">
               <div class="card card-round card-primary skew-shadow">
                 <div class="card-header">
                   <div class="card-head-row">
                   <div class="card-title">Total Students Balance</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">

                   <p class="card-title">&#8358;  <?php echo $total_students_balance; ?> </p>
                 
                   </div>
                 </div>
               </div>
             </div>

             <div class="col-md-3">
               <div class="card card-round card-danger bubble-shadow">
                 <div class="card-header">
                   <div class="card-head-row">
                   <div class="card-title">Low Balance</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">

                   <p class="card-title"> <?php echo $total_low_balance; ?> </p>
                 
                   </div>
                 </div>
               </div>
             </div>

             <div class="col-md-3">
               <div class="card card-round card-success curves-shadow">
                 <div class="card-header">
                   <div class="card-head-row">
                   <div class="card-title">Sales</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">

                   <p class="card-title"> &#8358; <?php echo number_format($total_sales, 2); ?> </p>
                 
                   </div>
                 </div>
               </div>
             </div>

              
             <div class="col-md-3">
               <div class="card card-round card-secondary skew-shadow">
                 <div class="card-header">
                   <div class="card-head-row">
                     <div class="card-title">Transactions</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">

                   <p class="card-title"> <?php echo $total_transactions; ?> Transactions</p>
                 
                   </div>
                 </div>
               </div>
             </div>


             <div class="col-md-3">
               <div class="card card-round card-warning bubble-shadow">
                 <div class="card-header">
                   <div class="card-head-row">
                     <div class="card-title">Inventory Quantity</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">

                   <p class="card-title"><?php echo $inventory_qty; ?> Items</p>
                 
                   </div>
                 </div>
               </div>
             </div>


             <div class="col-md-3">
               <div class="card card-round card-primary curves-shadow">
                 <div class="card-header">
                   <div class="card-head-row">
                     <div class="card-title">Inventory Sum</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">

                   <p class="card-title"> &#8358; <?php echo number_format($inventory_sum, 2); ?></p>
                 
                   </div>
                 </div>
               </div>
             </div>


             <div class="col-md-3">
               <div class="card card-round card-danger bubble-shadow">
                 <div class="card-header">
                   <div class="card-head-row">
                     <div class="card-title">Out of Stock</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">

                   <p class="card-title"><?php echo $out_of_stock; ?> Products</p>
                 
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
