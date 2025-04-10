<?php include('components/admin_logic.php');


// VIEW STUDENTS ==============================
$result_all = $conn->query("SELECT * FROM students");
if ($result_all) {
    while ($row = $result_all->fetch_assoc()) {
        $students[] = $row;
    }
} else {
    die("Error fetching student records: " . $conn->error);
}

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
                <h3 class="fw-bold mb-3">View</h3>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                  <li class="breadcrumb-item active">Students</li>
                  <li class="breadcrumb-item active">View</li>
              </ol>
              </div>
           
            </div>

            <!-- BULK UPLOAD ============================ -->
            <div class="row">
             
              <div class="col-md-12">
                <div class="card card-round">
                  <div class="card-header">
                    <div class="card-head-row">
                      <div class="card-title">Students Registered</div>
                    </div>
                  </div>
                  <div class="card-body pb-0">
                    <div class="mb-4 mt-2">
                  
                      <?php if (!empty($students)): ?>
                        <div class="table-responsive"> 
                        <table
                        id="multi-filter-select"
                        class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Class</th>
                                    <th>Arm</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): 
                                    // Convert student ID from format WF/1004/23 to WF_1004_23 for image filename
                                    $image_name = str_replace("/", "_", $student['id']) . ".jpg";
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($student['id'], ENT_QUOTES) ?></td>
                                        <td><?= htmlspecialchars($student['name'], ENT_QUOTES) ?></td>
                                        <td><?= htmlspecialchars($student['class'], ENT_QUOTES) ?></td>
                                        <td><?= htmlspecialchars($student['arm'], ENT_QUOTES) ?></td>
                                        <td>
                                            <button class="btn btn-primary" onclick="showStudentProfile(
                                                '<?= htmlspecialchars($student['id'], ENT_QUOTES) ?>', 
                                                '<?= htmlspecialchars($student['name'], ENT_QUOTES) ?>', 
                                                '<?= htmlspecialchars($student['gender'], ENT_QUOTES) ?>',
                                                '<?= htmlspecialchars($student['dob'], ENT_QUOTES) ?>', 
                                                '<?= htmlspecialchars($student['placeob'], ENT_QUOTES) ?>', 
                                                '<?= htmlspecialchars($student['address'], ENT_QUOTES) ?>', 
                                                '<?= htmlspecialchars($student['religion'], ENT_QUOTES) ?>', 
                                                '<?= htmlspecialchars($student['state'], ENT_QUOTES) ?>', 
                                                '<?= htmlspecialchars($student['lga'], ENT_QUOTES) ?>', 
                                                '<?= htmlspecialchars($student['class'], ENT_QUOTES) ?>', 
                                                '<?= htmlspecialchars($student['arm'], ENT_QUOTES) ?>', 
                                                '<?= htmlspecialchars($student['hostel'], ENT_QUOTES) ?>', 
                                                '<?= htmlspecialchars($student['bloodtype'], ENT_QUOTES) ?>', 
                                                '<?= htmlspecialchars($student['bloodgroup'], ENT_QUOTES) ?>', 
                                                '<?= htmlspecialchars($student['height'], ENT_QUOTES) ?>', 
                                                '<?= htmlspecialchars($student['weight'], ENT_QUOTES) ?>', 
                                                '<?= htmlspecialchars($student['gname'], ENT_QUOTES) ?>',  
                                                '<?= htmlspecialchars($student['mobile'], ENT_QUOTES) ?>', 
                                                '<?= htmlspecialchars($student['goccupation'], ENT_QUOTES) ?>', 
                                                'studentimg/<?= htmlspecialchars($image_name, ENT_QUOTES) ?>'
                                            )">View</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                            <p>No records found.</p>
                        <?php endif; ?>
                        </div>

                              <!-- Student Profile Modal -->
                          <div class="modal fade" id="studentModal" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Student Profile Card</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="text-center">
                                            <img id="studentImage" src="" alt="Student Image" class="profile-img">
                                            <h4 id="studentName"></h4>
                                        </div>
                                        <table class="table table-bordered">
                                            <tr>
                                                <td><strong>ID:</strong></td>
                                                <td id="studentId"></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Gender:</strong></td>
                                                <td id="studentGender"></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Date of Birth:</strong></td>
                                                <td id="studentDob"></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Place of Birth:</strong></td>
                                                <td id="studentPlaceOb"></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Religion:</strong></td>
                                                <td id="studentReligion"></td>
                                            </tr>
                                            <tr>
                                                <td><strong>State:</strong></td>
                                                <td id="studentState"></td>
                                            </tr>
                                            <tr>
                                                <td><strong>LGA:</strong></td>
                                                <td id="studentLga"></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Class:</strong></td>
                                                <td id="studentClass"></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Arm:</strong></td>
                                                <td id="studentArm"></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Hostel:</strong></td>
                                                <td id="studentHostel"></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Blood Type:</strong></td>
                                                <td id="studentBloodType"></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Blood Group:</strong></td>
                                                <td id="studentBloodGroup"></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Height:</strong></td>
                                                <td id="studentHeight"></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Weight:</strong></td>
                                                <td id="studentWeight"></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Guardian Name:</strong></td>
                                                <td id="studentGname"></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Guardian Occupation:</strong></td>
                                                <td id="studentGoccupation"></td>
                                            </tr>
                                            <tr>
                                                <td width="200px"><strong>Guardian Mobile:</strong></td>
                                                <td id="studentMobile"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="2"><strong>Address:</strong> <span id="studentAddress"></span></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
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
