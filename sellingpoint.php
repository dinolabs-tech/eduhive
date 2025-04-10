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


// Initialize variables
$message = '';
$student = [];
$products = [];
$searchResults = [];

// Fetch products
$productQuery = "SELECT * FROM product";
$result = $conn->query($productQuery);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
} else {
    die("Error fetching products: " . $conn->error);
}

// Handle student search
if (isset($_POST['search_student'])) {
    $searchTerm = $conn->real_escape_string($_POST['search_term']);
    $searchQuery = "SELECT regno, studentname, studentclass, csession, vbalance FROM tuck WHERE regno LIKE '%$searchTerm%' OR studentname LIKE '%$searchTerm%'";
    
    $searchResult = $conn->query($searchQuery);

    if ($searchResult) {
        while ($row = $searchResult->fetch_assoc()) {
            $searchResults[] = $row;
        }
    } else {
        $message = "Error fetching students: " . $conn->error;
    }
}

// Handle student selection
if (isset($_POST['select_student'])) {
    $regno = $conn->real_escape_string($_POST['regno']);
    $studentQuery = "SELECT * FROM tuck WHERE regno = '$regno'";
    $result = $conn->query($studentQuery);

    if ($result && $result->num_rows > 0) {
        $studentDetails = $result->fetch_assoc();
        $_SESSION['selected_student'] = $studentDetails;
    } else {
        $message = "Student not found.";
    }
}

