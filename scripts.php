
  <script>
    function fetchStudentDetails(studentId) {
      if (studentId.length > 0) {
        $.ajax({
          type: 'POST',
          url: window.location.href,  // or explicitly specify your file, e.g., 'bursary.php'
          data: { id: studentId, action: 'fetch' },
          success: function(response) {
            try {
              var student = JSON.parse(response);
              if (student) {
                $('#name').val(student.name);
                $('#class').val(student["class"]); // use bracket notation here
                $('#arm').val(student.arm);
                $('#term').val(student.term);
                $('#gender').val(student.gender);
                $('#session').val(student.session);
              } else {
                $('#name, #class, #arm, #term, #gender, #session').val('');
              }
            } catch (e) {
              console.error("Error parsing JSON response", e);
            }
          }
        });
      } else {
        $('#name, #class, #arm, #term, #gender, #session').val('');
      }
    }
  </script>


 <!--   Core JS Files   -->
  <script src="../assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/core/popper.min.js"></script>
    <script src="../assets/js/core/bootstrap.min.js"></script>

    <!-- jQuery Scrollbar -->
    <script src="../assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <!-- Datatables -->
    <script src="../assets/js/plugin/datatables/datatables.min.js"></script>
    <!-- Kaiadmin JS -->
    <script src="../assets/js/kaiadmin.min.js"></script>
    <!-- Kaiadmin DEMO methods, don't include it in your project! -->
    <script src="../assets/js/setting-demo2.js"></script>
