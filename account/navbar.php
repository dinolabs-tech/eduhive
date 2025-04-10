
  <style>
    .collapse a {
      text-indent: 10px;
    }
    nav#sidebar {
      background: white;
    }
  

		@media (max-width: 768px) {
  	#sidebar {
    display: none; /* Hide sidebar by default on mobile */
    position: absolute;
    width: 250px;
    height: 100%;
    background: white;
    box-shadow: 2px 0px 5px rgba(0, 0, 0, 0.2);
    z-index: 1000;
  }
}

@media (max-width: 768px) {
  body,
  main#view-panel,
  .container {
    width: 100%;
    margin: 0;
    padding: 0;
		padding-top: 20px;
  }
}


  </style>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var sidebar = document.getElementById('sidebar');
    var toggleSidebarBtn = document.getElementById('toggleSidebar');

    if (toggleSidebarBtn) {
      toggleSidebarBtn.addEventListener('click', function () {
        if (sidebar.style.display === 'block') {
          sidebar.style.display = 'none';
        } else {
          sidebar.style.display = 'block';
        }
      });
    }
  });
</script>
</head>
<body>


  <nav id="sidebar" class="mx-lt-5 bg-dark">
	
    <div class="sidebar-list">
		
      <a href="..\dashboard.php" class="nav-item nav-dashboard">
        <span class="icon-field"><i class="fa fa-tachometer-alt"></i></span> Dashboard
      </a>

      <a href="index.php?page=home" class="nav-item nav-home">
        <span class="icon-field"><i class="fa fa-home"></i></span> Home
      </a>

      <a href="index.php?page=students" class="nav-item nav-students">
        <span class="icon-field"><i class="fa fa-receipt"></i></span> Register Students
      </a>

      <!-- Collapsible Menu for Payments -->
      <a href="#paymentsSubMenu" class="nav-item nav-payments nav_collapse" data-toggle="collapse">
        <span class="icon-field"><i class="fa fa-receipt"></i></span> Payments
      </a>
      
      <div id="paymentsSubMenu" class="collapse">
        <a href="index.php?page=payments" class="nav-item nav-payments">Payments</a>
        <a href="index.php?page=payments_report" class="nav-item nav-payments_report">Payments Report</a>
      </div>

      <a href="index.php?page=courses" class="nav-item nav-courses">
        <span class="icon-field"><i class="fa fa-scroll"></i></span> School Fees
      </a>
      <a href="index.php?page=fees" class="nav-item nav-fees">
        <span class="icon-field"><i class="fa fa-money-check"></i></span> Student Fees
      </a>

      <div class="mx-2 text-dark font-weight-bold">Systems</div>
      <?php if($_SESSION['role'] == 1): ?>
      <a href="index.php?page=users" class="nav-item nav-users">
        <span class="icon-field"><i class="fa fa-users"></i></span> Users
      </a>
      <a href="index.php?page=site_settings" class="nav-item nav-site_settings">
        <span class="icon-field"><i class="fa fa-cogs"></i></span> System Settings
      </a>
      <?php endif; ?>
    </div>
  </nav>


  <script>
    $(document).ready(function() {
      // Toggle collapse on click
      $('.nav_collapse').click(function(e) {
        e.preventDefault();
        $($(this).attr('href')).collapse('toggle');
      });
      
      // Add active class to the current page
      $('.nav-<?php echo isset($_GET['page']) ? $_GET['page'] : '' ?>').addClass('active');
    });
  </script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var sidebar = document.getElementById('sidebar');
    var toggleSidebarBtn = document.getElementById('toggleSidebar');

    if (toggleSidebarBtn) {
      toggleSidebarBtn.addEventListener('click', function () {
        if (sidebar.style.display === 'block') {
          sidebar.style.display = 'none';
        } else {
          sidebar.style.display = 'block';
        }
      });
    }
  });
</script>
