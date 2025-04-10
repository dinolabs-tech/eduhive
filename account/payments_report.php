<?php
include 'db_connect.php'; // Ensure this file connects to your database
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m'); // Default to current month if not specified
?>
<br><br><br>
<div class="container-fluid">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <b>Payment Report</b>
            </div>
            <div class="card-body">
                <!-- Month Selection -->
                <div class="form-group row justify-content-center pt-4">
                    <label for="month" class="col-sm-1 col-form-label">Month</label>
                    <div class="col-sm-3">
                        <input type="month" name="month" id="month" value="<?php echo $month ?>" class="form-control">
                    </div>
                </div>
                <hr>

                <!-- Payment Report Table -->
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="report-list">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>Date</th>
                                    <th>Reg. No.</th>
                                    <th>Name</th>
                                    <th class="text-right">Paid Amount</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                $total = 0;
                                // Query to fetch payment data for the selected month
                                $payments = $conn->query("SELECT p.*, s.name as sname, ef.ef_no, s.id_no FROM payments p INNER JOIN student_ef_list ef ON ef.id = p.ef_id INNER JOIN student s ON s.id = ef.student_id WHERE DATE_FORMAT(p.date_created, '%Y-%m') = '$month' ORDER BY UNIX_TIMESTAMP(p.date_created) ASC");
                                if ($payments->num_rows > 0):
                                    while ($row = $payments->fetch_array()):
                                        $total += $row['amount'];
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo $i++ ?></td>
                                    <td><p><b><?php echo date("M d, Y H:i A", strtotime($row['date_created'])) ?></b></p></td>
                                    <td><p><b><?php echo $row['id_no'] ?></b></p></td>
                                    <td><p><b><?php echo ucwords($row['sname']) ?></b></p></td>
                                    <td class="text-right"><p><b><?php echo number_format($row['amount'], 2) ?></b></p></td>
                                    <td><p><b><?php echo $row['remarks'] ?></b></p></td>
                                </tr>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <th class="text-center" colspan="6">No Data.</th>
                                </tr>
                                <?php 
                                endif;
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-right">Total</th>
                                    <th class="text-right"><?php echo number_format($total, 2) ?></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <hr>
                    <!-- Print Button -->
                    <div class="col-md-12 mb-4">
                        <center>
                            <button class="btn btn-success btn-sm col-sm-3" type="button" id="print"><i class="fa fa-print"></i> Print</button>
                        </center>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Noscript Styles for Printing -->
<noscript>
    <style>
        table#report-list {
            width: 100%;
            border-collapse: collapse;
        }
        table#report-list td, table#report-list th {
            border: 1px solid;
            padding: 8px;
        }
        p {
            margin: 0;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
    </style>
</noscript>

<!-- CSS Styling -->
<style>
    :root {
        --primary-color: #007bff; /* Blue for headers and buttons */
        --primary-light: #e7f1ff; /* Light blue for highlights */
        --gray-light: #f8f9fa;    /* Light gray for backgrounds */
        --text-dark: #333;        /* Dark text for readability */
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f6f9;
    }

    .card {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        border: none;
        background-color: #fff;
    }

    .card-header {
        background-color: var(--primary-color);
        color: white;
        text-align: center;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
        padding: 15px;
    }

    .card-title {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 500;
    }

    .card-body {
        padding: 20px;
    }

    #month {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 1rem;
    }

    #month:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        outline: none;
    }

    #report-list {
        width: 100%;
        border-collapse: collapse;
    }

    #report-list th,
    #report-list td {
        padding: 15px;
        border: none;
        vertical-align: middle;
    }

    #report-list thead th {
        background-color: var(--primary-light);
        color: var(--text-dark);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.9rem;
        border-bottom: 2px solid #dee2e6;
    }

    #report-list tbody tr:nth-child(even) {
        background-color: var(--gray-light);
    }

    #report-list tfoot th {
        background-color: var(--gray-light);
        font-weight: bold;
        padding: 15px;
    }

    #report-list p {
        margin: 0;
    }

    #print {
        padding: 10px 20px;
        font-size: 1rem;
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        border-radius: 5px;
    }

    @media print {
        table#report-list {
            width: 100%;
            border-collapse: collapse;
        }

        table#report-list th,
        table#report-list td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        table#report-list thead th {
            background-color: #f2f2f2;
        }

        table#report-list tfoot th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
    }
</style>

<!-- JavaScript for Functionality -->
<script>
    // Update page when month changes
    $('#month').change(function() {
        location.replace('index.php?page=payments_report&month=' + $(this).val());
    });

    // Print functionality
    $('#print').click(function() {
        var _c = $('#report-list').clone();
        var ns = $('noscript').clone();
        ns.append(_c);
        var nw = window.open('', '_blank', 'width=900,height=600');
        nw.document.write('<p class="text-center"><b>Payment Report as of <?php echo date("F, Y", strtotime($month)) ?></b></p>');
        nw.document.write(ns.html());
        nw.document.close();
        nw.print();
        setTimeout(() => {
            nw.close();
        }, 500);
    });
</script>