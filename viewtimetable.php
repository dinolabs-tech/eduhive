<?php include('components/students_logic.php');?>

<!DOCTYPE html>
<html lang="en">
<?php include('head.php');?>
  <body>
    <div class="wrapper">
      <!-- Sidebar -->
     <?php include('studentnav.php');?>
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
                <h3 class="fw-bold mb-3">Class Schedule</h3>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="students.php">Home</a></li>
                  <li class="breadcrumb-item active">Class Schedule</li>
              </ol>
              </div>
           
            </div>

            
              <div class="col-md-12">
                <div class="card">
                  <div class="card-header">
                    <h4 class="card-title">Class Schedule | 
                    <?php echo htmlspecialchars($student_class); ?> <?php echo htmlspecialchars($student_arm); ?>
                    </h4>   
                    </div>

                  <div class="card-body">
                        <div class="table-responsive table-hover">
                        <?php if (!empty($timetable)) { ?>
                            <table class="basic-dattables">
                                <tr>
                                    <th>Time</th>
                                    <th>Monday</th>
                                    <th>Tuesday</th>
                                    <th>Wednesday</th>
                                    <th>Thursday</th>
                                    <th>Friday</th>
                                </tr>
                                <?php foreach ($timetable as $timeSlot => $days) { ?>
                                    <tr>
                                        <td class="time-col"><?php echo $timeSlot; ?></td>
                                        <?php 
                                        $weekdays = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"];
                                        foreach ($weekdays as $day) { ?>
                                            <td><?php echo isset($days[$day]) ? $days[$day] : ''; ?></td>
                                        <?php } ?>
                                    </tr>
                                <?php } ?>
                            </table>
                        <?php } else { ?>
                            <p>No timetable available for your class.</p>
                        <?php } ?>
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
