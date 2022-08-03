<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    if (isset($_SESSION['login_rest_id']) && isset($_SESSION['login_user_id'])) {
        include_once '../../../../db_connect.php';
        include_once '../../helpers/user_group.php';
        include_once '../../helpers/user.php';

        $has_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'user/user_group', 'modify_permission');
   
        if ((int)$has_permission['has_permission']){
            $file_names = getPermissionFiles($connection);

            $file_names_2 = $file_names;
            $to_remove_file_names = ['customer/customer', 'dashboard/dashboard'];
            $temp_file_names = array_column($file_names_2, 'file_name');
            foreach ($to_remove_file_names as $name){
                $index = array_search($name, $temp_file_names);
                unset($file_names_2[$index]);
            }

            $edit_user_group = 'false';
            $current_user_group_name = '';
            if (isset($_GET['user_group_id'])) {
                $edit_user_group = 'true';
                $user_group = getUserGroup($connection, $_GET['user_group_id']);
                $current_user_group_name = $user_group['user_group_name'];
            }

            $user_groups = getUserGroups($connection, $_SESSION['login_rest_id']);
            $user_groups_name = array_column($user_groups, 'user_group_name');
        }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Group &VerticalLine; F&amp;B Corner</title>

    <!-- link -->
    <?php include_once '../../../../link.php'?>

    <?php if ((int)$has_permission['has_permission']){ ?>
        <style>
            .overflow{
                border: 1px solid black; 
                border-radius: 5px;
                background-color: rgb(243, 243, 243);
                overflow: auto;
                height: 200px;
                max-height: 200px;
            }
        </style>
    <?php } ?>
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
                <a href="user_group.php"><button type="button" class="btn btn-secondary float-end ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Cancel"><i class="fas fa-undo-alt font-size-20" style="line-height: 30px;"></i></button></a>
                <button type="submit" form="user-group-form" class="btn bg-primary bg-opacity-75 text-white float-end" data-bs-toggle="tooltip" data-bs-placement="top" title="Save"><i class="fas fa-save font-size-20" style="line-height: 30px;"></i></button>
                <h3 style="cursor: default; line-height: 45px;" class="m-0 ms-3 font-bernard font-size-35">
                    <?php if (isset($user_group)) { ?>
                        Edit User Group
                    <?php } else { ?>
                        Add User Group
                    <?php } ?>
                </h3>
            
                <div>
                    <?php if (isset($user_group)) { ?>
                        <form action="../../helpers/user_group.php?user_group_id=<?=$user_group['user_group_id']?>" class="m-5" id="user-group-form" method="POST">
                    <?php } else { ?>
                        <form action="../../helpers/user_group.php" class="m-5" id="user-group-form" method="POST">
                    <?php } ?>    
                        <div class="mb-4">
                            <input type="text" style="display: none;" name="submit_form_user_group">
                            <label for="user-group-name" class="form-label fw-bold">User Group Name <span class="required-star">*</span></label>
                            <?php if (isset($user_group)) { ?>
                                <input type="text" class="form-control" id="user-group-name" value="<?=$user_group['user_group_name']?>" name="user-group-name">
                            <?php } else { ?>
                                    <input type="text" class="form-control" id="user-group-name" name="user-group-name">
                            <?php } ?>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Access Permission</label>
                            <div class="p-3 overflow">
                                <?php foreach ($file_names as $file_name) { 
                                    if (isset($user_group)) {
                                        $access = json_decode($user_group['access_permission']);
                                    }
                                ?>
                                    <div class="mb-1">
                                        <?php 
                                            if (isset($user_group)) {
                                                if (in_array($file_name['permission_id'], $access)) { 
                                        ?>
                                            <input type="checkbox" id="checkbox-access-<?=$file_name['permission_id']?>" class="form-check-input checkbox-default me-2" value="<?=$file_name['permission_id']?>" name="access[]" checked="checked">
                                        <?php } else { ?>
                                            <input type="checkbox" id="checkbox-access-<?=$file_name['permission_id']?>" class="form-check-input checkbox-default me-2" value="<?=$file_name['permission_id']?>" name="access[]">
                                        <?php } 
                                            } else { 
                                        ?>
                                            <input type="checkbox" id="checkbox-access-<?=$file_name['permission_id']?>" class="form-check-input checkbox-default me-2" value="<?=$file_name['permission_id']?>" name="access[]">
                                        <?php } ?>
                                        <label class="form-check-label" for="checkbox-access-<?=$file_name['permission_id']?>"><?=$file_name['file_name']?></label>
                                    </div>
                                <?php } ?>
                            </div>
                            <span style="color: blue; cursor: default;"><small><span class="select-all" style="cursor: pointer;">Select All</span> / <span class="unselect-all" style="cursor: pointer;">Unselect All</span></small></span>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Modify Permission</label>
                            <div class="p-3 overflow">
                            <?php foreach ($file_names_2 as $file_name) { 
                                if (isset($user_group)) {
                                    $modify = json_decode($user_group['modify_permission']);
                                }
                            ?>
                                    <div class="mb-1">
                                        <?php 
                                            if (isset($user_group)) {
                                                if (in_array($file_name['permission_id'], $modify)) { 
                                        ?>
                                            <input type="checkbox" id="checkbox-modify-<?=$file_name['permission_id']?>" class="form-check-input checkbox-default me-2" value="<?=$file_name['permission_id']?>" name="modify[]" checked="checked">
                                        <?php } else { ?>
                                            <input type="checkbox" id="checkbox-modify-<?=$file_name['permission_id']?>" class="form-check-input checkbox-default me-2" value="<?=$file_name['permission_id']?>" name="modify[]">
                                        <?php } 
                                            } else {
                                        ?>
                                            <input type="checkbox" id="checkbox-modify-<?=$file_name['permission_id']?>" class="form-check-input checkbox-default me-2" value="<?=$file_name['permission_id']?>" name="modify[]">
                                        <?php } ?>
                                        <label class="form-check-label" for="checkbox-modify-<?=$file_name['permission_id']?>"><?=$file_name['file_name']?></label>
                                    </div>
                                <?php } ?>
                            </div>
                            <span style="color: blue; cursor: default;"><small><span class="select-all" style="cursor: pointer;">Select All</span> / <span class="unselect-all" style="cursor: pointer;">Unselect All</span></small></span>
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
                $('#user-group-form').removeClass('mt-4');
            }

            $(document).ready(function() {
                $('.select-all').on('click', function () {
                    $(this).parent().parent().parent().find('div .form-check-input').prop('checked', true);
                });

                $('.unselect-all').on('click', function () {
                    $(this).parent().parent().parent().find('div .form-check-input').prop('checked', false);
                });

                $("#user-group-form").submit(function(event){
                    event.preventDefault();

                    $('#user-group-form').removeClass('mt-4');
                    $('.alert-danger').remove();
                    $('.text-danger').text('');
                    $('#user-group-name').removeClass('red-box-shadow is-invalid');
                    var user_group_name = $.trim($('#user-group-name').val())
                    if (user_group_name == '' || user_group_name.length > 32){
                        $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;There are errors on the form. Please fix them before continuing.<button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>').insertBefore('#user-group-form');
                        $('#user-group-form').addClass('mt-4');
                        $('.text-danger').text('User group name must be between 1 and 32 characters!');
                        $('#user-group-name').addClass('red-box-shadow is-invalid');
                        $('html, body').animate({ scrollTop: 0 }, 0);
                    } else if (!<?=$edit_user_group?>){
                        var user_groups_name_array = <?=json_encode($user_groups_name)?>;
                        if (user_groups_name_array.indexOf(user_group_name) != -1){
                            $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;There are errors on the form. Please fix them before continuing.<button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>').insertBefore('#user-group-form');
                            $('fo#user-group-formrm').addClass('mt-4');
                            $('.text-danger').text('This name has already been used!');
                            $('#user-group-name').addClass('red-box-shadow is-invalid');
                            $('html, body').animate({ scrollTop: 0 }, 0);
                        }
                        else {
                            $('#user-group-form').unbind().submit();
                        }
                    } else if (<?=$edit_user_group?>){
                        var user_groups_name_array = <?=json_encode($user_groups_name)?>;
                        var index = user_groups_name_array.indexOf("<?=$current_user_group_name?>");
                        user_groups_name_array.splice(index, 1);
                        if (user_groups_name_array.indexOf(user_group_name) != -1){
                            $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;There are errors on the form. Please fix them before continuing.<button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>').insertBefore('#user-group-form');
                            $('#user-group-form').addClass('mt-4');
                            $('.text-danger').text('This name has already been used!');
                            $('#user-group-name').addClass('red-box-shadow is-invalid');
                            $('html, body').animate({ scrollTop: 0 }, 0);
                        }
                        else {
                            $('#user-group-form').unbind().submit();
                        }
                    } 
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