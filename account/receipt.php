<?php 
include 'db_connect.php';
$fees = $conn->query("SELECT ef.*,s.name as sname,s.id_no,concat(c.course,' - ',c.level) as `class` FROM student_ef_list ef inner join student s on s.id = ef.student_id inner join courses c on c.id = ef.course_id  where ef.id = {$_GET['ef_id']}");
foreach($fees->fetch_array() as $k => $v){
    $$k= $v;
}
$payments = $conn->query("SELECT * FROM payments where ef_id = $id ");
$pay_arr = array();
while($row=$payments->fetch_array()){
    $pay_arr[$row['id']] = $row;
}

// Calculate totals
$cfees = $conn->query("SELECT * FROM fees where course_id = $course_id");
$fee_arr = array();
$ftotal = 0;
while ($row = $cfees->fetch_assoc()) {
    $fee_arr[] = $row;
    $ftotal += $row['amount'];
}

$ptotal = 0;
foreach ($pay_arr as $row) {
    if($row["id"] <= $_GET['pid'] || $_GET['pid'] == 0){
        $ptotal += $row['amount'];
    }
}
?>

<style>
    .container-fluid {
        padding: 20px;
        font-family: 'Arial', sans-serif;
    }
    .student-details, .payment-info, .summary {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .student-details h2 {
        margin-bottom: 15px;
        font-size: 1.75rem;
        color: #343a40;
    }
    .payment-info h3, .summary h3 {
        margin-bottom: 15px;
        font-size: 1.25rem;
        color: #495057;
    }
    .details.flex {
        display: flex;
        justify-content: space-between;
        gap: 20px;
    }
    .details .w-50 {
        width: 48%;
    }
    .table {
        width: 100%;
        border-collapse: collapse;
    }
    .table th, .table td {
        padding: 12px;
        vertical-align: middle;
    }
    .table thead th {
        background-color: #e9ecef;
        color: #495057;
        font-weight: 600;
    }
    .table tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }
    .table tfoot th {
        background-color: #dee2e6;
        font-weight: 600;
    }
    .text-center {
        text-align: center;
    }
    .text-right {
        text-align: right;
    }
    hr {
        border: 0;
        border-top: 1px solid #dee2e6;
        margin: 20px 0;
    }
    @media (max-width: 768px) {
        .details.flex {
            flex-direction: column;
        }
        .details .w-50 {
            width: 100%;
            margin-bottom: 20px;
        }
    }
    @media print {
        .container-fluid {
            padding: 0;
        }
        .student-details, .payment-info, .summary {
            background-color: transparent;
            box-shadow: none;
            padding: 10px 0;
            margin-bottom: 10px;
        }
        .details.flex {
            display: block;
        }
        .details .w-50 {
            width: 100%;
            margin-bottom: 10px;
        }
    }
</style>

<div class="container-fluid">
    <p class="text-center"><b><?php echo $_GET['pid'] == 0 ? "Payments" : 'Payment Receipt' ?></b></p>
    <hr>
    <div class="student-details">
        <h2><?php echo ucwords($sname) ?></h2>
        <p><strong>EF. No:</strong> <?php echo $ef_no ?></p>
        <p><strong>Course/Level:</strong> <?php echo $class ?></p>
    </div>
    <?php if($_GET['pid'] > 0): ?>
    <div class="payment-info">
        <h3>Payment Information</h3>
        <p><strong>Date:</strong> <?php echo isset($pay_arr[$_GET['pid']]) ? date("M d,Y",strtotime($pay_arr[$_GET['pid']]['date_created'])): '' ?></p>
        <p><strong>Amount:</strong> <?php echo isset($pay_arr[$_GET['pid']]) ? number_format($pay_arr[$_GET['pid']]['amount'],2): '' ?></p>
        <p><strong>Remarks:</strong> <?php echo isset($pay_arr[$_GET['pid']]) ? $pay_arr[$_GET['pid']]['remarks']: '' ?></p>
    </div>
    <?php endif; ?>
    <div class="summary">
        <h3>Payment Summary</h3>
        <p><strong>Total Payable Fee:</strong> <?php echo number_format($ftotal,2) ?></p>
        <p><strong>Total Paid:</strong> <?php echo number_format($ptotal,2) ?></p>
        <p><strong>Balance:</strong> <?php echo number_format($ftotal - $ptotal,2) ?></p>
    </div>
    <div class="details flex">
        <div class="w-50">
            <h4>Fee Details</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Fee Type</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach ($fee_arr as $row) {
                    ?>
                    <tr>
                        <td><?php echo $row['description'] ?></td>
                        <td class="text-right"><?php echo number_format($row['amount'],2) ?></td>
                    </tr>
                    <?php
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Total</th>
                        <th class="text-right"><?php echo number_format($ftotal,2) ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="w-50">
            <h4>Payment Details</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach ($pay_arr as $row) {
                        if($row["id"] <= $_GET['pid'] || $_GET['pid'] == 0){
                    ?>
                    <tr>
                        <td><?php echo date("Y-m-d",strtotime($row['date_created'])) ?></td>
                        <td class="text-right"><?php echo number_format($row['amount'],2) ?></td>
                    </tr>
                    <?php
                        }
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Total</th>
                        <th class="text-right"><?php echo number_format($ptotal,2) ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>