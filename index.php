<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
<title>Page Title</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

<!-- jQuery library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!-- Popper JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>

<!-- Latest compiled JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container">
    <div class="row mb-5">
        <div class="col-4" id="session_data"></div>
        <div class="col-4 mt-5">
            <div id= "rename-form" class="form-group">
                <select class="form-control" name="" id="select-giro">
                </select>
                <input type="text" class="form-control mt-3" value = "" id = "rename"/>
                <input type = "button" class="btn-danger form-control mt-1" id = "renamebtn" value = "Rename">
                <input type = "button" class='btn btn-success form-control mt-5' id= "save" value ="Save">   
            </div>
        </div>
        <div class="col-4 text-center mt-5">
        <input type="file" id= "fileUpload" hidden/>
        <input type="button" id="importBtn" class="btn btn-success" name="Import file" value="Import file"/>
        <input type="submit" id="submitBtn" name="submit" hidden/>
        </div>
    </div>
    <div class="row">
        <div class="col-4">
        </div>
        <div class="col-4" id="temp">
        </div>
    </div>
</div>
</body>
<script>
    $(document).ready(function(){
        $('#rename-form').hide();
    });
    $("#importBtn").click(function(){
        $("#fileUpload").trigger('click');
    })
    $("#fileUpload").change(function(){
        var file_data = $('#fileUpload').prop('files')[0];   
        var form_data = new FormData();                  
        form_data.append('file', file_data);

        $.ajax({
        url: 'parser.php', // point to server-side PHP script 
            cache: false,
            contentType: false,
            processData: false,
            data: form_data,                         
            type: 'post',
            success: function(response){
                // $("#session_data").text()
                $("#rename-form").show();
                var dataa = response;
                $("#select-giro").html(response);

                $("#importBtn").hide();
                // console.log(dataa[0]); // display response from the PHP script, if any
            }
        });
    });

     $('#select-giro').on('change', function() {
        $('#rename').val($(this).find(":selected").html());
    });

    $('#renamebtn').click(function(){
        var rename = $('#rename').val();
        var giroID = $("#select-giro").find(":selected").val();
        var flag1 = true;
        var data = {flag1: flag1, ID: giroID, name: rename};   
        $.ajax({
            url: 'parser.php', // point to server-side PHP script 
            data: data,                         
            type: 'post',
            success: function(response){
                $('#rename').val("");
                // alert(response); // display response from the PHP script, if any
            }
        });
    })

    $("#save").click(function(){
        var rename = $('#rename').val();
        if(rename){
            var giroID = $("#select-giro").find(":selected").val();
        }
        else{
            giroID = -1;
        }
        var flag = true;
        var data = {flag: flag, ID: giroID, name: rename};   

        $.ajax({
            url: 'parser.php', // point to server-side PHP script 
            data: data,                         
            type: 'post',
            success: function(response){
            
                console.log(response); // display response from the PHP script, if any
                $("#importBtn").show();
                $("#rename-form").hide();
            }
        });
    });

</script>

</html>