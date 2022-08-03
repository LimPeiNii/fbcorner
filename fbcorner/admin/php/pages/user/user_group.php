<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    if (isset($_SESSION['login_rest_id']) && isset($_SESSION['login_user_id'])) {

        include_once '../../../../db_connect.php';
        include_once '../../helpers/user_group.php';
        include_once '../../helpers/user.php';

        $has_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'user/user_group', 'access_permission');
        $has_modify_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'user/user_group', 'modify_permission');
   
        if ((int)$has_permission['has_permission']){
            $user_groups = getUserGroups($connection, $_SESSION['login_rest_id'], ['order_by' => 'user_group_name', 'asc_desc' => 'ASC']);
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
            input[type=checkbox]{
                transform: scale(1.2);
            }

            table{
                border: 1px solid black !important;
            }

            tr, th{
                border-color: #dee2e6 !important;
            }
            
            thead, tbody{
                border-width: 2px;
            }

            th{
                background-color: rgb(235, 235, 235) !important;
            }

            .user-group-list{
                margin-top: 15px;
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
                <button type="button" onclick="delete_data();" class="btn bg-danger bg-opacity-75 float-end ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete"><i class="fas fa-trash-alt font-size-20 text-white m-0 p-0" style="line-height: 30px;"></i></button>
                <a href="user_group_form.php"><button type="button" class="btn btn-secondary float-end" data-bs-toggle="tooltip" data-bs-placement="top" title="Add"><i class="fas fa-plus font-size-20 text-white m-0 p-0" style="line-height: 30px;"></i></button></a>
                <h3 style="cursor: default; line-height: 45px;" class="m-0 ms-3 font-bernard font-size-35">
                    User Group
                </h3>

                <?php if (isset($_SESSION['success'])) { 
                    $success = $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
                    <div class="alert form-message mt-4 mb-0 alert-success" role="alert"><i class="fas fa-check-circle"></i>&nbsp;&nbsp;<?=$success?><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>
                <?php } ?>

                <div class="table-responsive user-group-list">
                    <?php if (count($user_groups) == 0 || count($user_groups) == 1) { ?>
                    <table class="table table-bordered" style="white-space: nowrap;">
                    <?php } else { ?>
                    <table class="table table-bordered table-hover" style="white-space: nowrap;">
                    <?php } ?>
                        <thead>
                            <tr>
                                <th style="width: 20px;" class="px-4"><input type="checkbox" class="form-check-input checkbox-default" id="select-unselect-all"></td>
                                <th width="5%" class="px-4">No.</td>
                                <th class="px-3">User Group</td>
                                <th width="10%" class="text-center">Action</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $counter = 0;
                                foreach ($user_groups as $user_group) { 
                                    $counter++;
                            ?>
                                <tr>
                                    <td style="width: 20px;" class="px-4 align-middle"><input type="checkbox" class="form-check-input checkbox-default data-checkbox" value="<?=$user_group['user_group_id']?>"></td>
                                    <td width="5%" class="text-center align-middle"><?=$counter?></td>
                                    <td class="align-middle px-3"><?=$user_group['user_group_name']?></td>
                                    <td width="10%" class="text-center">
                                        <a href="user_group_form.php?user_group_id=<?=$user_group['user_group_id']?>"><button type="button" class="btn bg-primary bg-opacity-75" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"><i class="fas fa-pen font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button></a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
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
            }

            function delete_data(){
                var selected_data = [];
                $('.data-checkbox:checked').each(function() {
                    selected_data.push($(this).val());
                });

                if (selected_data.length > 0){
                    <?php if ((int)$has_modify_permission['has_permission']){ ?>
                        if (confirm("Are you sure you want to delete the user group(s)?\n* Relevant user(s) will also be deleted")){
                            $.ajax({ 
                                url: '../../helpers/user_group.php',
                                data: {selected_group: selected_data},
                                type: 'post',
                                success: function(){
                                    window.location.reload();
                                }
                            });
                        }
                    <?php } else { ?>
                        close_message();
                        $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;Sorry, you do not have the permission to delete the user group(s).</div>').insertBefore('.user-group-list');
                    <?php } ?>
                }
            }

            $(document).ready(function() {
                
                $('#select-unselect-all').on('click', function () {
                    if ($(this).is(':checked')){
                        $('.content').find('.data-checkbox').prop('checked', true);
                    } else {
                        $('.content').find('.data-checkbox').prop('checked', false);
                    }
                });

                $('.data-checkbox').click(function() {
                    if ($('#select-unselect-all').is(':checked')){
                        $('#select-unselect-all').prop('checked', false);
                    }
                });

                $('.user-group-list table').DataTable();
                $('table.dataTable').removeClass('dataTable');

                <?php if (count($user_groups) == 0) { ?>
                    $('.dataTables_empty').addClass('text-center');
                    $('.dataTables_empty').text('No results !');
                <?php } ?>
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