<!--   Core JS Files   -->
    <script src="assets/js/core/jquery-3.7.1.min.js"></script>

    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>

    <!-- jQuery Scrollbar -->
    <script src="assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>

    <!-- Chart JS -->
    <script src="assets/js/plugin/chart.js/chart.min.js"></script>

    <!-- jQuery Sparkline -->
    <script src="assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js"></script>

    <!-- Chart Circle -->
    <script src="assets/js/plugin/chart-circle/circles.min.js"></script>

    <!-- Datatables -->
    <script src="assets/js/plugin/datatables/datatables.min.js"></script>

    <!-- Bootstrap Notify -->
    <script src="assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>

    <!-- jQuery Vector Maps -->
    <script src="assets/js/plugin/jsvectormap/jsvectormap.min.js"></script>
    <script src="assets/js/plugin/jsvectormap/world.js"></script>

    <!-- Sweet Alert -->
    <script src="assets/js/plugin/sweetalert/sweetalert.min.js"></script>

    <!-- Kaiadmin JS -->
    <script src="assets/js/kaiadmin.min.js"></script>

    <!-- Kaiadmin DEMO methods, don't include it in your project! -->
    <script src="assets/js/setting-demo.js"></script>
    <script src="assets/js/demo.js"></script>

    <!-- LINE CHART -============================ -->
    <script>
      $("#lineChart").sparkline([102, 109, 120, 99, 110, 105, 115], {
        type: "line",
        height: "70",
        width: "100%",
        lineWidth: "2",
        lineColor: "#177dff",
        fillColor: "rgba(23, 125, 255, 0.14)",
      });

      $("#lineChart2").sparkline([99, 125, 122, 105, 110, 124, 115], {
        type: "line",
        height: "70",
        width: "100%",
        lineWidth: "2",
        lineColor: "#f3545d",
        fillColor: "rgba(243, 84, 93, .14)",
      });

      $("#lineChart3").sparkline([105, 103, 123, 100, 95, 105, 115], {
        type: "line",
        height: "70",
        width: "100%",
        lineWidth: "2",
        lineColor: "#ffa534",
        fillColor: "rgba(255, 165, 52, .14)",
      });
    </script>

    <!-- BASIC DATATABLES ===================== -->
    <script>
      $(document).ready(function () {
        $("#basic-datatables").DataTable({});

        $("#multi-filter-select").DataTable({
          pageLength: 5,
          initComplete: function () {
            this.api()
              .columns()
              .every(function () {
                var column = this;
                var select = $(
                  '<select class="form-select"><option value=""></option></select>'
                )
                  .appendTo($(column.footer()).empty())
                  .on("change", function () {
                    var val = $.fn.dataTable.util.escapeRegex($(this).val());

                    column
                      .search(val ? "^" + val + "$" : "", true, false)
                      .draw();
                  });

                column
                  .data()
                  .unique()
                  .sort()
                  .each(function (d, j) {
                    select.append(
                      '<option value="' + d + '">' + d + "</option>"
                    );
                  });
              });
          },
        });

        // Add Row
        $("#add-row").DataTable({
          pageLength: 5,
        });

        var action =
          '<td> <div class="form-button-action"> <button type="button" data-bs-toggle="tooltip" title="" class="btn btn-link btn-primary btn-lg" data-original-title="Edit Task"> <i class="fa fa-edit"></i> </button> <button type="button" data-bs-toggle="tooltip" title="" class="btn btn-link btn-danger" data-original-title="Remove"> <i class="fa fa-times"></i> </button> </div> </td>';

        $("#addRowButton").click(function () {
          $("#add-row")
            .dataTable()
            .fnAddData([
              $("#addName").val(),
              $("#addPosition").val(),
              $("#addOffice").val(),
              action,
            ]);
          $("#addRowModal").modal("hide");
        });
      });

    </script>

    <!-- ADMIN CHART============================= -->
    <script>
    // Retrieve the PHP-generated data.
    var classes = <?php echo $classesJson; ?>;
    var datasetsData = <?php echo $datasetsJson; ?>;

    // Get the canvas context.
    var ctx = document.getElementById("adminChart").getContext("2d");

    // Pre-defined color gradients for the chart (will be cycled if more arms exist).
    var colorOptions = [
      {
        stroke: ['#177dff', '#80b6f4'],
        fill:   ['rgba(23, 125, 255, 0.7)', 'rgba(128, 182, 244, 0.3)']
      },
      {
        stroke: ['#f3545d', '#ff8990'],
        fill:   ['rgba(243, 84, 93, 0.7)', 'rgba(255, 137, 144, 0.3)']
      },
      {
        stroke: ['#fdaf4b', '#ffc478'],
        fill:   ['rgba(253, 175, 75, 0.7)', 'rgba(255, 196, 120, 0.3)']
      }
    ];

    // Loop through each dataset to assign gradient colors.
    for (var i = 0; i < datasetsData.length; i++) {
      var gradientStroke = ctx.createLinearGradient(500, 0, 100, 0);
      var gradientFill = ctx.createLinearGradient(500, 0, 100, 0);
      var colors = colorOptions[i % colorOptions.length];

      gradientStroke.addColorStop(0, colors.stroke[0]);
      gradientStroke.addColorStop(1, colors.stroke[1]);

      gradientFill.addColorStop(0, colors.fill[0]);
      gradientFill.addColorStop(1, colors.fill[1]);

      // Assign gradient properties and other dataset settings.
      datasetsData[i].borderColor = gradientStroke;
      datasetsData[i].pointBackgroundColor = gradientStroke;
      datasetsData[i].backgroundColor = gradientFill;
      datasetsData[i].fill = true;
      datasetsData[i].borderWidth = 1;
    }

    // Build the chart data.
    var chartData = {
      labels: classes,
      datasets: datasetsData
    };

    // Create the Chart.js line chart.
    var studentChart = new Chart(ctx, {
      type: "line",
      data: chartData,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        legend: {
          display: false // Disable the default legend (we're using a custom HTML legend).
        },
        tooltips: {
          bodySpacing: 4,
          mode: "nearest",
          intersect: 0,
          position: "nearest",
          xPadding: 10,
          yPadding: 10,
          caretPadding: 10
        },
        layout: {
          padding: { left: 15, right: 15, top: 15, bottom: 15 }
        },
        scales: {
          yAxes: [{
            ticks: {
              fontColor: "rgba(0,0,0,0.5)",
              fontStyle: "500",
              beginAtZero: true,
              maxTicksLimit: 5,
              padding: 20
            },
            gridLines: {
              drawTicks: false,
              display: false
            }
          }],
          xAxes: [{
            gridLines: {
              zeroLineColor: "transparent"
            },
            ticks: {
              padding: 20,
              fontColor: "rgba(0,0,0,0.5)",
              fontStyle: "500"
            }
          }]
        },
        // Custom HTML legend generation.
        legendCallback: function (chart) {
          var text = [];
          text.push('<ul class="' + chart.id + '-legend html-legend">');
          for (var i = 0; i < chart.data.datasets.length; i++) {
            text.push('<li><span style="background-color:' + chart.data.datasets[i].legendColor + '"></span>');
            if (chart.data.datasets[i].label) {
              text.push(chart.data.datasets[i].label);
            }
            text.push("</li>");
          }
          text.push("</ul>");
          return text.join("");
        }
      }
    });

    // Insert the custom legend into the container with ID "myChartLegend".
    document.getElementById("myChartLegend").innerHTML = studentChart.generateLegend();

    // Bind click events on the legend items to toggle dataset visibility.
    var legendItems = document.getElementById("myChartLegend").getElementsByTagName("li");
    for (var i = 0; i < legendItems.length; i++) {
      legendItems[i].addEventListener("click", function (event) {
        var index = Array.prototype.indexOf.call(legendItems, event.target.closest("li"));
        var meta = studentChart.getDatasetMeta(index);
        meta.hidden = meta.hidden === null ? !studentChart.data.datasets[index].hidden : null;
        studentChart.update();
      });
    }
  </script>

  <!-- PERSONAL AI ====================================== -->
  <script>
    // Typewriter effect for the message
    const msgElem = document.getElementById('message');
    const fullText = msgElem.getAttribute('data-message');
    let index = 0;
    function typeWriter() {
      if(index < fullText.length) {
        msgElem.innerHTML += fullText.charAt(index);
        index++;
        setTimeout(typeWriter, 50);
      }
    }
    typeWriter();

  </script>

  <!-- ADMIN VIEW STUDENTS ============================ -->
  <script>
    function showStudentProfile(id, name, gender, dob, placeob, address, religion, state, lga, studentClass, arm, hostel, bloodtype, bloodgroup, height, weight, gname, mobile, goccupation, imagePath) {
        document.getElementById('studentId').innerText = id;
        document.getElementById('studentName').innerText = name;
        document.getElementById('studentGender').innerText = gender;
        document.getElementById('studentDob').innerText = dob;
        document.getElementById('studentPlaceOb').innerText = placeob;
        document.getElementById('studentAddress').innerText = address;
        document.getElementById('studentReligion').innerText = religion;
        document.getElementById('studentState').innerText = state;
        document.getElementById('studentLga').innerText = lga;
        document.getElementById('studentClass').innerText = studentClass;
        document.getElementById('studentArm').innerText = arm;
        document.getElementById('studentHostel').innerText = hostel;
        document.getElementById('studentBloodType').innerText = bloodtype;
        document.getElementById('studentBloodGroup').innerText = bloodgroup;
        document.getElementById('studentHeight').innerText = height;
        document.getElementById('studentWeight').innerText = weight;
        document.getElementById('studentGname').innerText = gname;
        document.getElementById('studentMobile').innerText = mobile;
        document.getElementById('studentGoccupation').innerText = goccupation;
        document.getElementById('studentImage').src = imagePath;
        var modal = new bootstrap.Modal(document.getElementById('studentModal'));
        modal.show();
    }
  </script>



