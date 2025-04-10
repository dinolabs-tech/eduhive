<style>
  .logo {
    margin: auto;
    font-size: 20px;
    background: white;
    padding: 7px 11px;
    border-radius: 50%;
    color: #000000b3;
  }

  /* Style for the toggle button */
  #nav-toggle-button {
    border: none;
    background: transparent;
    padding: 0.5rem;
    margin-right: 0.5rem;
  }

  /* Custom styles for search input */
  #find {
    background-color: #495057;
    color: #fff;
    border: none;
    border-radius: 50px;
    padding: 0.5rem 1rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: box-shadow 0.3s ease;
  }

  #find:focus {
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
  }

  #find::placeholder {
    color: #ced4da;
  }

  /* Style for the search button */
  #manage-search .btn-link {
    color: #fff;
    text-decoration: none;
    font-size: 1.2rem;
  }
</style>

<nav class="navbar navbar-dark fixed-top bg-dark" style="padding:0;min-height: 3.5rem">
  <div class="container-fluid mt-2 mb-2 d-flex align-items-center">
    <!-- Toggle button on the left -->
    <button id="nav-toggle-button" class="btn btn-light me-2" type="button">
      <span class="navbar-toggler-icon"></span>
    </button>
    <!-- Wrapper div to center the search form -->
    <div class="flex-grow-1 d-flex justify-content-center">
      <!-- Search form with 80% width -->
      <form id="manage-search" style="width: 80%;" class="position-relative">
        <input type="text" placeholder="Search here" id="find" class="form-control pe-5" 
          value="<?php echo isset($_GET['keyword']) ? $_GET['keyword'] : '' ?>">
        <button type="submit" class="btn btn-link position-absolute end-0 top-50 translate-middle-y">
          <i class="bi bi-search"></i>
        </button>
      </form>
    </div>
  </div>
</nav>

<script>
  $('#find').keypress(function(e) {
    if (e.which == 13) {
      $('#manage-search').submit();
    }
  });

  $('#manage-search').submit(function(e) {
    e.preventDefault();
    location.href = "index.php?page=search&keyword=" + $('#find').val();
  });

  // Toggle the sidebar with fade effect
  $('#nav-toggle-button').click(function() {
    $('#navbar-container').fadeToggle();
  });
</script>