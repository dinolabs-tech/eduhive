 <!-- Sidebar -->
 <div class="sidebar" data-background-color="dark">
        <div class="sidebar-logo">
       <?php include('logo_header.php');?>
        </div>
        <div class="sidebar-wrapper scrollbar scrollbar-inner">
          <div class="sidebar-content">
            <ul class="nav nav-secondary">
              <li class="nav-item">
                <a href="alumni.php">
                  <i class="fas fa-home"></i>
                  <p>Dashboard</p>                  
                </a>
              </li>
             
              <li class="nav-item">
                <a data-bs-toggle="collapse" href="#" onclick="showPopup()">
                  <i class="fas fa-chart-bar"></i>
                  <p>Result</p>
                </a>
              </li>


            <!-- Overlay -->
            <div id="overlay" class="overlay" onclick="closePopup()"></div>

            <!-- Popup Form -->
            <div id="popup" class="popup">
                <span class="close" onclick="closePopup()">&times;</span>
                <form id="filterForm">
                    <p></p>
                    <p></p>
                    <select id="session" name="session">
                        <option value="">Select Session</option>
                        <!-- Options will be added dynamically -->
                    </select>

                    <select id="term" name="term">
                        <option value="">Select Term</option>
                        <!-- Options will be added dynamically -->
                    </select>

                    <button type="button" onclick="checkResult()">Check Result</button>
                </form>
            </div>

                  <script>
              // JavaScript to handle the popup
              function showPopup() {
                  document.getElementById('popup').style.display = 'block';
                  document.getElementById('overlay').style.display = 'block';
                  loadOptions(); // Load options for session and term
              }

              function closePopup() {
                  document.getElementById('popup').style.display = 'none';
                  document.getElementById('overlay').style.display = 'none';
              }

                  function loadOptions() {
              const sessionSelect = document.getElementById('session');
              const termSelect = document.getElementById('term');

              // Clear existing options
              sessionSelect.innerHTML = '<option value="">Select Session</option>';
              termSelect.innerHTML = '<option value="">Select Term</option>';

              // Add static term options
              const terms = ['1st Term', '2nd Term', '3rd Term'];
              terms.forEach(term => {
                  const option = document.createElement('option');
                  option.value = term;
                  option.textContent = term;
                  termSelect.appendChild(option);
              });

              // Fetch session values from the server using AJAX
              fetch('get_sessions.php')
                  .then(response => response.json())
                  .then(data => {
                      data.sessions.forEach(session => {
                          const option = document.createElement('option');
                          option.value = session;
                          option.textContent = session;
                          sessionSelect.appendChild(option);
                      });
                  })
                  .catch(error => {
                      console.error('Error fetching sessions:', error);
                  });
              }


                  function checkResult() {
                      const session = document.getElementById('session').value;
                      const term = document.getElementById('term').value;

                      if (session && term) {
                          window.location.href = `checkresult.php?session=${encodeURIComponent(session)}&term=${encodeURIComponent(term)}`;
                      } else {
                          alert('Please select both session and term.');
                      }
                  }
              </script>


              <li class="nav-item">
                <a href="transcript.php">
                  <i class="fas fa-desktop"></i>
                  <p>Transcript</p>
                </a>
              </li>


              <li class="nav-item">
                <a href="./forum">
                  <i class="fas fa-comment-dots"></i>
                  <p>Discussion Forum</p>
                </a>
              </li>

            
            </ul>
          </div>
        </div>
      </div>
      <!-- End Sidebar -->