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

// Fetch classes from the database
$class_sql = "SELECT DISTINCT Class FROM class"; // Adjust this query based on your class table structure
$class_result = $conn->query($class_sql);

$classes = [];
if ($class_result->num_rows > 0) {
    while ($row = $class_result->fetch_assoc()) {
        $classes[] = $row['Class'];
    }
}

// Fetch subjects from the database based on selected class
$subjects = [];
if (isset($_POST['Class'])) {
    $selected_class = $_POST['Class'];
    $subject_sql = "SELECT subject FROM subject WHERE Class = '$selected_class'"; // Adjust this query based on your subject table structure
    $subject_result = $conn->query($subject_sql);

    if ($subject_result->num_rows > 0) {
        while ($row = $subject_result->fetch_assoc()) {
            $subjects[] = $row['subject'];
        }
    }
}

// Handle form submission for upload
$message = '';
$target_dir = 'Notes/'; // Define this outside to use globally

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload'])) {
    $subject_name = $_POST['subject'];
    $class_name = $_POST['class'];

    if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
        $document = $_FILES['document'];
        $file_ext = pathinfo($document['name'], PATHINFO_EXTENSION);

        // Sanitize file name to avoid special characters and spaces
        $sanitized_subject = preg_replace('/[^a-zA-Z0-9_-]/', '_', $subject_name);
        $sanitized_class = preg_replace('/[^a-zA-Z0-9_-]/', '_', $class_name);
        $file_name = $sanitized_subject . '_' . $sanitized_class . '.' . $file_ext;

        $target_file = $target_dir . $file_name;

        // Create the assignment directory if it doesn't exist
        if (!is_dir($target_dir)) {
            if (!mkdir($target_dir, 0755, true)) {
                echo "<script>alert('Failed to create target directory.');</script>";
                exit; // Exit to prevent further execution
            }
        }

        // Move uploaded file
        if (move_uploaded_file($document['tmp_name'], $target_file)) {
            // Insert assignment details into the database
            $insert_sql = "INSERT INTO notes (subject_name, class_name, file_name)
                           VALUES ('$subject_name', '$class_name', '$file_name')";
            if ($conn->query($insert_sql) === TRUE) {
                // Display success popup
                echo "<script>alert('File uploaded and record saved successfully!');</script>";
            } else {
                echo "<script>alert('File uploaded but record not saved: " . $conn->error . "');</script>";
            }
        } else {
            echo "<script>alert('Error uploading file. Check file permissions and directory path.');</script>";
        }
    } else {
        echo "<script>alert('Please select a valid file.');</script>";
    }
}



// Handle form submission for delete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $note_id = $_POST['note_id'];
    $file_name = $_POST['file_name'];

    // Delete the file from the file system
    $target_file = 'Note/' . $file_name;
    if (file_exists($target_file)) {
        unlink($target_file);
    }

    // Delete the record from the database
    $delete_sql = "DELETE FROM notes WHERE id = $note_id";
    if ($conn->query($delete_sql) === TRUE) {
        $message = 'Notes deleted successfully!';
    } else {
        $message = 'Error deleting Note: ' . $conn->error;
    }
}

// Fetch uploaded assignments
$notes_sql = "SELECT * FROM notes";
$notes_result = $conn->query($notes_sql);
$uploaded_notes = [];
if ($notes_result->num_rows > 0) {
    while ($row = $notes_result->fetch_assoc()) {
        $uploaded_notes[] = $row;
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
                <nts class="fw-bold mb-3">Notes</h3>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                  <li class="breadcrumb-item active">E-Learning Resources</li>
                  <li class="breadcrumb-item active">Notes</li>
                  <li class="breadcrumb-item active">Upload</li>
              </ol>
              </div>
           
            </div>

            <!-- BULK UPLOAD ============================ -->
            <div class="row">
             
             <div class="col-md-12">
               <div class="card card-round">
                 <div class="card-header">
                   <div class="card-head-row">
                     <div class="card-title">Upload</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">
                    
                   <p>
                          
                          <form method="post" enctype="multipart/form-data">
                              <select class="form-control form-select" id="class" name="class" onchange="fetchSubjects()" required>
                                  <option value="">Select Class</option>
                                  <?php foreach ($classes as $class): ?>
                                      <option value="<?php echo htmlspecialchars($class); ?>">
                                          <?php echo htmlspecialchars($class); ?>
                                      </option>
                                  <?php endforeach; ?>
                              </select>
                                <br>
                              <div id="subject-container">
                                  <select class="form-control form-select" id="subject" name="subject" required>
                                      <option value="">Select Subject</option>
                                      <?php foreach ($subjects as $subject): ?>
                                          <option value="<?php echo htmlspecialchars($subject); ?>">
                                              <?php echo htmlspecialchars($subject); ?>
                                          </option>
                                      <?php endforeach; ?>
                                  </select>
                              </div>
          
                                <br>
                              <input class="form-control" type="file" id="document" name="document" accept=".doc,.docx" required><br>
                              <button type="submit" name="upload" class="btn btn-success"><span class="btn-label">
                              <i class="fa fa-cloud-upload-alt"></i>Upload</button>
                          </form>
                          </p>
                          <?php if ($message): ?>
                              <p class="message"><?php echo htmlspecialchars($message); ?>
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
        function fetchSubjects() {
            var classSelect = document.getElementById("class");
            var selectedClass = classSelect.value;

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "fetch_subjects.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.getElementById("subject-container").innerHTML = xhr.responseText;
                }
            };
            xhr.send("class=" + selectedClass);
        }
    </script>
  </body>
</html>
