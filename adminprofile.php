<?php include('components/admin_logic.php');

// ADD QUESTION ==============================
// Initialize message variable
$message = '';

// Handle form submission for changing password
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate new password and confirm password
    if ($new_password !== $confirm_password) {
        $message = "New passwords do not match.";
    } else {
        // Update new password in the database for the logged-in student only
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("UPDATE login SET password=? WHERE username=?");
        $stmt->bind_param("ss", $new_password, $user_id);

        if ($stmt->execute()) {
            $message = "Password changed successfully!";
        } else {
            $message = "Error changing password: " . $stmt->error;
        }

        $stmt->close();
    }
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
                <h3 class="fw-bold mb-3">Change Password</h3>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                  <li class="breadcrumb-item active">My Profile</li>
                  <li class="breadcrumb-item active">Change Password</li>
              </ol>
              </div>
           
            </div>

            <!-- BULK UPLOAD ============================ -->
            <div class="row">
             
             <div class="col-md-12">
               <div class="card card-round">
                 <div class="card-header">
                   <div class="card-head-row">
                     <div class="card-title">Change Password</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">
                   
                   <form method="POST" action="">
                    <div class="form-group">
                    <input class="form-control" type="password" id="new_password" name="new_password" placeholder="Enter New Password" required>
                    </div>
                    <div class="form-group">
                    <input class="form-control" type="password" id="confirm_password" name="confirm_password" placeholder="Confirm New Password"required>
                    </div>
                    <div class="form-group">
                    <button class="btn btn-success" type="submit"><span class="btn-label">
                    <i class="fa fa-save"></i> Change Password</button>
                    </div>
                    </form>
                    <div class="message">
                    <?php 
                      if (!empty($message)) { 
                          echo '<div class="alert alert-info">' . htmlspecialchars($message) . '</div>'; 
                      } 
                      ?>
                    </div>
                    
                                     
                                        <!-- End Change Password Form -->

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
