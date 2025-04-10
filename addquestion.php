<?php include('components/admin_logic.php');

// ADD QUESTION ==============================
// Process new question form submission
$insert_message = "";
if (isset($_POST['add_question'])) {
    // Debugging: Log the raw POST data
    error_log("POST Data: " . print_r($_POST, true));

    // Escape and retrieve input values
    $que_desc  = $conn->real_escape_string($_POST['que_desc']);
    $ans1      = $conn->real_escape_string($_POST['ans1']);
    $ans2      = $conn->real_escape_string($_POST['ans2']);
    $ans3      = $conn->real_escape_string($_POST['ans3']);
    $ans4      = $conn->real_escape_string($_POST['ans4']);
    $true_ans  = $conn->real_escape_string($_POST['true_ans']);
    $class     = $conn->real_escape_string($_POST['class']);
    $arm       = $conn->real_escape_string($_POST['arm']);
    $subject   = $conn->real_escape_string($_POST['subject']);
    $term      = $conn->real_escape_string($_POST['term']);
    $sessionq  = $conn->real_escape_string($_POST['session']);
    
    // Convert true answer from letter to number (A=1, B=2, C=3, D=4)
    $true_ans_num = 0;
    switch (strtoupper($true_ans)) {
        case 'A': $true_ans_num = 1; break;
        case 'B': $true_ans_num = 2; break;
        case 'C': $true_ans_num = 3; break;
        case 'D': $true_ans_num = 4; break;
        default: $true_ans_num = 1; break;
    }
    
    // Insert the new question into the database
    $insert_query = "INSERT INTO question 
                    (que_desc, ans1, ans2, ans3, ans4, true_ans, class, arm, term, session, subject)
                    VALUES ('$que_desc', '$ans1', '$ans2', '$ans3', '$ans4', '$true_ans_num', '$class', '$arm', '$term', '$sessionq', '$subject')";
    
    if ($conn->query($insert_query) === TRUE) {
        $insert_message = "Question added successfully!";
    } else {
        $insert_message = "Error adding question: " . $conn->error;
        error_log("SQL Error: " . $conn->error); // Log SQL errors
    }
}

// Retrieve classes, arms, subjects, term, and session (unchanged)
$classes = [];
$result = $conn->query("SELECT * FROM class");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $classes[] = $row;
    }
}

$arms = [];
$result = $conn->query("SELECT * FROM arm");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $arms[] = $row;
    }
}

$subjects = [];
$result = $conn->query("SELECT * FROM subject");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
}

$current_term = "";
$result = $conn->query("SELECT cterm FROM `currentterm` LIMIT 1");
if ($result && $row = $result->fetch_assoc()) {
    $current_term = $row['cterm'];
}