// Handle cart operations
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add_to_cart':
            $product_id = $_POST['product_id'];
            $product_name = $_POST['product_name'];
            $price = $_POST['price'];
            $qty = $_POST['qty'];

            // Initialize cart if it doesn't exist
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }

            // Check if the product is already in the cart
            $product_exists = false;
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['product_id'] === $product_id) {
                    $item['qty'] += $qty; // Update quantity
                    $product_exists = true;
                    break;
                }
            }

            // If not, add new item to the cart
            if (!$product_exists) {
                $_SESSION['cart'][] = [
                    'product_id' => $product_id,
                    'product_name' => $product_name,
                    'price' => $price,
                    'qty' => $qty,
                ];
            }
            break;

        case 'remove_from_cart':
            $product_id = $_POST['product_id'];
            $_SESSION['cart'] = array_filter($_SESSION['cart'], function ($item) use ($product_id) {
                return $item['product_id'] !== $product_id;
            });
            break;

        case 'clear_cart':
            unset($_SESSION['cart']);
            break;

            case 'checkout':
                if (isset($_SESSION['selected_student']) && isset($_SESSION['cart'])) {
                    $student = $_SESSION['selected_student'];
                    $totalPrice = 0; 
                    $updates = [];
            
                    foreach ($_SESSION['cart'] as $item) {
                        $totalPrice += $item['price'] * $item['qty'];
                        $updateQuery = "UPDATE product SET qty = qty - ?, total = sellprice * qty WHERE productid = ?";
                        $stmt = $conn->prepare($updateQuery);
                        $stmt->bind_param("ii", $item['qty'], $item['product_id']);
                        $stmt->execute();
                        $stmt->close();
                        
                        // Store update details for feedback
                        $updates[] = "{$item['qty']} x {$item['product_name']}";
                        
                       // Prepare values for transaction insert
                        //$description = "Purchase of {$item['qty']} {$item['product_name']}";
                        $description = "Sales";
                        $amount = $item['price'] * $item['qty'];

                        // Ensure cost_price exists; if not, default to 0
                        $cost_price = isset($item['cost_price']) ? $item['cost_price'] : 0;

                        // Correctly assign profit value
                        //$profit = $amount - ($cost_price * $item['qty']);
                        $profit = $amount - '0';

                        $cashier = $_SESSION['staffname']; // Placeholder for cashier name, modify as needed
                        

                        // Verify that transactionID is set
                        if (empty($student['regno'])) {
                            die("Transaction ID is missing.");
                        }

                        // Insert into transactiondetails
                        $insertTransactionDetailsQuery = "INSERT INTO transactiondetails 
                            (transactionID, studentname, productname, description, units, amount, transactiondate, profit, cashier, rownumber) 
                            VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?, NULL)";
                        $stmt = $conn->prepare($insertTransactionDetailsQuery);

                        // Bind parameters (ensure data types match your database schema)
                        $stmt->bind_param("sssssdds", 
                            $student['regno'], 
                            $student['studentname'], 
                            $item['product_name'], 
                            $description, 
                            $item['qty'], 
                            $amount, 
                            $profit, 
                            $cashier
                        );
                        $stmt->execute();
                        $stmt->close();

                    }
            
                    // Update student balance
                    $newBalance = $student['vbalance'] - $totalPrice;
                    if ($newBalance >= 0) {
                        $updateBalanceQuery = "UPDATE tuck SET vbalance = ? WHERE regno = ?";
                        $stmt = $conn->prepare($updateBalanceQuery);
                        $stmt->bind_param("ds", $newBalance, $student['regno']);
                        $stmt->execute();
                        $stmt->close();
            
                        // Clear the cart after checkout
                        unset($_SESSION['cart']);
                        
                        // Prepare checkout message
                        $message = "Checkout successful for {$student['studentname']}. Items: " . 
                                   implode(", ", $updates) . ". New balance: " . number_format($newBalance, 2);
                        
                        // Update session with new balance
                        $_SESSION['selected_student']['vbalance'] = $newBalance;
                    } else {
                        $message = "Insufficient balance for {$student['studentname']}.";
                    }
                } else {
                    $message = "Please select a student and add items to the cart before checking out.";
                }
                
                // Redirect back to avoid form resubmission
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
    }

    // Redirect back to avoid form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
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
                <nts class="fw-bold mb-3">Tuck Shop</h3>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                  <li class="breadcrumb-item active">Tuck Shop</li>
              </ol>
              </div>
           
            </div>

            <!-- Selling Point ============================ -->
            <div class="row">
             
             <div class="col-md-12">
               <div class="card card-round">
                 <div class="card-header">
                   <div class="card-head-row">
                     <div class="card-title">Search</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">

                   <form method="POST" class="mb-4">
                          <div class="input-group">
                              <input type="text" name="search_term" class="form-control" placeholder="Enter student name or ID" required>
                              <button type="submit" name="search_student" class="btn btn-success"><span class="btn-label">
                              <i class="fa fa-search"></i>Search</button>
                          </div>
                      </form>

                      <?php if (!empty($searchResults)): ?>

                          <table id="basic-datatables">
                              <thead>
                              <tr>
                                  <th>Student ID</th>
                                  <th>Student Name</th>
                                  <th>Class</th>
                                  <th>Action</th>
                              </tr>
                              </thead>
                              <tbody>
                              <?php foreach ($searchResults as $student): ?>
                                  <tr>
                                      <td><?= htmlspecialchars($student['regno']) ?></td>
                                      <td><?= htmlspecialchars($student['studentname']) ?></td>
                                      <td><?= htmlspecialchars($student['studentclass']) ?></td>
                                      <td>
                                          <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                              <input type="hidden" name="regno" value="<?= htmlspecialchars($student['regno']) ?>">
                                              <input type="hidden" name="select_student" value="1">
                                              <button type="submit" class="btn btn-info btn-sm"><span class="btn-label">
                                              <i class="fa fa-check-circle"></i>Select</button>
                                          </form>
                                      </td>
                                  </tr>
                              <?php endforeach; ?>
                              </tbody>
                          </table>
                      <?php elseif (isset($_POST['search_student'])): ?>
                          <p class="text-danger">No results found for your search.</p>
                      <?php endif; ?>
                   
                 
                   </div>
                 </div>
               </div>
             </div>

              

             <div class="col-md-12">
               <div class="card card-round">
                 <div class="card-header">
                   <div class="card-head-row">
                     <saction class="card-title">Transaction Cart</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">
                            <div class="table-responsive"> 
                              <!-- Display Student Details -->
                              <table id="basic-datatables">
                            <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= htmlspecialchars($product['productname']) ?></td>
                                    <td><?= htmlspecialchars($product['sellprice']) ?></td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="add_to_cart">
                                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['productid']) ?>">
                                            <input type="hidden" name="product_name" value="<?= htmlspecialchars($product['productname']) ?>">
                                            <input type="hidden" name="price" value="<?= htmlspecialchars($product['sellprice']) ?>">
                                            <input type="number" name="qty" value="1" min="1" style="width: 10px;" class="form-control d-inline w-25">
                                            <button type="submit" class="btn btn-success btn-sm"><span class="btn-label">
                                            <i class="fa fa-shopping-cart"></i>Add to Cart</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>

                              <br/>
                              <!-- Cart -->
                              <h3 class="mb-3">Cart</h3>
                              <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
                                  <table class="table table-bordered cart-table">
                                      <thead>
                                      <tr>
                                          <th>Product</th>
                                          <th>Price</th>
                                          <th>Quantity</th>
                                          <th>Total</th>
                                          <th>Action</th>
                                      </tr>
                                      </thead>
                                      <tbody>
                                      <?php foreach ($_SESSION['cart'] as $item): ?>
                                          <tr>
                                              <td><?= htmlspecialchars($item['product_name']) ?></td>
                                              <td><?= htmlspecialchars($item['price']) ?></td>
                                              <td><?= htmlspecialchars($item['qty']) ?></td>
                                              <td><?= htmlspecialchars($item['price'] * $item['qty']) ?></td>
                                              <td>
                                                  <form method="POST" class="d-inline">
                                                      <input type="hidden" name="action" value="remove_from_cart">
                                                      <input type="hidden" name="product_id" value="<?= htmlspecialchars($item['product_id']) ?>">
                                                      <button type="submit" class="btn btn-danger btn-sm"><span class="btn-label">
                                                      <i class="fa fa-trash"></i></button>
                                                  </form>
                                              </td>
                                          </tr>
                                      <?php endforeach; ?>
                                      </tbody>
                                  </table>
                                  <form method="POST">
                                      <input type="hidden" name="action" value="clear_cart">
                                      <button type="submit" class="btn btn-warning"><span class="btn-label">
                                      <i class="fa fa-trash-alt"></i>Clear Cart</button>
                                  </form>
                                  <form method="POST" class="mt-3">
                                      <input type="hidden" name="action" value="checkout">
                                      <button type="submit" class="btn btn-success"><span class="btn-label">
                                      <i class="fa fa-credit-card"></i>Checkout</button>
                                  </form>
                            <?php else: ?>
                                <p class="text-center">Your cart is empty.</p>
                            <?php endif; ?>

                            <br/>
                            <?php if (isset($_SESSION['selected_student'])): ?>
                            <h3 class="mb-3">Selected Student</h3>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="studentId" class="form-label">Student ID:</label>
                                    <input type="text" class="form-control" id="studentId" value="<?= htmlspecialchars($_SESSION['selected_student']['regno']) ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label for="studentName" class="form-label">Student Name:</label>
                                    <input type="text" class="form-control" id="studentName" value="<?= htmlspecialchars($_SESSION['selected_student']['studentname']) ?>" readonly>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="studentClass" class="form-label">Class:</label>
                                    <input type="text" class="form-control" id="studentClass" value="<?= htmlspecialchars($_SESSION['selected_student']['studentclass']) ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label for="session" class="form-label">Session:</label>
                                    <input type="text" class="form-control" id="session" value="<?= htmlspecialchars($_SESSION['selected_student']['csession']) ?>" readonly>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="balance" class="form-label">Balance:</label>
                                    <input type="text" class="form-control" id="balance" value="<?= htmlspecialchars($_SESSION['selected_student']['vbalance']) ?>" readonly>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($message): ?>
                            <div class="alert alert-info mt-3" role="alert">
                                <?= htmlspecialchars($message) ?>
                            </div>
                            <?php endif; ?>

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
    const productSelect = document.querySelector('select[name="product_id"]');
    const priceField = document.getElementById('price');
    const qtyField = document.getElementById('qty');
    const totalField = document.getElementById('total');

    productSelect.addEventListener('change', () => {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const price = selectedOption.getAttribute('data-price');
        priceField.value = price || 0;
        calculateTotal();
    });

    qtyField.addEventListener('input', calculateTotal);

    function calculateTotal() {
        const price = parseFloat(priceField.value) || 0;
        const qty = parseInt(qtyField.value) || 0;
        totalField.value = price * qty;
    }
</script>
  </body>
</html>
