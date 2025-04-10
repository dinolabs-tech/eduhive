<?php include('db_connect.php'); ?>
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
<br><br><br>
<div class="container-fluid">
    <div class="col-lg-12">
        <div class="row">
            <div class="col-md-12">
                <div class="card mt-4 mb-4 shadow-sm">
                    <div class="card-header">
                        <b><i class="fa fa-users"></i> List of Students</b>
                        <span class="float-right">
                            <a class="btn btn-primary btn-sm-8" href="javascript:void(0)" id="new_student">
                                <i class="fa fa-plus"></i> New
                            </a>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th>ID No.</th>
                                        <th>Name</th>
                                        <th>Contact</th>
                                        <th>Address</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $i = 1;
                                    $student = $conn->query("SELECT * FROM student ORDER BY name ASC");
                                    while ($row = $student->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td class="text-center"><?php echo $i++; ?></td>
                                        <td>
                                            <p><?php echo $row['id_no']; ?></p>
                                        </td>
                                        <td>
                                            <p><b><?php echo ucwords($row['name']); ?></b></p>
                                        </td>
                                        <td>
                                            <p><small><?php echo $row['contact']; ?></small></p>
                                        </td>
                                        <td>
                                            <p><small><?php echo $row['address']; ?></small></p>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-primary edit_student" type="button" data-id="<?php echo $row['id']; ?>" title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete_student" type="button" data-id="<?php echo $row['id']; ?>" title="Delete">
                                                <i class="fa fa-trash"></i>
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
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('table').dataTable();
    });

    $('#new_student').click(function() {
        uni_modal("New Student", "manage_student.php", "mid-large");
    });

    $('.edit_student').click(function() {
        uni_modal("Manage Student Details", "manage_student.php?id=" + $(this).attr('data-id'), "mid-large");
    });

    $('.delete_student').click(function() {
        _conf("Are you sure to delete this Student?", "delete_student", [$(this).attr('data-id')]);
    });

    function delete_student($id) {
        start_load();
        $.ajax({
            url: 'ajax.php?action=delete_student',
            method: 'POST',
            data: { id: $id },
            success: function(resp) {
                if (resp == 1) {
                    alert_toast("Data successfully deleted", 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                }
            }
        });
    }
</script>