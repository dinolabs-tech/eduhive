<style>
	.logo {
    margin: auto;
    font-size: 20px;
    background: white;
    padding: 7px 11px;
    border-radius: 50% 50%;
    color: #000000b3;
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
    padding-top: 50px;
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


<nav class="navbar navbar-light fixed-top bg-primary" style="padding:0">
  <div class="container-fluid mt-2 mb-2">
  	<div class="col-lg-12">
  		<div class="col-md-1 float-left" style="display: flex;">
  		
  		</div>
      <div class="col-md-4 float-left text-white">
<!-- this is the container for the top left area-->
<button id="toggleSidebar" class="btn btn-primary d-md-none">☰ Menu</button>
 
      </div>
	  	<div class="float-right">
            <a href="" class="text-white "  id="account_settings"> &nbsp;</a>
        </div>
      </div>
  </div>
  
</nav>

<script>
  $('#manage_my_account').click(function(){
    uni_modal("Manage Account","manage_user.php?id=<?php echo $_SESSION['login_id'] ?>&mtype=own")
  })
</script>