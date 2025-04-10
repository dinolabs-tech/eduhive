<?php
  $role=isset($_SESSION['role']) ? $_SESSION['role']:'';
  //set the appropriate url based on the user role
  if ($role ==='Student') {
    $backurl = '../students.php';
  }elseif ($role ==='Administrator'){
    $backurl='../dashboard.php';
  }elseif ($role ==='Superuser'){
    $backurl='../dashboard.php';
  }elseif ($role==='Alumni') {
    $backurl='../alumni.php';
  }

?>
<style>

  /* Optional: Override active list-group-item colors */
  .list-group-item.active {
    background-color: #0d6efd; /* Bootstrap primary color */
    border-color: #0d6efd;
  }
</style>

<nav id="sidebar" class="bg-dark">
  <div class="list-group list-group-flush">
  <a href="#" onclick="window.location.href='<?php echo $backurl; ?>'" class="list-group-item list-group-item-action bg-dark text-light nav-home">
      <i class="fa fa-home me-2"></i> Dashboard
    </a>
    <a href="index.php?page=home" class="list-group-item list-group-item-action bg-dark text-light nav-home">
      <i class="fa fa-home me-2"></i> Home
    </a>
    <a href="index.php?page=categories" class="list-group-item list-group-item-action bg-dark text-light nav-categories">
      <i class="fa fa-tags me-2"></i> Categories
    </a>
    <a href="index.php?page=topics" class="list-group-item list-group-item-action bg-dark text-light nav-topics">
      <i class="fa fa-comment me-2"></i> Discussion
    </a>
  </div>
</nav>

<script>
  // If you have collapsible items in the future, this script enables the collapse functionality.
  $('.nav_collapse').click(function(){
    console.log($(this).attr('href'));
    $($(this).attr('href')).collapse();
  });
  
  // Add active class to the current page's nav item
  $('.nav-<?php echo isset($_GET['page']) ? $_GET['page'] : '' ?>').addClass('active');
</script>
