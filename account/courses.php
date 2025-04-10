<?php include('db_connect.php'); ?>

<!-- Include Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

<div class="container-fluid p-3">
    <div class="col-lg-12">
        <div class="row mb-4 mt-4">
            <div class="col-md-12">
                <!-- Placeholder for potential future content -->
            </div>
        </div>
        <div class="row">
            <!-- Table Panel -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <b>List of Courses and Fees</b>
                        <span class="float-right">
                            <a class="btn btn-primary btn-sm col-sm-12 float-right" href="javascript:void(0)" id="new_course">
                                <i class="fas fa-plus"></i> New Entry
                            </a>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-condensed table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th class="">Level</th>
                                        <th class="">Description</th>
                                        <th class="">Total Fee</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $i = 1;
                                    $course = $conn->query("SELECT * FROM courses ORDER BY course ASC");
                                    while ($row = $course->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td class="text-center"><?php echo $i++ ?></td>
                                        <td>
                                            <p><b><?php echo $row['course'] . " - " . $row['level'] . " - " . $row['arm'] . " - " . $row['hostel'] ?></b></p>
                                        </td>
                                        <td>
                                            <p><small><i><?php echo $row['description'] ?></i></small></p>
                                        </td>
                                        <td class="text-right">
                                            <p><b><?php echo number_format($row['total_amount'], 2) ?></b></p>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-primary edit_course" type="button" data-id="<?php echo $row['id'] ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete_course" type="button" data-id="<?php echo $row['id'] ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Table Panel -->
        </div>
    </div>
</div>

<style>
    /* Modern Typography */
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* Card Styling */
    .card {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
    }

    .card-header {
        background-color: #007bff;
        color: white;
        border-bottom: 1px solid #0056b3;
    }

    /* Table Styling */
    .table thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }

    .table tbody tr:nth-of-type(odd) {
        background-color: rgba(0, 0, 0, 0.05);
    }

    .table td, .table th {
        vertical-align: middle !important;
    }

    .table td p {
        margin: 0;
    }

    /* Button Styling */
    .btn-outline-primary {
        color: #007bff;
        border-color: #007bff;
    }

    .btn-outline-primary:hover {
        color: #fff;
        background-color: #007bff;
        border-color: #007bff;
    }

    .btn-outline-danger {
        color: #dc3545;
        border-color: #dc3545;
    }

    .btn-outline-danger:hover {
        color: #fff;
        background-color: #dc3545;
        border-color: #dc3545;
    }

    #new_course {
        background-color: #007bff;
        border-color: #007bff;
    }

    #new_course:hover {
        background-color: #0056b3;
        border-color: #004085;
    }

    td button + button {
        margin-left: 5px;
    }

    /* Checkbox Styling (retained and refined) */
    input[type=checkbox] {
        -ms-transform: scale(1.3);
        -moz-transform: scale(1.3);
        -webkit-transform: scale(1.3);
        -o-transform: scale(1.3);
        transform: scale(1.3);
        margin-right: 5px;
        cursor: pointer;
    }
</style>

<script>
    $(document).ready(function(){
        $('table').dataTable();
    });

    $('#new_course').click(function(){
        uni_modal("Fees Entry", "manage_course.php", 'large');
    });

    $('.edit_course').click(function(){
        uni_modal("Fees Entry", "manage_course.php?id=" + $(this).attr('data-id'), 'large');
    });

    $('.delete_course').click(function(){
        _conf("Are you sure to delete this course?", "delete_course", [$(this).attr('data-id')]);
    });

    function delete_course($id){
        start_load();
        $.ajax({
            url: 'ajax.php?action=delete_course',
            method: 'POST',
            data: {id: $id},
            success: function(resp){
                if (resp == 1) {
                    alert_toast("Data successfully deleted", 'success');
                    setTimeout(function(){
                        location.reload();
                    }, 1500);
                }
            }
        });
    }
</script>