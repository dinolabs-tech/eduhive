<?php 
include('components/admin_logic.php');

// MODIFY STUDENTS =============================
// Handle form submission for updating student record and image
if (isset($_POST['update'])) {
    // Collect student information from form
    $id = $_POST['id'];
    $name = $_POST['name'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $placeob = $_POST['placeob'];
    $address = $_POST['address'];
    $religion = $_POST['religion'];
    $state = $_POST['state'];
    $lga = $_POST['lga'];
    $class = $_POST['class'];
    $arm = $_POST['arm'];
    $session_val = $_POST['session'];
    $term = $_POST['term'];
    $schoolname = $_POST['schoolname'];
    $schooladdress = $_POST['schooladdress'];
    $hobbies = $_POST['hobbies'];
    $lastclass = $_POST['lastclass'];
    $sickle = $_POST['sickle'];
    $challenge = $_POST['challenge'];
    $emergency = $_POST['emergency'];
    $familydoc = $_POST['familydoc'];
    $docaddress = $_POST['docaddress'];
    $docmobile = $_POST['docmobile'];
    $polio = $_POST['polio'];
    $tuberculosis = $_POST['tuberculosis'];
    $measles = $_POST['measles'];
    $tetanus = $_POST['tetanus'];
    $whooping = $_POST['whooping'];
    $gname = $_POST['gname'];
    $mobile = $_POST['mobile'];
    $goccupation = $_POST['goccupation'];
    $gaddress = $_POST['gaddress'];
    $grelationship = $_POST['grelationship'];
    $hostel = $_POST['hostel'];
    $bloodtype = $_POST['bloodtype'];
    $bloodgroup = $_POST['bloodgroup'];
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $password = $_POST['password'];

    // Update student record
    $sql = "UPDATE students SET 
                name='$name', 
                gender='$gender', 
                dob='$dob',
                placeob='$placeob', 
                address='$address', 
                religion='$religion', 
                state='$state', 
                lga='$lga', 
                class='$class', 
                arm='$arm', 
                session='$session_val', 
                term='$term', 
                schoolname='$schoolname', 
                schooladdress='$schooladdress', 
                hobbies='$hobbies', 
                lastclass='$lastclass', 
                sickle='$sickle', 
                challenge='$challenge', 
                emergency='$emergency', 
                familydoc='$familydoc', 
                docaddress='$docaddress', 
                docmobile='$docmobile', 
                polio='$polio', 
                tuberculosis='$tuberculosis', 
                measles='$measles', 
                tetanus='$tetanus', 
                whooping='$whooping', 
                gname='$gname', 
                mobile='$mobile', 
                goccupation='$goccupation', 
                gaddress='$gaddress', 
                grelationship='$grelationship', 
                hostel='$hostel', 
                bloodtype='$bloodtype', 
                bloodgroup='$bloodgroup', 
                height='$height', 
                weight='$weight', 
                password='$password'
            WHERE id='$id'";

    if ($conn->query($sql) === TRUE) {
        // Process student image if a new file is provided
        if (isset($_FILES["formFile"]) && $_FILES["formFile"]["error"] !== UPLOAD_ERR_NO_FILE) {
            $targetDir = "studentimg/"; // Upload folder
            $studentID = trim($id);

            if (empty($studentID)) {
                $message = "Student ID is required for image upload.";
            } else {
                // Sanitize Student ID for filename
                $sanitizedID = str_replace("/", "_", $studentID);
                $fileExtension = strtolower(pathinfo($_FILES["formFile"]["name"], PATHINFO_EXTENSION));
                $fileSize = $_FILES["formFile"]["size"];
                $allowedTypes = ["jpg", "jpeg"];
                $targetFile = $targetDir . $sanitizedID . "." . $fileExtension; // Final file path

                // Validate file size (500KB limit) and file type
                if ($fileSize > 500 * 1024) {
                    $message = "File size must be less than 500KB.";
                } elseif (!in_array($fileExtension, $allowedTypes)) {
                    $message = "Only JPG/JPEG files are allowed.";
                } else {
                    // Create directory if it does not exist
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0777, true);
                    }
                    // Move the uploaded file to the target directory
                    if (move_uploaded_file($_FILES["formFile"]["tmp_name"], $targetFile)) {
                        $message = "Image uploaded successfully as " . htmlspecialchars($sanitizedID) . "." . $fileExtension;
                    } else {
                        $message = "Error uploading the image.";
                    }
                }
            }
        }
        // Redirect back to refresh the page (you can pass $message via session or GET if needed)
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

// Handle deletion of a student record
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM students WHERE id='$id'";
    if ($conn->query($sql) === TRUE) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}