$current_session = "";
$result = $conn->query("SELECT csession FROM currentsession LIMIT 1");
if ($result && $row = $result->fetch_assoc()) {
    $current_session = $row['csession'];
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include('head.php');?>

<!-- Load TinyMCE CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>

<body>
    <div class="wrapper">
        <?php include('adminnav.php');?>
        <div class="main-panel">
            <div class="main-header">
                <div class="main-header-logo">
                    <?php include('logo_header.php');?>
                </div>
                <?php include('navbar.php');?>
            </div>

            <div class="container">
                <div class="page-inner">
                    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
                        <div>
                            <h3 class="fw-bold mb-3">Add Question</h3>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                                <li class="breadcrumb-item active">CBT</li>
                                <li class="breadcrumb-item active">Add Question</li>
                            </ol>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-round">
                                <div class="card-header">
                                    <div class="card-head-row">
                                        <div class="card-title">Enter New Question</div>
                                    </div>
                                </div>
                                <div class="card-body pb-0">
                                    <div class="mb-4 mt-2">
                                        <?php 
                                        if (!empty($insert_message)) { 
                                            echo '<div class="alert alert-info">' . htmlspecialchars($insert_message) . '</div>'; 
                                        } 
                                        ?>
                                        <form method="POST" action="">
                                            <div class="mb-3">
                                                <label for="que_desc" class="form-label">Question</label>
                                                <textarea class="form-control" name="que_desc" id="que_desc" rows="3" required></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label for="ans1" class="form-label">Option A</label>
                                                <textarea class="form-control" name="ans1" id="ans1" required></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label for="ans2" class="form-label">Option B</label>
                                                <textarea class="form-control" name="ans2" id="ans2" required></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label for="ans3" class="form-label">Option C</label>
                                                <textarea class="form-control" name="ans3" id="ans3" required></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label for="ans4" class="form-label">Option D</label>
                                                <textarea class="form-control" name="ans4" id="ans4" required></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label for="true_ans" class="form-label">Correct Answer (Enter A, B, C, or D)</label>
                                                <select class="form-select" name="true_ans" id="true_ans" required>
                                                    <option value="">Select Correct Answer</option>
                                                    <option value="A">A</option>
                                                    <option value="B">B</option>
                                                    <option value="C">C</option>
                                                    <option value="D">D</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <select class="form-select" name="class" id="class" required>
                                                    <option value="">Select Class</option>
                                                    <?php foreach ($classes as $cls): ?>
                                                        <option value="<?php echo htmlspecialchars($cls['class']); ?>"><?php echo htmlspecialchars($cls['class']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <select class="form-select" name="arm" id="arm" required>
                                                    <option value="">Select Arm</option>
                                                    <?php foreach ($arms as $a): ?>
                                                        <option value="<?php echo htmlspecialchars($a['arm']); ?>"><?php echo htmlspecialchars($a['arm']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <select class="form-select" name="subject" id="subject" required>
                                                    <option value="">Select Subject</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <input type="text" class="form-control" name="term" id="term" value="<?php echo htmlspecialchars($current_term); ?>" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <input type="text" class="form-control" name="session" id="session" value="<?php echo htmlspecialchars($current_session); ?>" readonly>
                                            </div>
                                            <div class="d-grid gap-2">
                                                <input type="submit" name="add_question" class="btn btn-success" value="Add Question">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include('footer.php');?>
        </div>
        <?php include('cust-color.php');?>
    </div>
    <?php include('scripts.php');?>

    <!-- TinyMCE Initialization -->
    <script>
        tinymce.init({
            selector: '#que_desc, #ans1, #ans2, #ans3, #ans4',
            menubar: false,
            toolbar: 'undo redo | formatselect | bold italic underline superscript subscript | alignleft aligncenter alignright | bullist numlist outdent indent | table',
            plugins: 'lists',
            branding: false,
            setup: function (editor) {
                editor.on('change', function () {
                    editor.save();
                });
            }
        });

        document.querySelector('form').addEventListener('submit', function () {
            tinymce.triggerSave();
            // Debugging: Log TinyMCE content
            console.log("Question: ", tinymce.get('que_desc').getContent());
            console.log("Answer 1: ", tinymce.get('ans1').getContent());
            console.log("Answer 2: ", tinymce.get('ans2').getContent());
            console.log("Answer 3: ", tinymce.get('ans3').getContent());
            console.log("Answer 4: ", tinymce.get('ans4').getContent());
        });
    </script>

    <!-- Subject Filtering -->
    <script>
        const subjectsData = <?php echo json_encode($subjects); ?>;
        
        function filterSubjects() {
            const selectedClass = document.getElementById('class').value;
            const selectedArm = document.getElementById('arm').value;
            const subjectSelect = document.getElementById('subject');
            
            subjectSelect.innerHTML = '<option value="">Select Subject</option>';
            
            const filtered = subjectsData.filter(item => {
                return item.class === selectedClass && item.arm === selectedArm;
            });
            
            filtered.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.subject;
                opt.textContent = item.subject;
                subjectSelect.appendChild(opt);
            });
        }
        
        document.getElementById('class').addEventListener('change', filterSubjects);
        document.getElementById('arm').addEventListener('change', filterSubjects);
    </script>
</body>
</html>