<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    include_once '../../../../db_connect.php';
    include_once '../../helpers/customer.php';

  if (isset($_SESSION['login_cus_id'])) {

    $success_msg = '';
    $error_msg = '';
    $info_msg = '';
    if (isset($_SESSION['success'])){
      $success_msg = $_SESSION['success'];
      unset($_SESSION['success']);
    } elseif (isset($_SESSION['error'])){
      $error_msg = $_SESSION['error'];
      unset($_SESSION['error']);
    } elseif (isset($_SESSION['info'])){
      $info_msg = $_SESSION['info'];
      unset($_SESSION['info']);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile &VerticalLine; F&amp;B Corner</title>

    <?php include_once '../../../../link.php'?>
    
    <style>
        body{
            height: 100%;
        }

        #title-image, #title-image img{
            min-width: 180px;
            max-width: 180px;
        }

        @media (min-width: 768px){
          .col{
            flex: 1 0 0%;
          }

          #title-name{
            margin-left: 1.5rem;
          }

          #title-name h2{
              margin-left: 0.5rem;
          }
        }

        @media (max-width: 768px){
          .row>*{
            flex-shrink: unset;
          }

          #title-name, #title-image{
              width: 100% !important;
          }

          #title-name h1, #title-name h2, #title-image img{
            align-self: center!important;
          }

          #title-image{
            max-width: 100% !important;
            min-width: 100% !important;
            flex-direction: column;
          }
        }
    </style>

