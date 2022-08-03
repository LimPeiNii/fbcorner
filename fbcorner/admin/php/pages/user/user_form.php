<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    if (isset($_SESSION['login_rest_id']) && isset($_SESSION['login_user_id'])) {
        include_once '../../../../db_connect.php';
        include_once '../../helpers/user.php';
        include_once '../../helpers/user_group.php';
        include_once '../../helpers/staff.php';

        $has_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'user/user', 'modify_permission');

        if ((int)$has_permission['has_permission']){
            $user_groups = getUserGroups($connection, $_SESSION['login_rest_id']);
            $staffs = getStaffs($connection, $_SESSION['login_rest_id']);

            if (isset($_GET['user_id'])) {
                $user = getUser($connection, $_GET['user_id']);
                $edit_user_name = $user['username'];
                $user_id = (string)$user['user_id'];
            } else {
                $edit_user_name = '';
                $user_id = '';
            }
        }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User &VerticalLine; F&amp;B Corner</title>

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
                <a href="user.php"><button type="button" class="btn btn-secondary float-end ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Cancel"><i class="fas fa-undo-alt font-size-20" style="line-height: 30px;"></i></button></a>
                <button type="submit" form="user-form" class="btn bg-primary bg-opacity-75 text-white float-end" data-bs-toggle="tooltip" data-bs-placement="top" title="Save"><i class="fas fa-save font-size-20" style="line-height: 30px;"></i></button>
                <h3 style="cursor: default; line-height: 45px;" class="m-0 ms-3 font-bernard font-size-35">
                    <?php if (isset($user)) { ?>
                        Edit User
                    <?php } else { ?>
                        Add User
                    <?php } ?>
                </h3>
            
                <div>
                    <?php if (isset($_SESSION['success'])) { 
                        $success = $_SESSION['success'];
                        unset($_SESSION['success']);
                    ?>
                        <div class="alert form-message mt-4 mb-0 alert-success" role="alert"><i class="fas fa-check-circle"></i>&nbsp;&nbsp;<?=$success?><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>
                    <?php } ?>

                    <?php if (isset($user)) { ?>
                        <form action="../../helpers/user.php?user_id=<?=$user['user_id']?>" class="m-5" id="user-form" method="POST">
                    <?php } else { ?>
                        <form action="../../helpers/user.php" class="m-5" id="user-form" method="POST">
                    <?php } ?>    
                        <div class="mb-4">
                            <input type="text" style="display: none;" name="submit_form_user">
                            <label for="user-name" class="form-label fw-bold">Username <span class="required-star">*</span></label>
                            <?php if (isset($user)) { ?>
                                <input type="text" class="form-control" id="user-name" value="<?=$user['username']?>" name="user-name">
                            <?php } else { ?>
                                    <input type="text" class="form-control" id="user-name" name="user-name">
                            <?php } ?>
                            <p class="d-block text-danger"></p>
                        </div>
                        <?php if (!(isset($user) && empty($user['staff_id']))) { ?>
                        <div class="mb-4">
                            <label for="staff-id" class="form-label fw-bold">Staff <span class="required-star">*</span></label>
                            <select class="form-select" id="staff-id" name="staff-id">
                                <option value="">-- Please Select --</option>
                                <?php if (isset($user)) { 
                                    foreach ($staffs as $staff) {
                                        //staff name
                                        if (!empty($staff['lastname']))
                                            $staff_name = $staff['firstname'] . ' ' . $staff['lastname'];
                                        else
                                            $staff_name = $staff['firstname'];
                                        
                                        if ($user['staff_id'] == $staff['staff_id']) {
                                ?>
                                        <option value="<?=$staff['staff_id']?>" selected="selected"><?=$staff_name . ' (' . $staff['email'] . ') '?></option>
                                <?php } else { ?>
                                        <option value="<?=$staff['staff_id']?>"><?=$staff_name . ' (' . $staff['email'] . ') '?></option>                               
                                <?php }
                                    } 
                                } else { 
                                    foreach ($staffs as $staff) {
                                        //staff name
                                        if (!empty($staff['lastname']))
                                            $staff_name = $staff['firstname'] . ' ' . $staff['lastname'];
                                        else
                                            $staff_name = $staff['firstname'];
                                ?>
                                        <option value="<?=$staff['staff_id']?>"><?=$staff_name . ' (' . $staff['email'] . ') '?></option>    
                                <?php }
                                } ?>
                            </select>
                            <p class="d-block text-danger"></p>
                        </div>
                        <?php } ?>
                        <div class="mb-4">
                            <label for="user-group" class="form-label fw-bold">User Group <span class="required-star">*</span></label>
                            <select class="form-select" id="user-group" name="user-group">
                                <option value="">-- Please Select --</option>
                                <?php if (isset($user)) { 
                                    foreach ($user_groups as $user_group) {    
                                        if ($user['user_group_id'] == $user_group['user_group_id']) {
                                ?>
                                        <option value="<?=$user_group['user_group_id']?>" selected="selected"><?=$user_group['user_group_name']?></option>
                                <?php } else { ?>
                                        <option value="<?=$user_group['user_group_id']?>"><?=$user_group['user_group_name']?></option>                               
                                <?php }
                                    } 
                                } else { 
                                    foreach ($user_groups as $user_group) {
                                ?>
                                        <option value="<?=$user_group['user_group_id']?>"><?=$user_group['user_group_name']?></option>    
                                <?php }
                                } ?>
                            </select>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <label for="pwd" class="form-label fw-bold">Password <?php if (!isset($_GET['user_id'])) { ?><span class="required-star">*</span><?php } ?></label>
                            <input type="password" class="form-control" id="pwd" name="pwd">
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <label for="c-pwd" class="form-label fw-bold">Confirm Password <?php if (!isset($_GET['user_id'])) { ?><span class="required-star">*</span><?php } ?></label>
                            <input type="password" class="form-control" id="c-pwd" name="c-pwd">
                            <p class="d-block text-danger"></p>
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
                $('#user-form').removeClass('mt-4');
            }

            $(document).ready(function() {
                
                $("#user-form").submit(function(event){
                    event.preventDefault();

                    $('#user-form').removeClass('mt-4');
                    $('.form-message').remove();
                    $('.text-danger').text('');
                    $('input').each(function() {
                        $(this).removeClass('red-box-shadow is-invalid');
                    });
                    $('select').removeClass('red-box-shadow is-invalid');

                    var data = {
                        'user-name': $('#user-name').val(),
                        'user-group': $('#user-group').val(),
                        pwd: $('#pwd').val(),
                        'c-pwd': $('#c-pwd').val(),
                        edit_user: "<?php echo $edit_user_name?>",
                        user_id: '<?php echo $user_id?>',
                        submit_check_user: $("button[type=submit]").val()
                    };

                    if ($('#staff-id').length > 0)
                        data['staff'] = $('#staff-id').val();

                    $.ajax({ 
                        url: '../../helpers/user.php',
                        data: data,
                        type: 'post',
                        success: function(output){
                            var json = $.parseJSON(output);
                            
                            if (json['error']){
                                $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;There are errors on the form. Please fix them before continuing.<button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>').insertBefore('#user-form');
                                $('#user-form').addClass('mt-4');
                                $.each(json['error'], function(key, value) {
                                    $('#' + key).addClass('red-box-shadow is-invalid');
                                    $('#' + key).parent().find('p').text(value);
                                });
                                $('html, body').animate({ scrollTop: 0 }, 0);
                            } else {
                                $('#user-form').unbind().submit();
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