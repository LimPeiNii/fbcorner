<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    if (isset($_SESSION['login_rest_id']) && isset($_SESSION['login_user_id'])) {
        include_once '../../../../db_connect.php';
        include_once '../../helpers/staff.php';
        include_once '../../helpers/user.php';

        $has_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'staff/staff', 'modify_permission');
   
        if ((int)$has_permission['has_permission']){
            if (isset($_GET['staff_id'])) {
                $staff = getStaff($connection, $_GET['staff_id']);
            }
        }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff &VerticalLine; F&amp;B Corner</title>

    <!-- link -->
    <?php include_once '../../../../link.php'?>
</head>
<body>
    <!-- navigation bar -->   
    <?php include_once '../../common/navigation_bar_2.php'; ?>

    <!-- side bar -->
    <div class="side-bar-body">
        <?php include_once '../../common/side_bar.php'; ?>

        <!-- content -->
        <div class="content p-4">
            <?php if ((int)$has_permission['has_permission']){ ?>
                <a href="staff.php"><button type="button" class="btn btn-secondary float-end ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Cancel"><i class="fas fa-undo-alt font-size-20" style="line-height: 30px;"></i></button></a>
                <button type="submit" form="staff-form" class="btn bg-primary bg-opacity-75 text-white float-end" data-bs-toggle="tooltip" data-bs-placement="top" title="Save"><i class="fas fa-save font-size-20" style="line-height: 30px;"></i></button>
                <h3 style="cursor: default; line-height: 45px;" class="m-0 ms-3 font-bernard font-size-35">
                    <?php if (isset($staff)) { ?>
                        Edit Staff
                    <?php } else { ?>
                        Add Staff
                    <?php } ?>
                </h3>
            
                <div>
                    <?php if (isset($_SESSION['success'])) { 
                        $success = $_SESSION['success'];
                        unset($_SESSION['success']);
                    ?>
                        <div class="alert form-message mt-4 mb-0 alert-success" role="alert"><i class="fas fa-check-circle"></i>&nbsp;&nbsp;<?=$success?><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>
                    <?php } ?>

                    <?php if (isset($staff)) { ?>
                        <form action="../../helpers/staff.php?staff_id=<?=$staff['staff_id']?>" class="m-5" id="staff-form" method="POST" enctype="multipart/form-data">
                    <?php } else { ?>
                        <form action="../../helpers/staff.php" class="m-5" id="staff-form" method="POST" enctype="multipart/form-data">
                    <?php } ?>    
                        <div class="mb-4">
                            <input type="text" style="display: none;" name="submit_check_staff">
                            <?php if (isset($staff)) { ?>
                            <input type="number" style="display: none;" name="staff_id" value="<?=$staff['staff_id']?>">
                            <?php } ?>
                            <label for="first-name" class="form-label fw-bold">First Name <span class="required-star">*</span></label>
                            <?php if (isset($staff)) { ?>
                                <input type="text" autocomplete="off" class="form-control" id="first-name" value="<?=$staff['firstname']?>" name="first-name">
                            <?php } else { ?>
                                    <input type="text" autocomplete="off" class="form-control" id="first-name" name="first-name">
                            <?php } ?>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <label for="last-name" class="form-label fw-bold">Last Name</label>
                            <?php if (isset($staff)) { ?>
                                <input type="text" autocomplete="off" class="form-control" id="last-name" value="<?=$staff['lastname']?>" name="last-name">
                            <?php } else { ?>
                                    <input type="text" autocomplete="off" class="form-control" id="last-name" name="last-name">
                            <?php } ?>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <label for="email" class="form-label fw-bold">Email <span class="required-star">*</span></label>
                            <?php if (isset($staff)) { ?>
                                <input type="text" autocomplete="off" class="form-control" id="email" value="<?=$staff['email']?>" name="email">
                            <?php } else { ?>
                                    <input type="text" autocomplete="off" class="form-control" id="email" name="email">
                            <?php } ?>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <label for="contact-num" class="form-label fw-bold">Contact Number <span class="required-star">*</span></label>
                            <?php if (isset($staff)) { ?>
                                <input type="text" autocomplete="off" class="form-control" id="contact-num" value="<?=$staff['contact_num']?>" name="contact-num">
                            <?php } else { ?>
                                    <input type="text" autocomplete="off" class="form-control" id="contact-num" name="contact-num">
                            <?php } ?>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <label for="address" class="form-label fw-bold">Address <span class="required-star">*</span></label>
                            <textarea name="address" id="address" rows="5" class="form-control"><?=isset($staff) ? $staff['address'] : '' ?></textarea>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <label for="dob" class="form-label fw-bold">Date Of Birth <span class="required-star">*</span></label>
                            <?php if (isset($staff)) { ?>
                                <input type="date" class="form-control" id="dob" value="<?=$staff['date_of_birth']?>" name="dob">
                            <?php } else { ?>
                                    <input type="date" class="form-control" id="dob" name="dob">
                            <?php } ?>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <div style="height: fit-content;" class="d-flex mb-2">
                                <label class="form-label fw-bold align-self-center m-0" for="image">Image</label>
                                <button type="button" class="btn btn-outline-primary img-preview-btn btn-sm ms-2"><small>Click to edit</small></button>
                                <button type="button" class="btn btn-outline-primary remove-img btn-sm ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Set to default image"><small>Click to remove</small></button>
                                <input type="file" id="image" class="d-none picture-upload" name="image" accept="image/*">
                            </div>
                            <div class="img-content">
                                <input type="text" name="reset_staff_image" class="d-none">
                                <?php if (isset($staff)) { ?>
                                    <img id="img-preview" src="../../../uploads/staff_img/<?=$staff['image']?>">
                                <?php } else { ?>
                                    <img id="img-preview" src="../../../uploads/staff_img/default_staff_img.png">
                                <?php } ?>
                            </div>
                        </div>
                    </form>
                </div>
            <?php } else { ?>
                <div class="text-center mt-3">
                    <h4 class="mx-4">Sorry, you do not have the permission to access this page.</h4>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- script -->
    <?php include_once '../../../../script.php'?>

    <?php include_once '../../common/side_bar_script.php'?>

    <?php include_once '../../../script.php'?>

    <?php if ((int)$has_permission['has_permission']){ ?>
        <script>
            function close_message() {
                $('.form-message').remove();
                $('#staff-form').removeClass('mt-4');
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
                        $('<div class="alert form-message mb-3 mt-4 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;You can only select files with type "jpg", "jpeg", "png"!</div>').insertBefore('#staff-form');
                        current_element.val('');
                        $('#staff-form').addClass('mt-4');
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

                $("#staff-form").submit(function(event){
                    event.preventDefault();

                    $('.form-message').remove();
                    $('input, textarea').removeClass('is-invalid red-box-shadow');
                    $('.text-danger').text('');

                    $.ajax({
                        url: '../../helpers/staff.php',
                        data: $('#staff-form input:not([type=file]), #staff-form textarea'),
                        method: 'post',
                        success: function(output) {
                            var json = $.parseJSON(output);

                            if (json['error']){
                                $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;There are errors on the form. Please fix them before continuing.</div>').insertBefore('#staff-form');
                                $('#staff-form').addClass('mt-4');
                                $.each(json['error'], function(key, value) {
                                    $('#' + key).addClass('is-invalid red-box-shadow');
                                    $('#' + key).parent().find('p').text(value);
                                });
                                $('html, body').animate({ scrollTop: 0 }, 0);
                            } else {
                                $('input[name="submit_check_staff"]').attr('name', "submit_form_staff");
                                $('#staff-form').unbind().submit();
                            }
                        }
                    });
                });
            });
        </script>
    <?php } ?>
</body>
</html>

<?php

    } else {
        header("Location: ../signin_signup/sign_in.php");
        exit;
    }

?>