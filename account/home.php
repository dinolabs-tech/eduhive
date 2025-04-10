<?php


// Redirect to login if the user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: ..\login.php");
    exit();
}

include 'db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

   <style>
        body{
            padding-top: 50px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <h5>Welcome back, <?php echo htmlspecialchars($_SESSION['staffname']); ?>!</h5>
                        <hr>
                    </div>
                </div>
            </div>
        </div>
       
       

    <script>
        $(document).ready(function() {
            $('#manage-records').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: 'ajax.php?action=save_track',
                    data: new FormData(this),
                    cache: false,
                    contentType: false,
                    processData: false,
                    method: 'POST',
                    success: function(resp) {
                        let response = JSON.parse(resp);
                        if (response.status == 1) {
                            alert("Data successfully saved");
                            setTimeout(() => location.reload(), 800);
                        }
                    }
                });
            });

            $('#tracking_id').keypress(function(e) {
                if (e.which == 13) {
                    get_person();
                }
            });

            $('#check').click(get_person);

            function get_person() {
                $.ajax({
                    url: 'ajax.php?action=get_pdetails',
                    method: "POST",
                    data: { tracking_id: $('#tracking_id').val() },
                    success: function(resp) {
                        let response = JSON.parse(resp);
                        if (response.status == 1) {
                            $('#name').text(response.name);
                            $('#address').text(response.address);
                            $('[name="person_id"]').val(response.id);
                            $('#details').show();
                        } else {
                            alert("Unknown tracking ID.");
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
