<?php
session_start();
$loginid = $_SESSION['STUDENT_ID'];
error_reporting(1);
$time = date("h:i:s");
$date = date("l, F j, Y");
$tdate = $time . '  ' . $date;
include("database.php");
extract($_POST);
extract($_GET);
extract($_SESSION);

if(isset($subid) && isset($testid))
{
    $_SESSION['sid'] = $subid;
    $_SESSION['tid'] = $testid;
}

// Update student status to 'done'
mysqli_query($cn, "UPDATE studentreg SET status = 'done' WHERE id = '$loginid'") or die(mysqli_error($cn));

// Insert result into the database
mysqli_query($cn, "INSERT INTO mst_result(login, test_id, test_date, score) VALUES('$loginid', $tid, '$tdate', " . $_SESSION['trueans'] . ")") or die(mysqli_error($cn));
?>

<script type="text/javascript">
    window.location='result.php';
</script>