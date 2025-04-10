<?php include('components/students_logic.php');?>

<!DOCTYPE html>
<html lang="en">
<?php include('head.php');?>
  <body>
    <div class="wrapper">
      <!-- Sidebar -->
      <?php 
      
      $role=isset($_SESSION['role']) ? $_SESSION['role']:'';
      //set the appropriate url based on the user role
      if ($role ==='Student') {
        include('studentnav.php'); 
      }elseif ($role ==='Administrator'){
        include('adminnav.php'); 
      }elseif ($role==='Alumni') {
        include('alumninav.php'); 
      }

      ?>
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
                <h3 class="fw-bold mb-3">Dashboard</h3>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="students.php">Home</a></li>
                  <li class="breadcrumb-item active">Dashboard</li>
              </ol>
              </div>
           
            </div>

             <!-- PERSONAL AI ============================ -->
             <div class="row">
             
             <div class="col-md-12">
               <div class="card card-primary card-round">
                 <div class="card-header">
                   <div class="card-head-row">
                     <div class="card-title">Personal AI</div>
                   </div>
                 </div>
                 <div class="card-body pb-0">
                   <div class="mb-4 mt-2">
                 
                   <p id="message" data-message="<?php echo htmlspecialchars($message); ?>"></p>

                   </div>
                   
                 </div>
               </div>
             
             </div>
           </div>
            
        
      
            <div class="row">
              <div class="col-md-12">
                <div class="card card-round">
                  <div class="card-header">
                    <div class="card-head-row card-tools-still-right">
                      <h4 class="card-title">Academic Calendar</h4>
                    </div>
                    <p class="card-category">
                    <div class="calendar-container">
                      <div class="header1">
                          <button id="prev-month" class="btn btn-warning"><span class="btn-label">
                          <i class="fa fa-fast-backward"></i> </button>
                          <h2 id="month-year"></h2>
                          <button id="next-month" class="btn btn-success"><span class="btn-label">
                          <i class="fa fa-fast-forward"></i> </button>
                      </div>
                      <div class="calendar calendar-body"></div>
                    </div>

                      <div id="event-modal">
                          <h2 id="event-title"></h2>
                          <p id="event-description"></p>
                          <button id="close-modal">Close</button>
                      </div>
                    </p>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-6">
                        <div class="table-responsive table-hover table-sales">
                                            <script>
                            const calendarBody = document.querySelector('.calendar-body');
                            const monthYear = document.getElementById('month-year');
                            const prevMonthBtn = document.getElementById('prev-month');
                            const nextMonthBtn = document.getElementById('next-month');
                            const modal = document.getElementById('event-modal');
                            const modalTitle = document.getElementById('event-title');
                            const modalDescription = document.getElementById('event-description');
                            const closeModal = document.getElementById('close-modal');

                            let currentDate = new Date();

                            // Fetch events from PHP (embedded in the page as JSON)
                            const events = <?php echo json_encode($events); ?>;

                            function renderCalendar(date) {
                                const year = date.getFullYear();
                                const month = date.getMonth();
                                const firstDay = new Date(year, month, 1).getDay();
                                const lastDate = new Date(year, month + 1, 0).getDate();

                                // Set header
                                const monthNames = [
                                    'January', 'February', 'March', 'April', 'May', 'June',
                                    'July', 'August', 'September', 'October', 'November', 'December'
                                ];
                                monthYear.textContent = `${monthNames[month]} ${year}`;

                                // Clear previous calendar days
                                const days = calendarBody.querySelectorAll('.day');
                                days.forEach(day => calendarBody.removeChild(day));

                                // Fill blank days before the first day of the month
                                for (let i = 0; i < firstDay; i++) {
                                    const blankDay = document.createElement('div');
                                    blankDay.classList.add('day', 'inactive');
                                    calendarBody.appendChild(blankDay);
                                }

                                // Fill days of the month
                                for (let i = 1; i <= lastDate; i++) {
                                    const day = document.createElement('div');
                                    day.classList.add('day');
                                    day.textContent = i;

                                    // Format the date as MM/dd/yyyy
                                    const eventKey = `${String(month + 1).padStart(2, '0')}/${String(i).padStart(2, '0')}/${year}`;

                                    // Check if there's an event on this day
                                    if (events[eventKey]) {
                                        day.classList.add('event');
                                        day.addEventListener('click', () => showEvent(eventKey));
                                    }

                                    calendarBody.appendChild(day);
                                }
                            }

                            function showEvent(date) {
                                const event = events[date];
                                modalTitle.textContent = event.title;
                                modalDescription.textContent = event.description;
                                modal.style.display = 'flex';
                            }

                            closeModal.addEventListener('click', () => {
                                modal.style.display = 'none';
                            });

                            prevMonthBtn.addEventListener('click', () => {
                                currentDate.setMonth(currentDate.getMonth() - 1);
                                renderCalendar(currentDate);
                            });

                            nextMonthBtn.addEventListener('click', () => {
                                currentDate.setMonth(currentDate.getMonth() + 1);
                                renderCalendar(currentDate);
                            });

                            // Initial render
                            renderCalendar(currentDate);
                        </script>
                        </div>
                      </div>
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
                      <div class="card-title">Academic Performance</div>
                      <div class="card-tools">
                      
                      </div>
                    </div>
                  </div>
                  <div class="card-body">
                    <div class="chart-container" style="min-height: 375px">
                      <canvas id="adminChart"></canvas>
                    </div>
                    <div id="myChartLegend"></div>
                  </div>
                </div>
              </div>
             
            </div>


            <div class="row">
              <div class="col-md-12">
                <div class="card card-round">
                  <div class="card-body">
                    <div class="card-head-row card-tools-still-right">
                      <div class="card-title">Class Peers</div>
                      <div class="card-tools">
                        <div class="dropdown">
                          <button
                            class="btn btn-icon btn-clean me-0"
                            type="button"
                            id="dropdownMenuButton"
                            data-bs-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false"
                          > <strong><?php echo htmlspecialchars($student_details['count(id)']); ?></strong>
                            
                          </button>
                          
                        </div>
                      </div>


                    </div>
                    <div class="card-list py-4">
                    <div class="card-body p-0">

                    <div class="table-responsive">
                      <!-- Projects table -->
                      <table
                        id="basic-datatables"
                        class="display table table-striped table-hover">
                        <thead>
                            <tr>

                                <th>Name</th>
                                <th>Gender</th>
                                <th>Mobile</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($classpeer)): ?>
                                <?php foreach ($classpeer as $peer): ?>
                                    <tr>
                                        
                                        <td><?php echo htmlspecialchars($peer['name']); ?></td>
                                        <td><?php echo htmlspecialchars($peer['gender']); ?></td>
                                        <td><?php echo htmlspecialchars($peer['studentmobile']); ?></td>
                                        <td><?php echo htmlspecialchars($peer['email']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2">No records found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    </div>
                  </div>
                    </div>
                  </div>
                </div>
              </div>
             
            </div>
          </div>
        </div>
     
        <?php include('footer.php');?>
      </div>

      <!-- Custom template | don't include it in your project! -->
      <?php include('cust-color.php');?>
      <!-- End Custom template -->
    </div>
   <?php include('scripts.php');?>
  </body>
</html>