<!-- MODIFY RESULTS ============================ -->
    <script>
        // Function to fetch subjects based on selected class and arm
        function loadSubjects() {
            var classVal = document.getElementById('class').value;
            var armVal = document.getElementById('arm').value;

            if(classVal && armVal) {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', '?action=get_subjects&class=' + classVal + '&arm=' + armVal, true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        var subjects = JSON.parse(xhr.responseText);
                        var subjectSelect = document.getElementById('subject');
                        subjectSelect.innerHTML = '<option value="">Select Subject</option>'; // Reset subject dropdown

                        // Populate subject dropdown
                        subjects.forEach(function(subject) {
                            var option = document.createElement('option');
                            option.value = subject.subject;
                            option.textContent = subject.subject;
                            subjectSelect.appendChild(option);
                        });
                    }
                };
                xhr.send();
            }
        }

        // Function to load records based on selected class, arm, term, and subject
        function loadRecords(event) {
            event.preventDefault(); // Prevent form submission

            var classVal = document.getElementById('class').value;
            var armVal = document.getElementById('arm').value;
            var termVal = document.getElementById('term').value;
            var subjectVal = document.getElementById('subject').value;

            if(classVal && armVal && termVal && subjectVal) {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', '?action=get_records&class=' + classVal + '&arm=' + armVal + '&term=' + termVal + '&subject=' + subjectVal, true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        var records = JSON.parse(xhr.responseText);
                        var recordsTable = document.getElementById('recordsTable');
                        recordsTable.innerHTML = ''; // Reset the table

                        // Add headers
                        //recordsTable.innerHTML = '<tr><th>ID</th><th>Name</th><th>Class</th><th>Arm</th><th>Total</th><th>Term</th><th>Subject</th></tr>';

                        // Populate table with records
                        records.forEach(function(record) {
                            var row = document.createElement('tr');
                            row.innerHTML = '<td>' + record.id + '</td><td>' + record.name + '</td><td>' + record.class + '</td><td>' + record.arm + '</td><td>' + record.total + '</td><td>' + record.term + '</td><td>' + record.subject + '</td>';
                            recordsTable.appendChild(row);
                        });
                    }
                };
                xhr.send();
            } else {
                alert('Please select all fields!');
            }
        }
    </script>

    <!-- CLASS TEACHER COMMENTS ======================= -->
    <script>
    function editClassCommentRecord(id, name, comment, schlopen, dayspresent, daysabsent, className, arm, term, session) {
        document.getElementById('id').value = id;
        document.getElementById('name').value = name;
        document.getElementById('comment').value = comment;
        document.getElementById('schlopen').value = schlopen;
        document.getElementById('dayspresent').value = dayspresent;
        document.getElementById('daysabsent').value = daysabsent;
        document.getElementById('class').value = className;
        document.getElementById('arm').value = arm;
        document.getElementById('term').value = term;
        document.getElementById('session').value = session;
    }
    </script>

