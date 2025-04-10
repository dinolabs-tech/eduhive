<?php include('components/admin_logic.php');

// ADD QUESTION ==============================

// Process update form submission
if (isset($_POST['update_question'])) {
  $que_id   = $conn->real_escape_string($_POST['question_id']);
  $que_desc = $conn->real_escape_string($_POST['que_desc']);
  $ans1     = $conn->real_escape_string($_POST['ans1']);
  $ans2     = $conn->real_escape_string($_POST['ans2']);
  $ans3     = $conn->real_escape_string($_POST['ans3']);
  $ans4     = $conn->real_escape_string($_POST['ans4']);
  $true_ans = $conn->real_escape_string($_POST['true_ans']);

  // Convert letter to number (A=1, B=2, C=3, D=4)
  $true_ans_num = 0;
  switch (strtoupper($true_ans)) {
      case 'A': $true_ans_num = 1; break;
      case 'B': $true_ans_num = 2; break;
      case 'C': $true_ans_num = 3; break;
      case 'D': $true_ans_num = 4; break;
      default: $true_ans_num = 1; break;
  }

  $update_query = "UPDATE question 
                   SET que_desc='$que_desc', ans1='$ans1', ans2='$ans2', ans3='$ans3', ans4='$ans4', true_ans='$true_ans_num'
                   WHERE que_id='$que_id'";
  if ($conn->query($update_query) === TRUE) {
      $update_message = "Question updated successfully!";
  } else {
      $update_message = "Error updating question: " . $conn->error;
  }
}

// Get distinct values for filter dropdowns from the question table
$distinctClasses = [];
$result = $conn->query("SELECT DISTINCT class FROM question");
if ($result) {
  while ($row = $result->fetch_assoc()) {
      $distinctClasses[] = $row['class'];
  }
}

$distinctArms = [];
$result = $conn->query("SELECT DISTINCT arm FROM question");
if ($result) {
  while ($row = $result->fetch_assoc()) {
      $distinctArms[] = $row['arm'];
  }
}

$distinctTerms = [];
$result = $conn->query("SELECT DISTINCT term FROM question");
if ($result) {
  while ($row = $result->fetch_assoc()) {
      $distinctTerms[] = $row['term'];
  }
}

$distinctSessions = [];
$result = $conn->query("SELECT DISTINCT session FROM question");
if ($result) {
  while ($row = $result->fetch_assoc()) {
      $distinctSessions[] = $row['session'];
  }
}

$distinctSubject = [];
$result = $conn->query("SELECT DISTINCT subject FROM question");
if ($result) {
  while ($row = $result->fetch_assoc()) {
      $distinctSubject[] = $row['subject'];
  }
}

// Build filter conditions based on GET parameters for main question query
$where = [];
if (isset($_GET['class']) && $_GET['class'] !== "") {
  $where[] = "class='" . $conn->real_escape_string($_GET['class']) . "'";
}
if (isset($_GET['arm']) && $_GET['arm'] !== "") {
  $where[] = "arm='" . $conn->real_escape_string($_GET['arm']) . "'";
}
if (isset($_GET['term']) && $_GET['term'] !== "") {
  $where[] = "term='" . $conn->real_escape_string($_GET['term']) . "'";
}
if (isset($_GET['session']) && $_GET['session'] !== "") {
  $where[] = "session='" . $conn->real_escape_string($_GET['session']) . "'";
}
if (isset($_GET['subject']) && $_GET['subject'] !== "") {
  $where[] = "subject='" . $conn->real_escape_string($_GET['subject']) . "'";
}


$query = "SELECT * FROM question";
if (count($where) > 0) {
  $query .= " WHERE " . implode(" AND ", $where);
}

$sqdel = $conn->query($query);


?>

