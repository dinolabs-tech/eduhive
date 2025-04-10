<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Database connection
include 'db_connect.php';

// Check connection to the database
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role']; // e.g., "Administrator" or "Student"

// Determine which SQL query to run based on the user's role
if ($user_role === "Administrator") {
    // For administrators, fetch the staff name from the 'login' table
    $stmt = $conn->prepare("SELECT staffname FROM login WHERE id = ?");
} else {
    // For students, fetch the name from the 'students' table
    $stmt = $conn->prepare("SELECT name FROM students WHERE id = ?");
}

$stmt->bind_param("s", $user_id);
$stmt->execute();

// Bind the result to a variable
$stmt->bind_result($staff_name);
$stmt->fetch();
$stmt->close();
?>

<style>
    /* Existing styles maintained */
    span.float-right.summary_icon {
        font-size: 3rem;
        position: absolute;
        right: 1rem;
        color: #ffffff96;
    }
    .imgs {
        margin: .5em;
        max-width: calc(100%);
        max-height: calc(100%);
    }
    .imgs img {
        max-width: calc(100%);
        max-height: calc(100%);
        cursor: pointer;
    }
    #imagesCarousel, #imagesCarousel .carousel-inner, #imagesCarousel .carousel-item {
        height: 60vh !important;
        background: black;
    }
    #imagesCarousel .carousel-item.active {
        display: flex !important;
    }
    #imagesCarousel .carousel-item-next {
        display: flex !important;
    }
    #imagesCarousel .carousel-item img {
        margin: auto;
    }
    #imagesCarousel img {
        width: auto !important;
        height: auto !important;
        max-height: calc(100%) !important;
        max-width: calc(100%) !important;
    }

    /* New modern and sophisticated styles */
    .card-users {
        background: linear-gradient(135deg, #007bff, #00c6ff);
        color: white;
    }
    .card-topics {
        background: linear-gradient(135deg, #28a745, #5dd75d);
        color: white;
    }
    .card-categories {
        background: linear-gradient(135deg, #6f42c1, #a370f7);
        color: white;
    }
    .card {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
        border: none;
        border-radius: 10px;
    }
    .card:hover {
        transform: scale(1.05);
    }
    .card-body {
        padding: 1.5rem;
    }
    h2.welcome-text {
        font-weight: 700;
        color: #333;
        margin-bottom: 2rem;
    }
</style>

<div class="container-fluid">
    <div class="row mt-3 ml-3 mr-3">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="text-center welcome-text">Welcome back, <?php echo $staff_name; ?>!</h2>
                    <hr>
                    <div class="row">
                        <!-- Users Card -->
                        <div class="col-md-4 mb-4">
                            <div class="card card-users">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-4 d-flex align-items-center justify-content-center">
                                            <i class="fa fa-users fa-3x"></i>
                                        </div>
                                        <div class="col-8">
                                            <h3><b><?php echo $conn->query("SELECT * FROM users")->num_rows; ?></b></h3>
                                            <p class="mb-0"><b>Users</b></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Forum Topics Card -->
                        <div class="col-md-4 mb-4">
                            <div class="card card-topics">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-4 d-flex align-items-center justify-content-center">
                                            <i class="fa fa-comments fa-3x"></i>
                                        </div>
                                        <div class="col-8">
                                            <h3><b><?php echo $conn->query("SELECT * FROM topics")->num_rows; ?></b></h3>
                                            <p class="mb-0"><b>Forum Topics</b></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Categories Card -->
                        <div class="col-md-4 mb-4">
                            <div class="card card-categories">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-4 d-flex align-items-center justify-content-center">
                                            <i class="fa fa-tags fa-3x"></i>
                                        </div>
                                        <div class="col-8">
                                            <h3><b><?php echo $conn->query("SELECT * FROM categories")->num_rows; ?></b></h3>
                                            <p class="mb-0"><b>Categories</b></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Commented-out Tags Section (maintained as is) -->
                    <!--
                    <hr class="divider" style="max-width: 100%">
                    <h4><i class="fa fa-tags text-primary"></i> Tags</h4>
                    <div class="row">
                    <?php
                     $tags = $conn->query("SELECT * FROM categories order by name asc");
                     while($row = $tags->fetch_assoc()):
                    ?>
                        <div class="col-md-3">
                        <div class="card mb-3">
                            <div class="card-body">
                                <p>
                                    <large><i class="fa fa-tag text-primary"></i> <b><?php echo $row['name'] ?></b></large>
                                </p>
                                <hr class="divider" style="max-width: 100%">
                                <p><small><i><?php echo $row['description'] ?></i></small></p>
                            </div>
                        </div>
                        </div>
                    <?php endwhile; ?>
                    </div>
                    -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Existing JavaScript maintained
    $('#manage-records').submit(function(e){
        e.preventDefault();
        start_load();
        $.ajax({
            url: 'ajax.php?action=save_track',
            data: new FormData($(this)[0]),
            cache: false,
            contentType: false,
            processData: false,
            method: 'POST',
            type: 'POST',
            success: function(resp){
                resp = JSON.parse(resp);
                if(resp.status == 1){
                    alert_toast("Data successfully saved", 'success');
                    setTimeout(function(){
                        location.reload();
                    }, 800);
                }
            }
        });
    });
    $('#tracking_id').on('keypress', function(e){
        if(e.which == 13){
            get_person();
        }
    });
    $('#check').on('click', function(e){
        get_person();
    });
    function get_person(){
        start_load();
        $.ajax({
            url: 'ajax.php?action=get_pdetails',
            method: "POST",
            data: { tracking_id: $('#tracking_id').val() },
            success: function(resp){
                if(resp){
                    resp = JSON.parse(resp);
                    if(resp.status == 1){
                        $('#name').html(resp.name);
                        $('#address').html(resp.address);
                        $('[name="person_id"]').val(resp.id);
                        $('#details').show();
                        end_load();
                    } else if(resp.status == 2){
                        alert_toast("Unknown tracking id.", 'danger');
                        end_load();
                    }
                }
            }
        });
    }
</script>