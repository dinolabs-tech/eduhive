<?php 
include 'db_connect.php'; 

if(isset($_GET['id'])){
    $qry = $conn->query("SELECT * FROM student where id= ".$_GET['id']);
    foreach($qry->fetch_array() as $k => $val){
        $$k=$val;
    }
}
?>
<div class="container-fluid">
    <form action="" id="manage-student">
        <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
        <div id="msg" class="form-group"></div>
        <div class="form-group">
            <label for="" class="control-label">Id No.</label>
            <input type="text" class="form-control" name="id_no" id="id_no" value="<?php echo isset($id_no) ? $id_no :'' ?>" required>
        </div>
        <div class="form-group">
            <label for="" class="control-label">Name</label>
            <input type="text" class="form-control" name="name" id="name" value="<?php echo isset($name) ? $name :'' ?>" required readonly>
        </div>
        <div class="form-group">
            <label for="" class="control-label">Parent/Guardian Mobile</label>
            <input type="text" class="form-control" name="contact" id="contact" value="<?php echo isset($contact) ? $contact :'' ?>" required readonly>
        </div>
        <!--<div class="form-group">
            <label for="" class="control-label">Email</label>
            <input type="email" class="form-control" name="email" id="email" value="<?php echo isset($email) ? $email :'' ?>" required readonly>
        </div>-->
        <div class="form-group">
            <label for="" class="control-label">Address</label>
            <textarea readonly name="address" id="address" cols="30" rows="3" class="form-control" required><?php echo isset($address) ? $address :'' ?></textarea>
        </div>
    </form>
</div>


<script>
         $('#manage-student').on('reset',function(){
        $('#msg').html('')
        $('input:hidden').val('')
    })
    $('#manage-student').submit(function(e){
        e.preventDefault()
        start_load()
        $('#msg').html('')
        $.ajax({
            url:'ajax.php?action=save_student',
            data: new FormData($(this)[0]),
            cache: false,
            contentType: false,
            processData: false,
            method: 'POST',
            type: 'POST',
            success:function(resp){
                if(resp==1){
                    alert_toast("Data successfully saved.",'success')
                        setTimeout(function(){
                            location.reload()
                        },1000)
                }else if(resp == 2){
                $('#msg').html('<div class="alert alert-danger mx-2">ID # already exist.</div>')
                end_load()
                }   
            }
        })
    })

    $('.select2').select2({
        placeholder:"Please Select here",
        width:'100%'
    })
    
    
            $(document).ready(function(){
                $('#id_no').on('input', function(){
                    var idNo = $(this).val();
                    
                    if(idNo !== ''){
                        $.ajax({
                            url: 'getstudentbyid.php',
                            method: 'POST',
                            data: {action: 'get_student_by_idno', id_no: idNo},
                            dataType: 'json',
                            success: function(response){
                                if(response.status === 'success'){
                                    $('#name').val(response.data.name);
                                    $('#contact').val(response.data.contact);
                                    //$('#email').val(response.data.email);
                                    $('#address').val(response.data.address);
                                    $('#msg').html('<div class="alert alert-success">Record found</div>');
                                } else {
                                    // Clear fields if no record found
                                    $('#name').val('');
                                    $('#contact').val('');
                                // $('#email').val('');
                                    $('#address').val('');
                                    $('#msg').html('<div class="alert alert-warning">No record found</div>');
                                }
                            },
                            error: function(xhr, status, error){
                                $('#msg').html('<div class="alert alert-danger">Error occurred while fetching data</div>');
                            }
                        });
                    } else {
                        // Clear fields if ID No is empty
                        $('#name').val('');
                        $('#contact').val('');
                        $('#email').val('');
                        $('#address').val('');
                        $('#msg').html('');
                    }
                });
            });


   
</script>