</head>
<body>
    <?php include_once '../../common/navigation_bar.php'; ?>

    <div class="d-flex flex-column" style="min-height: calc(100% - 76px)">
    <main>
      <div class="m-5">
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-2 mx-3">
            <div class="col-sm-12 col-md-2 d-flex" style="min-width: 180px;" id="title-image">
                <img width='180' height='180' class='rounded-circle align-self-center' style="border: 3px solid #d0efff;" src="../../../uploads/profile_pic/<?=$profile_img?>">
            </div>
            <div class="col-sm-12 col-md-10 align-self-center d-flex flex-column" id="title-name" style="width: fit-content;">
                <h1 style="cursor: default; font-size: 3rem;" class="mb-1 pt-2 font-bernard d-inline-block text-nowrap">My Profile</h1>
                <h2 class="mb-1 d-inline-block" style="cursor: default;"><?=$customer['firstname'] . ' ' . $customer['lastname']?></h2>
            </div>
        </div>

        <div class="d-flex flex-column mt-3">
        <button type="submit" style="width: fit-content;" class="align-self-end btn btn-primary mx-4 mt-2" form="details-form">Save</button>
        <form action="../../helpers/customer.php?cus_id=<?=$customer['cus_id']?>" class="mb-5 m-4" id="details-form" method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <input type="text" style="display: none;" name="submit_check_cus">
                <label for="first-name" class="form-label fw-bold">First Name <span class="required-star">*</span></label>
                <input type="text" autocomplete="off" class="form-control" id="first-name" value="<?=$customer['firstname']?>" name="first-name">
                <p class="d-block text-danger"></p>
            </div>
            <div class="mb-4">
                <label for="last-name" class="form-label fw-bold">Last Name</label>
                <input type="text" autocomplete="off" class="form-control" id="last-name" value="<?=$customer['lastname']?>" name="last-name">
                <p class="d-block text-danger"></p>
            </div>
            <div class="mb-4">
                <label for="email" class="form-label fw-bold">Email <span class="required-star">*</span></label>
                <input type="text" autocomplete="off" class="form-control" id="email" value="<?=$customer['email']?>" name="email">
                <p class="d-block text-danger"></p>
            </div>
            <div class="mb-4">
                <label for="contact-num" class="form-label fw-bold">Contact Number <span class="required-star">*</span></label>
                <input type="text" autocomplete="off" class="form-control" id="contact-num" value="<?=$customer['contact_num']?>" name="contact-num">
                <p class="d-block text-danger"></p>
            </div>
            <div class="mb-4">
                <label for="pwd" class="form-label fw-bold">Password</label>
                <input type="password" class="form-control" id="pwd" name="pwd">
                <p class="d-block text-danger"></p>
            </div>
            <div class="mb-4">
                <label for="c-pwd" class="form-label fw-bold">Confirm Password</label>
                <input type="password" class="form-control" id="c-pwd" name="c-pwd">
                <p class="d-block text-danger"></p>
            </div>
            <div class="mb-4">
                <div style="height: fit-content;" class="d-flex mb-3">
                    <label class="form-label fw-bold align-self-center m-0" for="profile_pic">Profile Picture</label>
                    <button type="button" class="btn btn-outline-primary img-preview-btn btn-sm ms-2"><small>Click to edit</small></button>
                    <button type="button" class="btn btn-outline-primary remove-img btn-sm ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Set to default profile picture"><small>Click to remove</small></button>
                    <input type="file" id="profile_pic" class="d-none picture-upload" accept="image/*" name="profile_pic">
                </div>
                <div class="img-content">
                    <input type="text" name="reset_profile" class="d-none">
                    <img id="img-preview" src="../../../uploads/profile_pic/<?=$customer['profile_pic']?>">
                </div>
            </div>
        </form>
        </div>
      </div>
    </main>

    <?php include_once '../../common/notification.php'; ?>

    <?php include_once '../../common/footer.php'?>
    </div>
    
    <?php include_once '../../../../script.php'?>

    <?php include_once '../../../script.php'?>

    <script>
      function close_message() {
        $('.form-message').remove();
      }

      $(document).ready(function() {
        $('.img-preview-btn').click(function() {
            $(this).parent().find('input').trigger('click');
        });

        $('.remove-img').click(function() {
            $(this).parent().find('input').val('');
            $(this).parent().parent().find('img').remove();
            $(this).parent().parent().find('.img-content input').val('remove');
        });

        $('.picture-upload').change(function() {
            $('.form-message').remove();

            var upload_img = this.files[0];
            var img_name = upload_img.name
            var allowed_ext = ["jpg", "jpeg", "png"]; 
            var current_element = $(this);

            if ($.inArray(img_name.substring(img_name.lastIndexOf('.')+1).toLowerCase(), allowed_ext) == -1){
                $('<div class="alert form-message mb-1 mt-4 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;You can only select files with type "jpg", "jpeg", "png"!</div>').insertBefore('#details-form');
                current_element.val('');
                $('#details-form').addClass('mt-4');
                $('html, body').animate({ scrollTop: 0 }, 0);
            } else if (upload_img){
                let reader = new FileReader();
                reader.onload = function(event){
                    if (current_element.parent().parent().find('.img-content').children().length > 1)
                        current_element.parent().parent().find('img').attr('src', event.target.result);
                    else
                        current_element.parent().parent().find('.img-content').append('<img class="me-2 mb-2" id="img-preview" src="' + event.target.result + '">');
                }
                reader.readAsDataURL(upload_img);
            }                
        });

        $("#details-form").submit(function(event){
            event.preventDefault();

            $('.form-message').remove();
            $('input').removeClass('is-invalid red-box-shadow');
            $('.text-danger').text('');

            $.ajax({
                url: '../../helpers/customer.php',
                data: $('#details-form input:not([type=file])'),
                method: 'post',
                success: function(output) {
                    var json = $.parseJSON(output);

                    if (json['error']){
                        $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;There are errors on the form. Please fix them before continuing.</div>').insertBefore('#details-form');
                        $('#details-form').addClass('mt-4');
                        $.each(json['error'], function(key, value) {
                            $('#' + key).addClass('is-invalid red-box-shadow');
                            $('#' + key).parent().find('p').text(value);
                        });
                        $('html, body').animate({ scrollTop: 0 }, 0);
                    } else {
                        $('input[name="submit_check_cus"]').attr('name', "submit_form_cus");
                        $('#details-form').unbind().submit();
                    }
                }
            });
        });
      });
    </script>

  </body>
</html>


<?php

  } else {
    header("Location: ../homepage/index.php");
    exit;
  }
  
?>