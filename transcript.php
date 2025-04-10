<?php include('components/students_logic.php');

// Fetch user ID from session
$user_id = $_SESSION['user_id'];


// Fetch student details from the database
$student_details = [];
$sql = "SELECT id, name, gender, dob, address, state, class, session, arm FROM students WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->bind_result($student_details['id'], $student_details['name'], $student_details['gender'], 
                       $student_details['dob'], $student_details['address'], $student_details['state'], 
                       $student_details['class'], $student_details['session'], $student_details['arm']);
    $stmt->fetch();
    $stmt->close();
} else {
    die("Failed to fetch student details: " . $conn->error);
}


//Transcript data
// Query all records for the logged in student, ordered by academic session and term.
$student_id = $_SESSION['user_id'];
$sql = "SELECT * FROM mastersheet WHERE id = '$student_id' ORDER BY csession, term, subject";
$result = $conn->query($sql);

$transcriptData = [];
$studentName = "";

// Process the result set.
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (empty($studentName)) {
            $studentid = $row['id'];
            $studentName = $row['name'];
        }
        $csession = $row['csession'];
        $term     = $row['term'];
        // Group records by academic session and term.
        $transcriptData[$csession][$term][] = $row;
    }
} else {
    echo "No transcript data available.";
    exit();
}


// Fetch tuckshop balance for the student
$sql = "SELECT vbalance FROM tuck WHERE regno = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->bind_result($student_details['vbalance']);
    $stmt->fetch();
    $stmt->close();
} else {
    die("Failed to fetch tuckshop details: " . $conn->error);
}



// Fetch calendar events
$events = [];
$sql = "SELECT date, title, description FROM calendar";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $formattedDate = date("m/d/Y", strtotime($row['date']));
        $events[$formattedDate] = [
            'title' => $row['title'],
            'description' => $row['description']
        ];
    }
    $result->free();
} else {
    die("Failed to fetch calendar events: " . $conn->error);
}



$student_id = $_SESSION['user_id'];
$student_class = "";
$timetable = [];
$today = date('l'); // Get current day

// Ensure the database connection is valid
if ($conn) {
    // Fetch student class
    $stmt = $conn->prepare("SELECT Class FROM students WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $stmt->bind_result($student_class);
        $stmt->fetch();
        $stmt->close();
    } else {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt = $conn->prepare("SELECT arm FROM students WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $stmt->bind_result($student_arm);
        $stmt->fetch();
        $stmt->close();
    } else {
        die("Error preparing statement: " . $conn->error);
    }

   // Fetch timetable for today only
if (!empty($student_class) && !empty($student_arm)) {
    $stmt = $conn->prepare("SELECT starttime, endtime, subject FROM timetable WHERE class = ? AND arm = ? AND day = ? ORDER BY starttime ASC");
    if ($stmt) {
        $stmt->bind_param("sss", $student_class, $student_arm, $today);
        $stmt->execute();
        $stmt->bind_result($starttime, $endtime, $subject);
        
        while ($stmt->fetch()) {
            $time_slot = date("h:i A", strtotime($starttime)) . " - " . date("h:i A", strtotime($endtime));
            $timetable[] = ['subject' => $subject, 'time' => $time_slot];
        }
        $stmt->close();
    } else {
        die("Error preparing statement: " . $conn->error);
    }
} else {
    die("Database connection error.");
}
}


// Fetch tuck shop transactions for the student
$transactions = [];
$sql = "SELECT productname, units, amount, transactiondate FROM transactiondetails WHERE transactionID = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->bind_result($productname, $units, $amount, $transactiondate);
    
    while ($stmt->fetch()) {
        $transactions[] = [
            'productname' => $productname,
            'units' => $units,
            'amount' => $amount,
            'transactiondate' => $transactiondate
        ];
    }
    $stmt->close();
} else {
    die("Failed to fetch tuck shop transactions: " . $conn->error);
}


