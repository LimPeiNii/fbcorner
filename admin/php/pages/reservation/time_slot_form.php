<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    if (isset($_SESSION['login_rest_id']) && isset($_SESSION['login_user_id'])) {
        include_once '../../../../db_connect.php';
        include_once '../../helpers/time_slot.php';
        include_once '../../helpers/table.php';
        include_once '../../helpers/user.php';

        $has_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'reservation/time_slot', 'modify_permission');

        if ((int)$has_permission['has_permission']){
            if (isset($_GET['time_slot_id'])) {
                $time_slot = getTimeSlot($connection, $_GET['time_slot_id']);
                $tables_json = json_decode($time_slot['tables'], true);
                $tables = getTables($connection, $_SESSION['login_rest_id']);
                foreach ($tables as $key => $table){
                    $tables[$key]['current_time_slot_status'] = $tables_json[$table['table_id']];
                }
            } else {
                header("Location: time_slot.php");
                exit;
            }
        }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time Slot &VerticalLine; F&amp;B Corner</title>

    <!-- link -->
    <?php include_once '../../../../link.php'?>

    <?php if ((int)$has_permission['has_permission']){ ?>
        <style>
            table{
                width: 100%;
            }

            thead, tbody, tfoot{
                border: 1px solid #ccc !important;
                border-width: 2px !important;
            }

            tbody tr{
                border-color: #dee2e6 !important;
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
                <a href="time_slot.php"><button type="button" class="btn btn-secondary float-end ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Cancel"><i class="fas fa-undo-alt font-size-20" style="line-height: 30px;"></i></button></a>
                <button type="submit" form="time-slot-form" class="btn bg-primary bg-opacity-75 text-white float-end" data-bs-toggle="tooltip" data-bs-placement="top" title="Save"><span class="spinner-border spinner-border-sm me-3 d-none" id="spinner" style="margin-bottom: 0.1rem;" role="status" aria-hidden="true"></span><i class="fas fa-save font-size-20" style="line-height: 30px;"></i></button>
                <h3 style="cursor: default; line-height: 45px;" class="m-0 ms-3 font-bernard font-size-35">
                    <?php if (isset($time_slot)) { ?>
                        Edit Time Slot
                    <?php } ?>
                </h3>
            
                <div>
                    <?php if (isset($_SESSION['success'])) { 
                        $success = $_SESSION['success'];
                        unset($_SESSION['success']);
                    ?>
                        <div class="alert form-message mt-4 mb-0 alert-success" role="alert"><i class="fas fa-check-circle"></i>&nbsp;&nbsp;<?=$success?><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>
                    <?php } ?>

                    <?php if (isset($time_slot)) { ?>
                        <form action="../../helpers/time_slot.php?time_slot_id=<?=$time_slot['time_slot_id']?>" class="m-5" id="time-slot-form" method="POST">
                    <?php } ?>    
                        <div class="mb-4">
                            <input type="text" style="display: none;" name="submit_form_time_slot">
                            <label for="start-time" class="form-label fw-bold">Start Time</label>
                            <?php if (isset($time_slot)) { ?>
                                <input type="text" class="form-control" id="start-time" value="<?=date('h:i a',strtotime($time_slot['start_time']))?>" name="start-time" disabled>
                            <?php } ?>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <label for="end-time" class="form-label fw-bold">End Time</label>
                            <?php if (isset($time_slot)) { ?>
                                <input type="text" class="form-control" id="end-time" value="<?=date('h:i a',strtotime($time_slot['end_time']))?>" name="end-time" disabled>
                            <?php } ?>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <label for="status" class="form-label fw-bold">Status</label>
                            <select class="form-select" id="status" name="status">
                                <?php if (isset($time_slot)) { 
                                    if ($time_slot['status'] == 0) { ?>
                                        <option value="1">Available</option>
                                        <option value="0" selected="selected">Unavailable</option>
                                <?php } else { ?>
                                        <option value="1" selected="selected">Available</option>
                                        <option value="0">Unavailable</option>                        
                                <?php } 
                                    } ?>
                            </select>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold d-block">Tables</label>
                            <div class="table-responsive">
                                <table class="table table-striped mt-1 align-middle" style="white-space: nowrap;">
                                <?php foreach ($tables as $table) { ?>
                                    <tr">
                                        <?php if ($table['status'] == 1) { ?>
                                            <td width="10%" class="text-end p-2">
                                                <label for="<?=$table['table_num_name']?>" class="form-label fw-bold"><?=$table['table_num_name']?></label>
                                            </td>
                                            <td width="90%" class="pe-2">
                                                <select class="form-select" style="min-width: 121.46px;" id="<?=$table['table_num_name']?>" name="table[<?=$table['table_id']?>]">
                                                    <?php if ($table['current_time_slot_status'] == 0) { ?>
                                                        <option value="1">Enabled</option>
                                                        <option value="0" selected="selected">Disabled</option>
                                                    <?php } else { ?>
                                                        <option value="1" selected="selected">Enabled</option>
                                                        <option value="0">Disabled</option>
                                                    <?php } ?>
                                                </select>
                                            </td>
                                        <?php } else { ?>
                                            <td width="20%" class="text-end p-2 align-middle">
                                                <label for="<?=$table['table_num_name']?>" class="form-label fw-bold"><?=$table['table_num_name']?></label>
                                            </td>
                                            <td width="80%" class="pe-2">
                                                <select class="form-select" id="<?=$table['table_num_name']?>" name="table[<?=$table['table_id']?>]" disabled>
                                                    <?php if ($table['current_time_slot_status'] == 0) { ?>
                                                        <option value="0" selected="selected">Disabled</option>
                                                    <?php } else { ?>
                                                        <option value="1" selected="selected">Enabled</option>
                                                    <?php } ?> 
                                                </select>
                                            </td>
                                        <?php } ?>
                                    </tr>
                                    <?php } ?>
                                </table>
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
                $('#time-slot-form').removeClass('mt-4');
            }


            $(document).ready(function() {
                $("#time-slot-form").submit(function(event){
                    event.preventDefault();
                    $('#spinner').removeClass('d-none');
                    $('#spinner').parent().prop('disabled', true);
                    $('select:disabled').prop("disabled", false);
                    $('#time-slot-form').unbind().submit();
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