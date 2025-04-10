<?php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
include 'db_connection.php';

// Check connection to the database
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


  $role=isset($_SESSION['role']) ? $_SESSION['role']:'';
  //set the appropriate url based on the user role
  if ($role ==='Student') {
    $backurl = 'studentprofile.php';
  }elseif ($role ==='Administrator'){
    $backurl='adminprofile.php';
  }elseif ($role==='Alumni') {
    $backurl='studentprofile.php';
  }

  

// Assume the logged in user's id is stored in the session variable 'user_id'
$userId = $_SESSION['user_id'];

// Query to count unread messages for the logged-in user
$sql = "SELECT COUNT(*) AS unread FROM mail WHERE to_user = '$userId' AND status = 0";
$result = $conn->query($sql);
$count = 0;
if ($result && $row = $result->fetch_assoc()) {
  $count = $row['unread'];
}
// $conn->close();
?>



<!-- Navbar Header -->
<nav
            class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom"
          >
            <div class="container-fluid">
              <nav
                class="navbar navbar-header-left navbar-expand-lg navbar-form nav-search p-0 d-none d-lg-flex"
              >
                <!-- <div class="input-group">
                  <div class="input-group-prepend">
                    <button type="submit" class="btn btn-search pe-1">
                      <i class="fa fa-search search-icon"></i>
                    </button>
                  </div>
                  <input
                    type="text"
                    placeholder="Search ..."
                    class="form-control"
                  />
                </div> -->
              </nav>
            
              <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
             
                <li class="nav-item topbar-icon dropdown hidden-caret">
                  <a
                    class="nav-link dropdown-toggle"
                    href="inbox.php"
                    
                    role="button"
                  >
                    <i class="fa fa-envelope"></i>
                    <span class="notification"><?php echo $count; ?></span>
                  </a>
                 
                </li>
                
              

                <li class="nav-item topbar-user dropdown hidden-caret">
                  <a
                    class="dropdown-toggle profile-pic"
                    data-bs-toggle="dropdown"
                    href="#"
                    aria-expanded="false"
                  >
                    <div class="avatar-sm">
                      <img
                        src="assets/img/profile-img.jpg"
                        alt="..."
                        class="avatar-img rounded-circle"
                      />
                    </div>
                    <span class="profile-username">
                      <span class="op-7">Welcome,</span>
                      <span class="fw-bold"> <?php echo htmlspecialchars($student_name); ?></span>
                    </span>
                  </a>
                  <ul class="dropdown-menu dropdown-user animated fadeIn">
                    <div class="dropdown-user-scroll scrollbar-outer">
                      <li>
                        <div class="user-box">
                          <div class="avatar-lg" style="width: 70px;">
                            <img
                              src="assets/img/profile-img.jpg"
                              alt="image profile"
                              class="avatar-img rounded"
                            />
                          </div>
                          <div class="u-text">
                            <h4> <?php echo htmlspecialchars($student_name); ?> </h4>
                            <!-- <p class="text-muted">hello@example.com</p> -->
                            
                        </div>
                      </li>
                      <li>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#" onclick="window.location.href='<?php echo $backurl; ?>'">My Profile</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="create_message.php">Create Mail</a>
                        <a class="dropdown-item" href="inbox.php">Inbox</a>
                        <a class="dropdown-item" href="sent_message.php">Sent</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="logout.php">Logout</a>
                      </li>
                    </div>
                  </ul>
                </li>
              </ul>
            </div>
          </nav>