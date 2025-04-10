<?php include('components/admin_logic.php');?>

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
                <h3 class="fw-bold mb-3">Student's Result</h3>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                  <li class="breadcrumb-item active">Results</li>
                  <li class="breadcrumb-item active">Student Result</li>
              </ol>
              </div>
           
            </div>

            <!-- BULK UPLOAD ============================ -->
            <div class="row">
             
             <div class="col-md-12">
               <div class="card card-round">
                 <div class="card-header">
                   <div class="card-head-row">
                     <div class="card-title">Check Result</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">
                      <p> 
                  
                        <form class="search-form" action="admincheckresult.php" method="get">
                            <input type="text" name="student_id" class="form-control" placeholder="Enter Student ID" required>
                            <br>
                            <input type="hidden" name="session" class="form-control" value="<?php echo htmlspecialchars($current_session); ?>">
                            
                            <input class=form-control type="hidden" name="term" value="<?php echo htmlspecialchars($current_term); ?>">
                            
                            <button type="submit" class="btn btn-success">
                            <span class="btn-label">
                            <i class="fa fa-check-circle"></i>Submit</button>
                       </form>
    
                      </p>
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
