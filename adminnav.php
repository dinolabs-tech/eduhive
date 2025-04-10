 <!-- Sidebar -->
 <div class="sidebar" data-background-color="dark">
        <div class="sidebar-logo">
       <?php include('logo_header.php');?>
        </div>
        <div class="sidebar-wrapper scrollbar scrollbar-inner">
          <div class="sidebar-content">
            <ul class="nav nav-secondary">
              <li class="nav-item">
                <a href="dashboard.php">
                  <i class="fas fa-home"></i>
                  <p>Dashboard</p>                  
                </a>
              </li>
             
             

              <?php if ($_SESSION['role'] == 'Administrator' || $_SESSION['role'] == 'Admission' || $_SESSION['role'] == 'Superuser') { ?>

                <li class="nav-section">
                <span class="sidebar-mini-icon">
                  <i class="fa fa-ellipsis-h"></i>
                </span>
                <h4 class="text-section">Admission</h4>
              </li>
               
                <li class="nav-item">
                <a data-bs-toggle="collapse" href="#students">
                  <i class="fas fa-user-graduate"></i>
                  <p>Students</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse" id="students">
                  <ul class="nav nav-collapse">
                    <li>
                      <a href="registerstudents.php">
                        <span class="sub-item">Enroll</span>
                      </a>
                    </li>
                    <li>
                      <a href="modifystudents.php">
                        <span class="sub-item">Modify</span>
                      </a>
                    </li>
                    <li>
                      <a href="viewstudents.php">
                        <span class="sub-item">view</span>
                      </a>
                    </li>
                  </ul>
                </div>
              </li>

                <!-- End Icons Nav -->
              <?php } ?>

           

              <?php if ($_SESSION['role'] == 'Administrator' || $_SESSION['role'] == 'Teacher' || $_SESSION['role'] == 'Superuser') { ?>
              
                <li class="nav-section">
                <span class="sidebar-mini-icon">
                  <i class="fa fa-ellipsis-h"></i>
                </span>
                <h4 class="text-section">Teacher</h4>
              </li>
              
              <li class="nav-item">
              <a data-bs-toggle="collapse" href="#result">
                <i class="fas fa-chart-bar"></i>
                <p>Results</p>
                <span class="caret"></span>
              </a>
              <div class="collapse" id="result">
                <ul class="nav nav-collapse">
                  <li>
                    <a href="uploadresults.php">
                      <span class="sub-item">Upload</span>
                    </a>
                  </li>
                  <li>
                    <a href="modifyresult.php">
                      <span class="sub-item">Modify</span>
                    </a>
                  </li>
                  <li>
                    <a href="classteachercomment.php">
                      <span class="sub-item">Class Teacher's Comments</span>
                    </a>
                  </li>
                  <li>
                    <a href="principalcomment.php">
                      <span class="sub-item">Principal's Comments</span>
                    </a>
                  </li>
                  
                  <li>
                    <a href="individualresult.php">
                      <span class="sub-item">View Student's result</span>
                    </a>
                  </li>
                  <li>
                    <a href="viewuploadedresult.php">
                      <span class="sub-item">View Uploaded Results</span>
                    </a>
                  </li>
                  <li>
                    <a href="mastersheet.php">
                      <span class="sub-item">Download Mastersheet</span>
                    </a>
                  </li>
                  
                </ul>
              </div>
            </li>

            <!-- E-LEARNING RESOURCES====================== -->
            <li class="nav-item">
                    <a data-bs-toggle="collapse" href="#resources">
                      <i class="fas fa-globe"></i>
                      <p>E-Learning Resources</p>
                      <span class="caret"></span>
                    </a>
                    <div class="collapse" id="resources">
                        <ul class="nav nav-collapse">
                        <li>
                            <a data-bs-toggle="collapse" href="#subnav1">
                              <span class="sub-item">Assignments</span>
                              <span class="caret"></span>
                            </a>
                            <div class="collapse" id="subnav1">
                              <ul class="nav nav-collapse subnav">
                                <li>
                                  <a href="uploadassignments.php">
                                    <span class="sub-item">Upload</span>
                                  </a>
                                </li>
                                <li>
                                  <a href="viewuploadassignments.php">
                                    <span class="sub-item">View</span>
                                  </a>
                                </li>
                              </ul>
                            </div>
                          </li>

                          <li>
                            <a data-bs-toggle="collapse" href="#subnav3">
                              <span class="sub-item">Notes</span>
                              <span class="caret"></span>
                            </a>
                            <div class="collapse" id="subnav3">
                              <ul class="nav nav-collapse subnav">
                                <li>
                                  <a href="uploadnotes.php">
                                    <span class="sub-item">Upload</span>
                                  </a>
                                </li>
                                <li>
                                  <a href="viewuploadnotes.php">
                                    <span class="sub-item">View</span>
                                  </a>
                                </li>
                              </ul>
                            </div>
                          </li>
                          <li>
                            <a data-bs-toggle="collapse" href="#subnav2">
                              <span class="sub-item">Curriculum</span>
                              <span class="caret"></span>
                            </a>
                            <div class="collapse" id="subnav2">
                              <ul class="nav nav-collapse subnav">
                                <li>
                                  <a href="uploadcurriculum.php">
                                    <span class="sub-item">Upload</span>
                                  </a>
                                </li>
                                <li>
                                  <a href="viewuploadcurriculum.php">
                                    <span class="sub-item">View</span>
                                  </a>
                                </li>
                              </ul>
                            </div>
                        </li>
                        </ul>
                </div>
              </li>
                    <!-- End Icons Nav -->

              <!-- CBT RESOURCES====================== -->
              <li class="nav-item">
              <a data-bs-toggle="collapse" href="#cbt">
                <i class="fas fa-laptop"></i>
                <p>CBT</p>
                <span class="caret"></span>
              </a>
              <div class="collapse" id="cbt">
                <ul class="nav nav-collapse">
                  <li>
                    <a href="addquestion.php">
                      <span class="sub-item">Add Questions</span>
                    </a>
                  </li>
                  <li>
                    <a href="questionadd.php">
                      <span class="sub-item">Upload Questions</span>
                    </a>
                  </li>
                  <li>
                    <a href="adquest.php">
                      <span class="sub-item">Modify Questions</span>
                    </a>
                  </li>
                  <li>
                    <a href="checkcbt.php">
                      <span class="sub-item">Check Results</span>
                    </a>
                  </li>
                  
                  <li>
                    <a href="settime.php">
                      <span class="sub-item">Set Exam Time/Date</span>
                    </a>
                  </li>
                 
                  
                </ul>
              </div>
            </li>

              <?php } ?>


              <?php if ($_SESSION['role'] == 'Administrator' || $_SESSION['role'] == 'Tuckshop' || $_SESSION['role'] == 'Superuser') { ?>

                
              <li class="nav-section">
                <span class="sidebar-mini-icon">
                  <i class="fa fa-ellipsis-h"></i>
                </span>
                <h4 class="text-section">TuckShop</h4>
              </li>

                <li class="nav-item">
                <a data-bs-toggle="collapse" href="#tuck">
                  <i class="fas fa-store"></i>
                  <p>Tuck Shop</p>
                  <span class="caret"></span>
                </a>
                <div class="collapse" id="tuck">
                  <ul class="nav nav-collapse">
                    <li>
                      <a href="regtuck.php">
                        <span class="sub-item">Register</span>
                      </a>
                    </li>
                    <li>
                      <a href="sellingpoint.php">
                        <span class="sub-item">POS</span>
                      </a>
                    </li>
                    <li>
                      <a href="inventory.php">
                        <span class="sub-item">Inventory</span>
                      </a>
                    </li>
                    <li>
                      <a href="supplier.php">
                        <span class="sub-item">Suppliers</span>
                      </a>
                    </li>
                  
                    <li>
                      <a href="tuckdashboard.php">
                        <span class="sub-item">Dashboard</span>
                      </a>
                    </li>
                    <li>
                      <a href="transactions.php">
                        <span class="sub-item">Transactions</span>
                      </a>
                    </li>
                  
                  </ul>
                </div>
              </li>
              <?php } ?>

              

              <?php if ($_SESSION['role'] == 'Administrator' || $_SESSION['role'] == 'Bursary' || $_SESSION['role'] == 'Superuser') { ?>
               
                <li class="nav-section">
                <span class="sidebar-mini-icon">
                  <i class="fa fa-ellipsis-h"></i>
                </span>
                <h4 class="text-section">Bursary</h4>
              </li>

               <li class="nav-item">
               <a data-bs-toggle="collapse" href="#bursary">
                 <i class="fas fa-hand-holding-usd"></i>
                 <p>Bursary</p>
                 <span class="caret"></span>
               </a>
               <div class="collapse" id="bursary">
                 <ul class="nav nav-collapse">
                   <li>
                     <a href="./account">
                       <span class="sub-item">Account Management</span>
                     </a>
                   </li>
                   <li>
                     <a href="approvepayments.php">
                       <span class="sub-item">Approve Payments</span>
                     </a>
                   </li>
                 </ul>
               </div>
             </li>

               <!-- End Icons Nav -->
             <?php } ?>

            

             <?php if ($_SESSION['role'] == 'Administrator' || $_SESSION['role'] == 'Superuser') { ?>

              <li class="nav-section">
                <span class="sidebar-mini-icon">
                  <i class="fa fa-ellipsis-h"></i>
                </span>
                <h4 class="text-section">Administrator</h4>
              </li>

                <li class="nav-item">
                  <a href="timetable.php">
                    <i class="fas fa-th-list"></i>
                    <p>Class Schedule</p>
                  </a>
                </li>

                <li class="nav-item">
                  <a href="calendar.php">
                    <i class="fas fa-calendar-alt"></i>
                    <p>Calendar</p>
                  </a>
                </li>

                <li class="nav-item">
                  <a href="./forum">
                    <i class="fas fa-comment-dots"></i>
                    <p>Discussion Forum</p>
                  </a>
                </li>

                <li class="nav-item">
                  <a href="subjects.php">
                    <i class="fas fa-book-open"></i>
                    <p>Subjects</p>
                  </a>
                </li>

                <li class="nav-item">
                  <a href="admin.php">
                    <i class="fas fa-cog"></i>
                    <p>Settings</p>
                  </a>
                </li>

                <li class="nav-item">
                  <a href="usercontrol.php">
                    <i class="fas fa-user-cog"></i>
                    <p>User Control</p>
                  </a>
                </li>
              <?php } ?>
            
            </ul>
          </div>
        </div>
      </div>
      <!-- End Sidebar -->