<!DOCTYPE html>
<html lang="en">
<?php include('head.php');?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>

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
                <h3 class="fw-bold mb-3">Modify Question</h3>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                  <li class="breadcrumb-item active">CBT</li>
                  <li class="breadcrumb-item active">Modify Question</li>
              </ol>
              </div>
           
            </div>

            <!-- BULK UPLOAD ============================ -->
            <div class="row">
             
             <div class="col-md-12">
               <div class="card card-round">
                 <div class="card-header">
                   <div class="card-head-row">
                     <div class="card-title">Filter Question</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">
                  
                   <form method="GET" action="">
                      <div class="mb-3">
                        <select name="class" id="class" class="form-select">
                          <option value="">Select Class</option>
                          <?php foreach ($distinctClasses as $classOption): ?>
                            <option value="<?php echo htmlspecialchars($classOption); ?>" <?php if(isset($_GET['class']) && $_GET['class'] == $classOption) echo 'selected'; ?>>
                              <?php echo htmlspecialchars($classOption); ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="mb-3">
                        <select name="arm" id="arm" class="form-select">
                          <option value="">Select Arm</option>
                          <?php foreach ($distinctArms as $armOption): ?>
                            <option value="<?php echo htmlspecialchars($armOption); ?>" <?php if(isset($_GET['arm']) && $_GET['arm'] == $armOption) echo 'selected'; ?>>
                              <?php echo htmlspecialchars($armOption); ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="mb-3">
                        <select name="term" id="term" class="form-select">
                          <option value="">Select Term</option>
                          <?php foreach ($distinctTerms as $termOption): ?>
                            <option value="<?php echo htmlspecialchars($termOption); ?>" <?php if(isset($_GET['term']) && $_GET['term'] == $termOption) echo 'selected'; ?>>
                              <?php echo htmlspecialchars($termOption); ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="mb-3">
                        <select name="session" id="session" class="form-select">
                          <option value="">Select Session</option>
                          <?php foreach ($distinctSessions as $sessionOption): ?>
                            <option value="<?php echo htmlspecialchars($sessionOption); ?>" <?php if(isset($_GET['session']) && $_GET['session'] == $sessionOption) echo 'selected'; ?>>
                              <?php echo htmlspecialchars($sessionOption); ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="mb-3">
                        <select name="subject" id="subject" class="form-select">
                          <option value="">Select Subject</option>
                          <?php foreach ($distinctSubject as $subjectOption): ?>
                            <option value="<?php echo htmlspecialchars($subjectOption); ?>" <?php if(isset($_GET['session']) && $_GET['session'] == $subjectOption) echo 'selected'; ?>>
                              <?php echo htmlspecialchars($subjectOption); ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success"><span class="btn-label">
                        <i class="fa fa-filter"></i> Filter Questions</button>
                      </div>
                    </form>
                  

                   </div>
                 </div>
               </div>
             </div>


           </div>

         

           <div class="row">
             
             <div class="col-md-12">
               <div class="card card-round">
                 <div class="card-header">
                   <div class="card-head-row">
                     <div class="card-title">Modify Question</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">
                  
                                     <!-- Content Area -->
                           
                        <?php 
                          if(isset($update_message)) { 
                            echo '<div class="alert alert-info">' . htmlspecialchars($update_message) . '</div>'; 
                          } 
                        ?>
                        <div class="table-responsive">
                        <table  id="multi-filter-select" class="table table-bordered table-striped">
                          <thead class="table-dark">
                            <tr class="text-center">
                              <!-- You can add a header here if needed -->
                            </tr>
                            <tr class="text-center">
                              <th rowspan="2">Questions</th>
                              <th colspan="5">Options</th>
                              <th width="50px">Setting</th>
                            </tr>
                            <tr class="text-center">
                              <th>A</th>
                              <th>B</th>
                              <th>C</th>
                              <th>D</th>
                              <th>Answer</th>
                              <th>Action</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php
                            while ($rowdel = $sqdel->fetch_assoc()) {
                                $q_idt = $rowdel['que_id'];
                                $qst   = $rowdel['que_desc']; // may contain WYSIWYG HTML
                                $ans1  = $rowdel['ans1'];
                                $ans2  = $rowdel['ans2'];
                                $ans3  = $rowdel['ans3'];
                                $ans4  = $rowdel['ans4'];
                                $ts    = $rowdel['true_ans'];
                                $tr1   = '';
                                switch ($ts) {
                                    case 1: $tr1 = 'A'; break;
                                    case 2: $tr1 = 'B'; break;
                                    case 3: $tr1 = 'C'; break;
                                    case 4: $tr1 = 'D'; break;
                                }
                                
                                echo '<tr>
                                        <td>
                                          <!-- Display the formatted (WYSIWYG) content -->
                                          <div class="wysiwyg-content">' . $qst . '</div>
                                        </td>
                                        <td>
                                        <div class="wysiwyg-content"> ' . $ans1 . '</div>
                                        </td>
                                        <td>
                                        <div class="wysiwyg-content"> ' . $ans2 . '</div>
                                        </td>
                                        <td>
                                        <div class="wysiwyg-content"> ' . $ans3 . '</div>
                                        </td>
                                        <td>
                                        <div class="wysiwyg-content"> ' . $ans4 . '</div>
                                        </td>
                                        <td class="text-center">' . $tr1 . '</td>
                                        <td class="text-center">
                                          <a class="btn btn-sm btn-warning edit-btn" 
                                            data-id="' . $q_idt . '" 
                                            data-que="' . htmlspecialchars($qst, ENT_QUOTES) . '"
                                            data-ans1="' . htmlspecialchars($ans1, ENT_QUOTES) . '"
                                            data-ans2="' . htmlspecialchars($ans2, ENT_QUOTES) . '"
                                            data-ans3="' . htmlspecialchars($ans3, ENT_QUOTES) . '"
                                            data-ans4="' . htmlspecialchars($ans4, ENT_QUOTES) . '"
                                            data-trueans="' . $tr1 . '">
                                            <span class="btn-label">
                        <i class="fa fa-edit"></i>
                                          </a>
                                          <a class="btn btn-sm btn-danger" href="quedel.php?delid=' . $q_idt . '"><span class="btn-label">
                        <i class="fa fa-trash"></i></a>
                                        </td>
                                      </tr>';
                            }
                                    ?>
                                  </tbody>
                                </table>

                                          </div>
                                          <!-- Edit Form Container (initially hidden) -->
                                          <div id="editFormContainer" class="card mt-4" style="display: none;">
                                            <div class="card-header">
                                              Edit Question
                                            </div>
                                            <div class="card-body">
                                              <form id="editForm" method="POST" action="">
                                                <input type="hidden" name="question_id" id="editQuestionId">
                                                <div class="mb-3">
                                                  <label for="editQueDesc" class="form-label">Question</label>
                                                  <textarea class="form-control" name="que_desc" id="editQueDesc" rows="3" required></textarea>
                                                </div>
                                                <div class="mb-3">
                                                  <label for="editAns1" class="form-label">Option A</label>
                                                  <textarea class="form-control" name="ans1" id="editAns1" required></textarea>
                                                </div>
                                                <div class="mb-3">
                                                  <label for="editAns2" class="form-label">Option B</label>
                                                  <textarea class="form-control" name="ans2" id="editAns2" required></textarea>
                                                </div>
                                                <div class="mb-3">
                                                  <label for="editAns3" class="form-label">Option C</label>
                                                  <textarea class="form-control" name="ans3" id="editAns3" required></textarea>
                                                </div>
                                                <div class="mb-3">
                                                  <label for="editAns4" class="form-label">Option D</label>
                                                  <textarea class="form-control" name="ans4" id="editAns4" required></textarea>
                                                </div>
                                                <div class="mb-3">
                                                  <label for="editTrueAns" class="form-label">Correct Answer</label>
                                                  <select class="form-select" name="true_ans" id="editTrueAns" required>
                                                    <option value="A">A</option>
                                                    <option value="B">B</option>
                                                    <option value="C">C</option>
                                                    <option value="D">D</option>
                                                  </select>
                                                </div>
                                                <div class="d-flex gap-2">
                                                  <button type="submit" name="update_question" class="btn btn-success"><span class="btn-label">
                                                  <i class="fa fa-sync-alt"></i>Update Question</button>
                                                  <button type="button" id="cancelEdit" class="btn btn-secondary"> <span class="btn-label">
                                                  <i class="fa fa-undo"></i> Cancel</button>
                                                </div>
                                              </form>
                                            </div>
                                          </div>
                                          <!-- End Edit Form Container -->
                                    

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
    document.addEventListener('DOMContentLoaded', function() {
      // Listen for clicks on all edit buttons
      const editButtons = document.querySelectorAll('.edit-btn');
      const editFormContainer = document.getElementById('editFormContainer');
      
      editButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
          // Retrieve data attributes from the clicked button
          const questionId = this.getAttribute('data-id');
          const queDesc    = this.getAttribute('data-que');
          const ans1       = this.getAttribute('data-ans1');
          const ans2       = this.getAttribute('data-ans2');
          const ans3       = this.getAttribute('data-ans3');
          const ans4       = this.getAttribute('data-ans4');
          const trueAns    = this.getAttribute('data-trueans');
          
          // Populate the form fields
          document.getElementById('editQuestionId').value = questionId;
          document.getElementById('editQueDesc').value    = queDesc;
          document.getElementById('editAns1').value       = ans1;
          document.getElementById('editAns2').value       = ans2;
          document.getElementById('editAns3').value       = ans3;
          document.getElementById('editAns4').value       = ans4;
          document.getElementById('editTrueAns').value    = trueAns;
          
          // Show the edit form
          editFormContainer.style.display = 'block';
          editFormContainer.scrollIntoView({ behavior: 'smooth' });
        });
      });
      
      // Cancel button hides the edit form
      document.getElementById('cancelEdit').addEventListener('click', function() {
        document.getElementById('editFormContainer').style.display = 'none';
      });
    });
  </script>

    <script>
      tinymce.init({
        selector: '#editQueDesc, #editAns1, #editAns2, #editAns3, #editAns4',
        menubar: false,
        toolbar: 'undo redo | formatselect | bold italic underline superscript subscript | alignleft aligncenter alignright | bullist numlist outdent indent | table',
        plugins: 'lists',
        branding: false
      });
    </script>


