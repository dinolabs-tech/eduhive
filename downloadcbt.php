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

// Get current session and term (assumed common for all students)
$stmt = $conn->prepare("SELECT csession FROM currentsession WHERE id = 1");
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($csession);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare("SELECT cterm FROM currentterm WHERE id = 1");
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($term);
$stmt->fetch();
$stmt->close();

// Query all students
$sql_students = "SELECT id, name, gender, class, arm FROM students";
$result_students = $conn->query($sql_students);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Results for All Students</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    .identity-header img { max-height: 110px; }
    .identity-photo img { max-height: 110px; }
    @media print { .no-print { display: none; } }
    .result-slip { page-break-after: always; }
  </style>
</head>
<body class="bg-light">
<div class="container my-4">
  <?php
  if ($result_students->num_rows > 0) {
      while ($student = $result_students->fetch_assoc()) {
          $id    = $student['id'];
          $name  = $student['name'];
          $class = $student['class'];
          $arm   = $student['arm'];

          // Calculate the total score for this student from mst_result
          $sql_score = "SELECT SUM(score) AS total_score FROM mst_result WHERE login = '$id'";
          $result_score = $conn->query($sql_score);
          $row_score = $result_score->fetch_assoc();
          $score = ($row_score && $row_score['total_score'] !== null) ? $row_score['total_score'] : 0;
          
          // Example: multiply total score by 4 (if required by your screening logic)
          $score1 = $score;

          // Define the maximum screening score. Adjust this value as needed.
          $maxScreeningScore = 60;
          $percentage = ($maxScreeningScore > 0) ? round(($score1 / $maxScreeningScore) * 100, 2) : 0;
          ?>
          <!-- Begin Result Slip -->
          <div class="result-slip my-5 p-3 border">
            <table border="0" width="100%">
              <tr>
                <td width="100">
                  <img style="border-radius: 10px;" height="110" width="95" src="logo.jpg" alt="Logo" />
                </td>
                <td valign="top" style="padding:10px; font-size:14px; text-align:center">
                  <b style="font-size:17px">DINOLABS TECH SERVICES</b><br>
                  <address>School address goes here</address>
                  <span style="font-size:15px;">(Computer Based Test)</span><br />
                  <b style="font-family:'Times New Roman', Times, serif;">Result Slip</b><br />
                  Class: <b><?php echo htmlspecialchars($class); ?></b> | Arm: <?php echo htmlspecialchars($arm); ?>
                </td>
                <td width="100"></td>
              </tr>
            </table>
            <hr />
            <!-- Student Details -->
            <div class="row my-3">
              <div class="col-md-12">
                <div style="text-align: center">
                  <strong><h3><?php echo htmlspecialchars($name); ?></h3></strong>
                </div>
              </div>
            </div>
            <div class="row my-3">
              <div class="col-md-4"><strong>REG. NO.:</strong> <?php echo htmlspecialchars($id); ?></div>
              <div class="col-md-4"><strong>CLASS:</strong> <?php echo htmlspecialchars($class); ?></div>
              <div class="col-md-4"><strong>ARM:</strong> <?php echo htmlspecialchars($arm); ?></div>
              <div class="col-md-4"><strong>SESSION:</strong> <?php echo htmlspecialchars($csession); ?></div>
              <div class="col-md-4"><strong>TERM:</strong> <?php echo htmlspecialchars($term); ?></div>
            </div>
            <!-- Subjects and Scores -->
            <div class="row my-3">
              <div class="col-12">
                <table class="table table-bordered">
                  <thead class="table-light">
                    <tr>
                      <th>SUBJECTS</th>
                      <th>SCORES</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    // Retrieve subject scores for this student
                    $sql_subjects = "SELECT * FROM mst_result WHERE login='$id'";
                    $result_subjects = $conn->query($sql_subjects);
                    if ($result_subjects->num_rows > 0) {
                        while ($subject = $result_subjects->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($subject['subject']) . "</td>";
                            echo "<td>" . htmlspecialchars($subject['score']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='2'>No scores available</td></tr>";
                    }
                    ?>
                    <tr class="table-secondary">
                      <td><strong>TOTAL</strong></td>
                      <td><strong><?php echo htmlspecialchars($score); ?></strong></td>
                    </tr>
                    <!-- Optionally, if you want to display the screening percentage:
                    <tr class="table-secondary">
                      <td><strong>Screening %</strong></td>
                      <td><strong><?php echo htmlspecialchars($percentage); ?>%</strong></td>
                    </tr>
                    -->
                  </tbody>
                </table>
              </div>
            </div>
            <!-- Print Button (will hide on printing) -->
            <div class="row my-3">
              <div class="col-md-6">
                <button onclick="window.print()" class="btn btn-primary no-print">Print Result</button>
              </div>
            </div>
          </div>
          <!-- End Result Slip -->
          <?php
      }
  } else {
      echo "<p>No students found.</p>";
  }
  ?>
</div>
<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