// Fetch the logged-in student's name
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name FROM students WHERE id=?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($student_name);
$stmt->fetch();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Transcript</title>
    <meta
      content="width=device-width, initial-scale=1.0, shrink-to-fit=no"
      name="viewport"
    />
    <link
      rel="icon"
      href="assets/img/kaiadmin/favicon.ico"
      type="image/x-icon"
    />

    <!-- Fonts and icons -->
    <script src="assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
      WebFont.load({
        google: { families: ["Public Sans:300,400,500,600,700"] },
        custom: {
          families: [
            "Font Awesome 5 Solid",
            "Font Awesome 5 Regular",
            "Font Awesome 5 Brands",
            "simple-line-icons",
          ],
          urls: ["assets/css/fonts.min.css"],
        },
        active: function () {
          sessionStorage.fonts = true;
        },
      });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/plugins.min.css" />
    <link rel="stylesheet" href="assets/css/kaiadmin.min.css" />

    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link rel="stylesheet" href="assets/css/demo.css" />
    <link rel="stylesheet" href="components/students_style.css" />

  </head>
  <body>
    <div class="wrapper">
      <!-- Sidebar -->
      <?php 
      
      $role=isset($_SESSION['role']) ? $_SESSION['role']:'';
      //set the appropriate url based on the user role
      if ($role ==='Student') {
        include('studentnav.php'); 
      }elseif ($role ==='Administrator'){
        include('adminnav.php'); 
      }elseif ($role==='Alumni') {
        include('alumninav.php'); 
      }

      ?>
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
                <h3 class="fw-bold mb-3">Transcript</h3>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="students.php">Home</a></li>
                  <li class="breadcrumb-item active">Transcript</li>
              </ol>
              </div>
           
            </div>

         
              
          
            <div class="col-md-12">
                <div class="card">
                  <div class="card-header">
                  <h4 class="card-title">Academic Transcript
                    </h4>   
                  </div>
                  <div class="card-body">
                  
                  <div class="text-center mb-4">
            <h2 class="h2">School Name</h2>
            <h6 class="h6">School Address and contact</h6>
            <h2 class="h4"><?php echo htmlspecialchars($studentid); ?> | <?php echo htmlspecialchars($studentName); ?></h2>
          
        </div>
       
        
        <?php foreach($transcriptData as $csession => $terms): ?>
            <?php 
                // Retrieve the first record of the first term to get class and arm details.
                $firstTerm = reset($terms);
                $firstRecord = reset($firstTerm);
                $studentClass = $firstRecord['class'];
                $studentArm = $firstRecord['arm'];
            ?>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white text-center">
                    <h3 class="mb-0">Academic Session: <?php echo htmlspecialchars($csession); ?></h3>
                    <p class="mb-0">Class: <?php echo htmlspecialchars($studentClass); ?> | Arm: <?php echo htmlspecialchars($studentArm); ?></p>
                </div>
                <div class="card-body">
                    <?php foreach($terms as $term => $records): ?>
                        <div class="mb-4">
                            <h4 class="mb-3">Term: <?php echo htmlspecialchars($term); ?></h4>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Subject</th>
                                            <th>CA1</th>
                                            <th>CA2</th>
                                            <th>Exam</th>
                                            <th>Last Cum.</th>
                                            <th>Total</th>
                                            <th>Average</th>
                                            <th>Grade</th>
                                            <th>Remark</th>
                                            
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($records as $record): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($record['subject']); ?></td>
                                                <td><?php echo htmlspecialchars($record['ca1']); ?></td>
                                                <td><?php echo htmlspecialchars($record['ca2']); ?></td>
                                                <td><?php echo htmlspecialchars($record['exam']); ?></td>
                                                <td><?php echo htmlspecialchars($record['lastcum']); ?></td>
                                                <td><?php echo htmlspecialchars($record['total']); ?></td>
                                                <td><?php echo htmlspecialchars($record['average']); ?></td>
                                                <td><?php echo htmlspecialchars($record['grade']); ?></td>
                                                <td><?php echo htmlspecialchars($record['remark']); ?></td>
                                                
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
   
        <div class="text-center mb-4">
            <a href="download_transcript.php" class="btn btn-success btn-lg">Download Transcript</a>
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