// Search query for student based on ID or name
$searchQuery = "";
if (isset($_POST['search'])) {
    $searchTerm = $_POST['searchTerm'];
    $searchQuery = "WHERE name LIKE '%$searchTerm%' OR id LIKE '%$searchTerm%'";
}

// Fetch student records
$sql = "SELECT * FROM students $searchQuery";
$result = $conn->query($sql);

// Convert result set into an array so it can be looped over safely later
$students = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}

// Fetch student details for editing if an ID is passed in the URL
$studentDetails = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $studentSql = "SELECT * FROM students WHERE id='$id'";
    $studentResult = $conn->query($studentSql);
    if ($studentResult->num_rows > 0) {
        $studentDetails = $studentResult->fetch_assoc();
    }
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
            <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
              <div>
                <h3 class="fw-bold mb-3">Modify</h3>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                  <li class="breadcrumb-item active">Students</li>
                  <li class="breadcrumb-item active">Modify</li>
                </ol>
              </div>
            </div>

            <!-- MODIFY STUDENTS============================= -->
            <div class="row">
              <div class="col-md-12">
                <div class="card card-round">
                  <div class="card-header">
                    <div class="card-head-row">
                      <div class="card-title">Modify Records</div>
                    </div>
                  </div>
                  <div class="card-body pb-0">
                    <div class="mb-4 mt-2">
                      <?php if ($studentDetails): ?>
                        <form method="POST" class="row g-3" enctype="multipart/form-data">
                          <input type="hidden" name="id" value="<?php echo $studentDetails['id']; ?>">
                          <div class="col-md-6">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="name" 
                                  value="<?php echo $studentDetails['name']; ?>"
                                  placeholder="Name" 
                                  required
                              >
                          </div>
                          <div class="col-md-2">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="gender" 
                                  value="<?php echo $studentDetails['gender']; ?>"
                                  placeholder="Gender" 
                                  required
                              >
                          </div>
                          <div class="col-md-2">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="dob" 
                                  value="<?php echo $studentDetails['dob']; ?>"
                                  placeholder="Date of Birth" 
                                  required
                              >
                          </div>
                          <div class="col-md-2">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="placeob" 
                                  value="<?php echo $studentDetails['placeob']; ?>"
                                  placeholder="Place of Birth" 
                                  required
                              >
                          </div>
                          <div class="col-md-1">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="religion" 
                                  value="<?php echo $studentDetails['religion']; ?>"
                                  placeholder="Religion" 
                                  required
                              >
                          </div>
                          <div class="col-md-8">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="address" 
                                  value="<?php echo $studentDetails['address']; ?>"
                                  placeholder="Address" 
                                  required
                              >
                          </div>
                          <div class="col-md-1">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="state" 
                                  value="<?php echo $studentDetails['state']; ?>"
                                  placeholder="State" 
                                  required
                              >
                          </div>
                          <div class="col-md-2">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="lga" 
                                  value="<?php echo $studentDetails['lga']; ?>"
                                  placeholder="Local Government" 
                                  required
                              >
                          </div>
                          <div class="col-md-2">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="class" 
                                  value="<?php echo $studentDetails['class']; ?>"
                                  placeholder="Class" 
                                  required
                              >
                          </div>
                          <div class="col-md-2">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="arm" 
                                  value="<?php echo $studentDetails['arm']; ?>"
                                  placeholder="Arm" 
                                  required
                              >
                          </div>
                          <div class="col-md-2">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="session" 
                                  value="<?php echo $studentDetails['session']; ?>"
                                  placeholder="Academic Session" 
                                  required
                              >
                          </div>
                          <div class="col-md-2">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="term" 
                                  value="<?php echo $studentDetails['term']; ?>"
                                  placeholder="Term" 
                                  required
                              >
                          </div>
                          <div class="col-md-2">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="hostel" 
                                  value="<?php echo $studentDetails['hostel']; ?>"
                                  placeholder="Hostel" 
                                  required
                              >
                          </div>
                          <hr width="100%">
                          <h5 class="card-title"><span> Parent / Guardian Information  </span></h5>
                          <div class="col-md-2">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="gname" 
                                  value="<?php echo $studentDetails['gname']; ?>"
                                  placeholder="Guardian Name" 
                                  required
                              >
                          </div>
                          <div class="col-md-2">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="mobile" 
                                  value="<?php echo $studentDetails['mobile']; ?>"
                                  placeholder="Guardian Mobile" 
                                  required
                              >
                          </div>
                          <div class="col-md-2">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="goccupation" 
                                  value="<?php echo $studentDetails['goccupation']; ?>"
                                  placeholder="Guardian Occupation" 
                                  required
                              >
                          </div>
                          <div class="col-md-2">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="grelationship" 
                                  value="<?php echo $studentDetails['grelationship']; ?>"
                                  placeholder="Guardian relationship"
                                  required
                              >
                          </div>
                          <div class="col-md-4">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="gaddress" 
                                  value="<?php echo $studentDetails['gaddress']; ?>"
                                  placeholder="Guardian Address" 
                                  required
                              >
                          </div>
                          <hr width="100%">
                          <h5 class="card-title"><span> Last School Attended  </span></h5>
                          <div class="col-md-2">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="schoolname" 
                                  value="<?php echo $studentDetails['schoolname']; ?>"
                                  placeholder="Last School Name" 
                              >
                          </div>
                          <div class="col-md-6">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="schooladdress" 
                                  value="<?php echo $studentDetails['schooladdress']; ?>"
                                  placeholder="School Address" 
                              >
                          </div>
                          <div class="col-md-2">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="hobbies" 
                                  value="<?php echo $studentDetails['hobbies']; ?>"
                                  placeholder="Hobbies"
                              >
                          </div>
                          <div class="col-md-2">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="lastclass" 
                                  value="<?php echo $studentDetails['lastclass']; ?>"
                                  placeholder="Last Class Attended" 
                              >
                          </div>
                          <hr width="100%">
                          <h5 class="card-title"><span> Medical Information  </span></h5>
                          <div class="col-md-3">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="bloodtype" 
                                  value="<?php echo $studentDetails['bloodtype']; ?>"
                                  placeholder="Blood Type"
                              >
                          </div>
                          <div class="col-md-3">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="bloodgroup" 
                                  value="<?php echo $studentDetails['bloodgroup']; ?>"
                                  placeholder="Blood Group"
                              >
                          </div>
                          <div class="col-md-3">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="height" 
                                  value="<?php echo $studentDetails['height']; ?>"
                                  placeholder="Height" 
                              >
                          </div>
                          <div class="col-md-3">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="weight" 
                                  value="<?php echo $studentDetails['weight']; ?>"
                                  placeholder="Weight" 
                              >
                          </div>
                          <strong><p>Have you been immunized against any of the following?</p></strong>
                          <div class="col-md-2">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="polio" 
                                  value="<?php echo $studentDetails['polio']; ?>"
                                  placeholder="Polio" 
                              >
                          </div>
                          <div class="col-md-2">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="tuberculosis" 
                                  value="<?php echo $studentDetails['tuberculosis']; ?>"
                                  placeholder="Tuberculosis" 
                              >
                          </div>
                          <div class="col-md-2">
                              <input  
                                  class="form-control form-control" 
                                  type="text"  
                                  name="measles" 
                                  value="<?php echo $studentDetails['measles']; ?>"
                                  placeholder="Measles" 
                              >
                          </div>
                          <div class="col-md-2">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="tetanus" 
                                  value="<?php echo $studentDetails['tetanus']; ?>"
                                  placeholder="Tetanus" 
                              >
                          </div>
                          <div class="col-md-2">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="whooping" 
                                  value="<?php echo $studentDetails['whooping']; ?>"
                                  placeholder="Whooping"
                              >
                          </div>
                          <strong><p>If "No"</p></strong>
                          <div class="col-md-2">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="familydoc" 
                                  value="<?php echo $studentDetails['familydoc']; ?>"
                                  placeholder="Family Doctor" 
                              >
                          </div>
                          <div class="col-md-2">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="docmobile" 
                                  value="<?php echo $studentDetails['docmobile']; ?>"
                                  placeholder="Doctor's Mobile" 
                              >
                          </div>
                          <div class="col-md-8">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="docaddress" 
                                  value="<?php echo $studentDetails['docaddress']; ?>"
                                  placeholder="Doctor's Address"
                              >
                          </div>
                          <strong><p>Does your ward have:</p></strong>
                          <div class="col-md-2">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="sickle" 
                                  value="<?php echo $studentDetails['sickle']; ?>"
                                  placeholder="Sickle Cell"
                              >
                          </div>
                          <div class="col-md-2">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="challenge" 
                                  value="<?php echo $studentDetails['challenge']; ?>"
                                  placeholder="Any challenges?"
                              >
                          </div>
                          <strong><p>In emergencies, are we permitted to take your ward to the hospital?</p></strong>
                          <div class="col-md-2">
                              <input 
                                  class="form-control form-control"
                                  type="text"  
                                  name="emergency" 
                                  value="<?php echo $studentDetails['emergency']; ?>"
                                  placeholder="Emergency" 
                              >
                          </div>
                          <hr width="100%">
                          <h5 class="card-title"><span> Passport </span></h5>
                          <div class="col-md-6">
                              <div class="col-sm-10">
                                  <input class="form-control mb-3" type="file" id="formFile" name="formFile" accept=".jpg,.jpeg" >
                              </div>
                          </div>
                          <hr width="100%"/>
                          <h5 class="card-title"><span> Student's Login Password  </span></h5>
                          <div class="col-md-2">
                              <input 
                                  class="form-control form-control"
                                  type="password"  
                                  name="password" 
                                  value="<?php echo $studentDetails['password']; ?>"
                                  placeholder="Password" 
                                  required
                              >
                          </div>
                          <br>
                          <button type="submit" name="update" class="btn btn-success btn-block"> 
                              <span class="btn-label">
                                  <i class="fa fa-check"></i>
                              </span> Update
                          </button>
                        </form>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- STUDENT RECORDS ========================== -->
            <div class="row">
              <div class="col-md-12">
                <div class="card card-round">
                  <div class="card-header">
                    <div class="card-head-row">
                      <div class="card-title">Students Records</div>
                    </div>
                  </div>
                  <div class="card-body pb-0">
                    <div class="mb-4 mt-2">
                      <div class="table-responsive">
                        <table id="multi-filter-select" class="display table table-striped table-hover">
                          <thead>
                            <tr>
                              <th>ID</th>
                              <th>Name</th>
                              <th>Date of Birth</th>
                              <th>Class</th>
                              <th>Arm</th>
                              <th>Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php if (!empty($students)): ?>
                              <?php foreach ($students as $student): ?>
                                <tr>
                                  <td><?php echo htmlspecialchars($student['id']); ?></td>
                                  <td><?php echo htmlspecialchars($student['name']); ?></td>
                                  <td><?php echo htmlspecialchars($student['dob']); ?></td>
                                  <td><?php echo htmlspecialchars($student['class']); ?></td>
                                  <td><?php echo htmlspecialchars($student['arm']); ?></td>
                                  <td>
                                    <a href="?edit=<?php echo $student['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="?delete=<?php echo $student['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
                                  </td>
                                </tr>
                              <?php endforeach; ?>
                            <?php else: ?>
                              <tr>
                                <td colspan="6">No data available in table.</td>
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
        <script>
          document.querySelector('button[type="reset"]').addEventListener('click', function() {
              document.getElementById('myForm').reset();
          });
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
