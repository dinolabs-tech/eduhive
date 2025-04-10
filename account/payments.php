<?php include 'db_connect.php'; ?>
<div class="container-fluid">
    <div class="col-lg-12">
        <div class="row mb-4 mt-4">
            <div class="col-md-12">
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <b>List of Payments</b>
                        <span class="float:right">
                            <a class="btn btn-primary btn-block btn-sm col-sm-2 float-right" href="javascript:void(0)" id="new_payment">
                                <i class="fa fa-plus"></i> New
                            </a>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th>Date</th>
                                        <th>Reg. No.</th>
                                        <th>Name</th>
                                        <th>Paid Amount</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $i = 1;
                                    $payments = $conn->query("SELECT p.*, s.name as sname, ef.ef_no, s.id_no FROM payments p INNER JOIN student_ef_list ef ON ef.id = p.ef_id INNER JOIN student s ON s.id = ef.student_id ORDER BY UNIX_TIMESTAMP(p.date_created) DESC");
                                    if ($payments->num_rows > 0):
                                        while ($row = $payments->fetch_assoc()):
                                            $paid = $conn->query("SELECT SUM(amount) as paid FROM payments WHERE ef_id=" . $row['id']);
                                            $paid = $paid->num_rows > 0 ? $paid->fetch_array()['paid'] : '';
                                    ?>
                                    <tr>
                                        <td class="text-center"><?php echo $i++ ?></td>
                                        <td>
                                            <p><b><?php echo date("M d,Y H:i A", strtotime($row['date_created'])) ?></b></p>
                                        </td>
                                        <td>
                                            <p><b><?php echo $row['id_no'] ?></b></p>
                                        </td>
                                        <td>
                                            <p><b><?php echo ucwords($row['sname']) ?></b></p>
                                        </td>
                                        <td class="text-right">
                                            <p><b><?php echo number_format($row['amount'], 2) ?></b></p>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-primary view_payment" type="button" data-id="<?php echo $row['id'] ?>" data-ef_id="<?php echo $row['ef_id'] ?>" title="View Payment"><i class="fa fa-eye"></i></button>
                                            <button class="btn btn-sm btn-outline-primary edit_payment" type="button" data-id="<?php echo $row['id'] ?>" title="Edit Payment"><i class="fa fa-edit"></i></button>
                                            <button class="btn btn-sm btn-outline-danger delete_payment" type="button" data-id="<?php echo $row['id'] ?>" title="Delete Payment"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    <?php 
                                        endwhile; 
                                    else:
                                    ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No data.</td>
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
    
    $('#new_payment').click(function(){
        uni_modal("New Payment", "manage_payment.php", "mid-large");
    });

    $('.view_payment').click(function(){
        uni_modal("Payment Details", "view_payment.php?ef_id=" + $(this).attr('data-ef_id') + "&pid=" + $(this).attr('data-id'), "mid-large");
    });

    $('.edit_payment').click(function(){
        uni_modal("Manage Payment", "manage_payment.php?id=" + $(this).attr('data-id'), "mid-large");
    });

    $('.delete_payment').click(function(){
        _conf("Are you sure to delete this payment?", "delete_payment", [$(this).attr('data-id')]);
    });

    function delete_payment($id){
        start_load();
        $.ajax({
            url: 'ajax.php?action=delete_payment',
            method: 'POST',
            data: {id: $id},
            success: function(resp){
                if (resp == 1){
                    alert_toast("Data successfully deleted", 'success');
                    setTimeout(function(){
                        location.reload();
                    }, 1500);
                }
            }
        });
    }
</script>