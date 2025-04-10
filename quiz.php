<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

$loginid = $_SESSION['user_id'];
error_reporting(1);
$time = date("h:i:s");
$date = date("l, F j, Y");
$tdate = $time . '  ' . $date;


// Instead of using a test ID, we use the subject selected by the user.
$subject = isset($_GET['subid']) ? $_GET['subid'] : (isset($_SESSION['subject']) ? $_SESSION['subject'] : null);
$submit = isset($_POST['submit']) ? $_POST['submit'] : null;
$ans    = isset($_POST['ans']) ? $_POST['ans'] : null;

// If a subject was passed in the URL, store it in the session.
if ($subject) {
    $_SESSION['subject'] = $subject;
} else {
    header("location:students.php");
    exit;
}

// Escape the subject value for queries.
$subjectEsc = mysqli_real_escape_string($conn, $subject);


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="author" content="Wink Hosting (www.winkhosting.com)">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $_SESSION['SUR_NAME'] . ' ' . $_SESSION['OTHER_NAME']; ?></title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .exam-card {
      margin-top: 30px;
    }
    .timer {
      font-size: 1.2rem;
      font-weight: bold;
      color: #dc3545;
    }
  </style>
</head>
<body>
  <script> alert($loginid);</script>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand" href="#">Online Exam Portal</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
         data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" 
         aria-label="Toggle navigation">
         <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
         <ul class="navbar-nav ms-auto">
           <li class="nav-item">
             <a class="nav-link" href="students.php">Dashboard</a>
           </li>
          
         </ul>
      </div>
    </div>
  </nav>
  
  <!-- Main Content -->
  <div class="container exam-card">
    <div class="card shadow">
      <div class="card-header bg-primary text-white">
        <div class="row">
          <div class="col-md-6">
            <h4>Exam Instructions</h4>
          </div>
          <div class="col-md-6 text-md-end">
            <!-- Timer Include (ensure timer.php outputs the timer HTML) -->
            <?php include 'timer.php'; ?>
          </div>
        </div>
      </div>
      <div class="card-body">
        
        <p><strong>Time To Be Used:</strong> <span class="text-danger fs-4"><?php echo $exam_duration_minutes; ?> Mins</span></p>

        <hr>
        <?php 
        // Create a timer record for the user if one does not exist.
        $timeri = date('H:i:s');
        $timi = mysqli_query($conn, "SELECT * FROM timer WHERE studentid = '$loginid'");
        $rowtimer = mysqli_fetch_assoc($timi);
        if (!$rowtimer || $rowtimer['studentid'] != $loginid) {
            mysqli_query($conn, "INSERT INTO timer (studentid, timer) VALUES ('$loginid', '$timeri')");
        }

        // Check if the student already took the exam.
        $scoli = mysqli_query($conn, "SELECT * FROM mst_result WHERE login = '$loginid' and subject='$subjectEsc' ");
        $rowli = mysqli_fetch_assoc($scoli);
        if ($rowli && $rowli['login'] == $loginid) {
          // User has already taken the exam.
          echo '<div class="alert alert-warning" role="alert">
                  You have already taken the exam; don\'t be over-smart!
                </div>';
          echo '<div class="mb-3">
                  <a href="students.php" class="btn btn-secondary">Go Back</a>
                </div>';
          exit; // Optionally, exit after showing the message.
           
           
        } else {
            // Initialize question number and correct answers count.
            if (!isset($_SESSION['qn'])) {
                $_SESSION['qn'] = 0;
                // Delete any previous answers for this session.
                mysqli_query($conn, "DELETE FROM mst_useranswer WHERE sess_id='" . session_id() . "'");
                $_SESSION['trueans'] = 0;
                } else {
                  if ($submit == 'Next Question') {
                    // Get the selected answer (default to 0 if not set)
                    $selectedAnswer = isset($ans) ? $ans : 0;
                    
                    // Fetch the current question based on subject and question number
                    $rs = mysqli_query($conn, "SELECT * FROM question WHERE subject='$subjectEsc'");
                    mysqli_data_seek($rs, $_SESSION['qn']);
                    $row = mysqli_fetch_row($rs); // current question row
                    
                    // Check if an answer record already exists for this question number
                    $uaResult = mysqli_query($conn, "SELECT * FROM mst_useranswer WHERE sess_id='" . session_id() . "' ORDER BY sess_id");
                    $uaCount = mysqli_num_rows($uaResult);
                    
                    if ($uaCount > $_SESSION['qn']) {
                        // A record exists: update it if the answer has changed.
                        // First, get all user answer records into an array (ensure ordering is consistent)
                        $uaRecords = [];
                        while ($ua = mysqli_fetch_assoc($uaResult)) {
                            $uaRecords[] = $ua;
                        }
                        $currentUA = $uaRecords[$_SESSION['qn']];
                        
                        // If the answer is different, update the record and adjust score if necessary.
                        if ($currentUA['your_ans'] != $selectedAnswer) {
                            // If the previous answer was correct, decrement the score if it is now changed to a wrong answer.
                            if ($currentUA['your_ans'] == $row[7] && $selectedAnswer != $row[7]) {
                                $_SESSION['trueans']--;
                            }
                            // If the new answer is correct and the old answer was not correct, increment the score.
                            if ($selectedAnswer == $row[7] && $currentUA['your_ans'] != $row[7]) {
                                $_SESSION['trueans']++;
                            }
                            mysqli_query($conn, "UPDATE mst_useranswer SET your_ans='$selectedAnswer' WHERE sess_id='" . $currentUA['sess_id'] . "'");
                        }
                    } else {
                        // No record exists for this question: insert a new record.
                        mysqli_query($conn, "INSERT INTO mst_useranswer(sess_id, subject, que_des, ans1, ans2, ans3, ans4, true_ans, your_ans) 
                            VALUES ('" . session_id() . "', '$subjectEsc', '" . addslashes($row[2]) . "', '" . addslashes($row[3]) . "', '" . addslashes($row[4]) . "', '" . addslashes($row[5]) . "', '" . addslashes($row[6]) . "', '" . addslashes($row[7]) . "', '$selectedAnswer')");
                        if ($selectedAnswer == $row[7]) {
                            $_SESSION['trueans']++;
                        }
                    }
                    $_SESSION['qn']++;
                    } elseif ($submit == 'Previous Question') {
                    // Instead of deleting the record, simply go back one question.
                    if ($_SESSION['qn'] > 0) {
                        $_SESSION['qn']--;
                    }
                   } elseif ($submit == 'Submit Exam') {
                  // For submission, record answer even if blank.
                  $selectedAnswer = isset($ans) ? $ans : -1;
              
              
                  // Fetch the current question to record the final answer.
                  $rs = mysqli_query($conn, "SELECT * FROM question WHERE subject='$subjectEsc'");
                  mysqli_data_seek($rs, $_SESSION['qn']);
                  $row = mysqli_fetch_row($rs);    
                  
                  // Insert the final answer into the database.
                  mysqli_query($conn, "INSERT INTO mst_useranswer(sess_id, subject, que_des, ans1, ans2, ans3, ans4, true_ans, your_ans) 
                      VALUES ('" . session_id() . "', '$subjectEsc', '" . addslashes($row[2]) . "', '" . addslashes($row[3]) . "', '" . addslashes($row[4]) . "', '" . addslashes($row[5]) . "', '" . addslashes($row[6]) . "', '" . addslashes($row[7]) . "', '$selectedAnswer')");
              
                  // Increment correct answers if the selected answer matches the true answer.
                  if ($selectedAnswer != -1 && $selectedAnswer == $row[7]) {
                      $_SESSION['trueans']++;
                  }
              
                  // Increment question number for total count.
                  $_SESSION['qn']++;
              
                  if (!isset($_SESSION['trueans']) || $_SESSION['trueans'] === '') {
                    die("Error: Score is not set.");
                }
                

                  // Store results in the database.
                  mysqli_query($conn, "INSERT INTO mst_result(login, subject, test_date, score) 
                      VALUES('$loginid', '$subjectEsc', '$tdate', " . $_SESSION['trueans'] . ")");
              
                  // Display the exam results.
                  echo "<div class='container mt-5'>";
                  echo "<div class='card shadow'>";
                  echo "<div class='card-header bg-success text-white text-center'><h2>Exam Results for " . htmlspecialchars($subjectEsc) . "</h2></div>";
                  echo "<div class='card-body text-center'>";
                  echo "<table class='table table-bordered w-50 mx-auto'>";
                  echo "<tr class='table-info'><td>Total Questions:</td><td>" . $_SESSION['qn'] . "</td></tr>";
                  echo "<tr class='table-success'><td>Correct Answers:</td><td>" . $_SESSION['trueans'] . "</td></tr>";
                  $wrong_answers = $_SESSION['qn'] - $_SESSION['trueans'];
                  echo "<tr class='table-danger'><td>Wrong Answers:</td><td>" . $wrong_answers . "</td></tr>";
                  $score_percentage = ($_SESSION['trueans'] / $_SESSION['qn']) * 100;
                  echo "<tr class='table-primary'><td>Score (%):</td><td>" . number_format($score_percentage, 2) . "%</td></tr>";
                  echo "</table>";
                  echo "<a href='students.php' class='btn btn-primary mt-3'>Return to Dashboard</a>";
                  echo "</div>";
                  echo "</div>";
                  echo "</div>";
              
                  // Clear session variables after displaying results.
                  unset($_SESSION['qn']);
                  unset($_SESSION['subject']);
                  unset($_SESSION['trueans']);
              
                  // Prevent further form display by exiting the script here.
                  echo "</body></html>";
                  $conn->close();
                  exit;
              }
            }
             
            // Fetch questions for the selected subject.
            $rs = mysqli_query($conn, "SELECT * FROM question WHERE subject='$subjectEsc'");
            $rs = mysqli_query($conn, "SELECT * FROM question WHERE subject='$subjectEsc'");
              if ($_SESSION['qn'] > mysqli_num_rows($rs) - 1) {
                  unset($_SESSION['qn']);
                  echo "<div class='alert alert-info text-center'>
                          <h4>No Question(s) for this subject yet. Kindly check back later.</h4>
                          <a href='students.php' class='btn btn-secondary'>Go Back</a>
                        </div>";
                  exit;
              }
              mysqli_data_seek($rs, $_SESSION['qn']);
              $row = mysqli_fetch_row($rs);

              // Try to fetch the user’s answer (if it exists) for this question.
              $uaQuery = "SELECT your_ans FROM mst_useranswer WHERE sess_id='" . session_id() . "' ORDER BY sess_id LIMIT " . $_SESSION['qn'] . ",1";
              $uaResult = mysqli_query($conn, $uaQuery);
              $uaRow = mysqli_fetch_assoc($uaResult);
              $user_answer = isset($uaRow['your_ans']) ? $uaRow['your_ans'] : 0;

            // Display the question form.
            echo "<form name='myfm' method='post' action='quiz.php'>";
            echo "<div class='mb-4'>";
            $n = $_SESSION['qn'] + 1;
            echo "<h5>Question " . $n . ":</h5>";
            echo "<p>" . $row[2] . "</p>";
            echo "</div>";
            echo "<div class='form-check mb-2'>";
            echo "<input class='form-check-input' type='radio' name='ans' id='ans1' value='1' " . ($user_answer == 1 ? "checked" : "") . ">";
            echo "<label class='form-check-label' for='ans1'>" . $row[3] . "</label>";
            echo "</div>";
            
            echo "<div class='form-check mb-2'>";
            echo "<input class='form-check-input' type='radio' name='ans' id='ans2' value='2' " . ($user_answer == 2 ? "checked" : "") . ">";
            echo "<label class='form-check-label' for='ans2'>" . $row[4] . "</label>";
            echo "</div>";
            
            echo "<div class='form-check mb-2'>";
            echo "<input class='form-check-input' type='radio' name='ans' id='ans3' value='3' " . ($user_answer == 3 ? "checked" : "") . ">";
            echo "<label class='form-check-label' for='ans3'>" . $row[5] . "</label>";
            echo "</div>";
            
            echo "<div class='form-check mb-4'>";
            echo "<input class='form-check-input' type='radio' name='ans' id='ans4' value='4' " . ($user_answer == 4 ? "checked" : "") . ">";
            echo "<label class='form-check-label' for='ans4'>" . $row[6] . "</label>";
            echo "</div>";
            
            echo "<div class='d-flex justify-content-between'>";
            if ($_SESSION['qn'] > 0) {
                echo "<button type='submit' name='submit' value='Previous Question' class='btn btn-outline-secondary'>Previous Question</button>";
            }
            if ($_SESSION['qn'] < mysqli_num_rows($rs) - 1) {
                echo "<button type='submit' name='submit' value='Next Question' class='btn btn-primary'>Next Question</button>";
            } else {
                echo "<button type='submit' name='submit' value='Submit Exam' class='btn btn-success'>Submit Exam</button>";
            }
            echo "</div>";
            echo "</form>";
        }
        ?>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-dark text-white mt-5 p-3 text-center">
    &copy; <?php echo date("Y"); ?> Online Exam Portal. All rights reserved.
  </footer>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