<script>
document.addEventListener('DOMContentLoaded', function() {
  // Listen for clicks on all edit buttons
  const editButtons = document.querySelectorAll('.edit-btn');
  const editFormContainer = document.getElementById('editFormContainer');
  
  editButtons.forEach(function(btn) {
    btn.addEventListener('click', function() {
      // Retrieve data attributes from the clicked button
      const questionId = this.getAttribute('data-id');
      const queDesc    = this.getAttribute('data-que');
      const ans1       = this.getAttribute('data-ans1');
      const ans2       = this.getAttribute('data-ans2');
      const ans3       = this.getAttribute('data-ans3');
      const ans4       = this.getAttribute('data-ans4');
      const trueAns    = this.getAttribute('data-trueans');
      
      // Populate the form fields using TinyMCE's API for rich text fields
      document.getElementById('editQuestionId').value = questionId;
      tinymce.get('editQueDesc').setContent(queDesc);
      tinymce.get('editAns1').setContent(ans1);
      tinymce.get('editAns2').setContent(ans2);
      tinymce.get('editAns3').setContent(ans3);
      tinymce.get('editAns4').setContent(ans4);
      document.getElementById('editTrueAns').value = trueAns;
      
      // Show the edit form
      editFormContainer.style.display = 'block';
      editFormContainer.scrollIntoView({ behavior: 'smooth' });
    });
  });
  
  // Cancel button hides the edit form
  document.getElementById('cancelEdit').addEventListener('click', function() {
    document.getElementById('editFormContainer').style.display = 'none';
  });
});

</script>


  </body>
</html>