<!-- INDIVIDUAL RESULT ============================== -->
    <script>
        function fetchSubjects() {
            var classSelect = document.getElementById('class');
            var armSelect = document.getElementById('arm');
            var subjectSelect = document.getElementById('subject');
            
            var selectedClass = classSelect.value;
            var selectedArm = armSelect.value;
            
            fetch('fetch_subjects.php?class=' + selectedClass + '&arm=' + selectedArm)
            .then(response => response.json())
            .then(data => {
                subjectSelect.innerHTML = '';
                data.forEach(subject => {
                    var option = document.createElement('option');
                    option.value = subject.Subject;
                    option.text = subject.Subject;
                    subjectSelect.add(option);
                });
            });
        }
    </script>


<!-- STUDENT ACADEMIC CHART ========================== -->
<script>
    // Retrieve PHP-generated data
    var terms = <?php echo $termsJson; ?>;
    var datasetsData = <?php echo $datasetsJson; ?>;

    // Get canvas context
    var ctx = document.getElementById("adminChart").getContext("2d");

    // Pre-defined color gradients (cycled for multiple sessions)
    var colorOptions = [
        {
            stroke: ['#177dff', '#80b6f4'],
            fill:   ['rgba(23, 125, 255, 0.7)', 'rgba(128, 182, 244, 0.3)']
        },
        {
            stroke: ['#f3545d', '#ff8990'],
            fill:   ['rgba(243, 84, 93, 0.7)', 'rgba(255, 137, 144, 0.3)']
        },
        {
            stroke: ['#fdaf4b', '#ffc478'],
            fill:   ['rgba(253, 175, 75, 0.7)', 'rgba(255, 196, 120, 0.3)']
        }
    ];

    // Assign gradient colors to datasets
    for (var i = 0; i < datasetsData.length; i++) {
        var gradientStroke = ctx.createLinearGradient(500, 0, 100, 0);
        var gradientFill = ctx.createLinearGradient(500, 0, 100, 0);
        var colors = colorOptions[i % colorOptions.length];

        gradientStroke.addColorStop(0, colors.stroke[0]);
        gradientStroke.addColorStop(1, colors.stroke[1]);
        gradientFill.addColorStop(0, colors.fill[0]);
        gradientFill.addColorStop(1, colors.fill[1]);

        datasetsData[i].borderColor = gradientStroke;
        datasetsData[i].pointBackgroundColor = gradientStroke;
        datasetsData[i].backgroundColor = gradientFill;
        datasetsData[i].fill = true;
        datasetsData[i].borderWidth = 1;
        datasetsData[i].legendColor = colors.stroke[0]; // For custom legend
    }

    // Build chart data
    var chartData = {
        labels: terms,
        datasets: datasetsData
    };

    // Create Chart.js line chart
    var studentChart = new Chart(ctx, {
        type: "line",
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                display: false // Using custom HTML legend
            },
            tooltips: {
                bodySpacing: 4,
                mode: "nearest",
                intersect: 0,
                position: "nearest",
                xPadding: 10,
                yPadding: 10,
                caretPadding: 10
            },
            layout: {
                padding: { left: 15, right: 15, top: 15, bottom: 15 }
            },
            scales: {
                yAxes: [{
                    ticks: {
                        fontColor: "rgba(0,0,0,0.5)",
                        fontStyle: "500",
                        beginAtZero: true,
                        maxTicksLimit: 5,
                        padding: 20
                    },
                    gridLines: {
                        drawTicks: false,
                        display: false
                    }
                }],
                xAxes: [{
                    gridLines: {
                        zeroLineColor: "transparent"
                    },
                    ticks: {
                        padding: 20,
                        fontColor: "rgba(0,0,0,0.5)",
                        fontStyle: "500"
                    }
                }]
            },
            legendCallback: function(chart) {
                var text = [];
                text.push('<ul class="' + chart.id + '-legend html-legend">');
                for (var i = 0; i < chart.data.datasets.length; i++) {
                    text.push('<li><span style="background-color:' + chart.data.datasets[i].legendColor + '"></span>');
                    if (chart.data.datasets[i].label) {
                        text.push(chart.data.datasets[i].label);
                    }
                    text.push("</li>");
                }
                text.push("</ul>");
                return text.join("");
            }
        }
    });

    // Insert custom legend
    document.getElementById("myChartLegend").innerHTML = studentChart.generateLegend();

    // Toggle dataset visibility on legend click
    var legendItems = document.getElementById("myChartLegend").getElementsByTagName("li");
    for (var i = 0; i < legendItems.length; i++) {
        legendItems[i].addEventListener("click", function(event) {
            var index = Array.prototype.indexOf.call(legendItems, event.target.closest("li"));
            var meta = studentChart.getDatasetMeta(index);
            meta.hidden = meta.hidden === null ? !studentChart.data.datasets[index].hidden : null;
            studentChart.update();
        });
    }
</script>
