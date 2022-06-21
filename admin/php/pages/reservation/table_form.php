<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    if (isset($_SESSION['login_rest_id']) && isset($_SESSION['login_user_id'])) {
        include_once '../../../../db_connect.php';
        include_once '../../helpers/table.php';
        include_once '../../helpers/user.php';

        $has_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'reservation/table', 'modify_permission');

        if ((int)$has_permission['has_permission']){
            if (isset($_GET['table_id'])) {
                $table = getTable($connection, $_GET['table_id']);
                $edit_table_num = $table['table_num_name'];
            } else {
                $edit_table_num = '';
            }
        }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Table &VerticalLine; F&amp;B Corner</title>

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
                <a href="table.php"><button type="button" class="btn btn-secondary float-end ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Cancel"><i class="fas fa-undo-alt font-size-20" style="line-height: 30px;"></i></button></a>
                <button type="submit" form="table-form" class="btn bg-primary bg-opacity-75 text-white float-end" data-bs-toggle="tooltip" data-bs-placement="top" title="Save"><span class="spinner-border spinner-border-sm me-3 d-none" id="spinner" style="margin-bottom: 0.1rem;" role="status" aria-hidden="true"></span><i class="fas fa-save font-size-20" style="line-height: 30px;"></i></button>
                <h3 style="cursor: default; line-height: 45px;" class="m-0 ms-3 font-bernard font-size-35">
                    <?php if (isset($table)) { ?>
                        Edit Table
                    <?php } else { ?>
                        Add Table
                    <?php } ?>
                </h3>
            
                <div>
                    <?php if (isset($_SESSION['success'])) { 
                        $success = $_SESSION['success'];
                        unset($_SESSION['success']);
                    ?>
                        <div class="alert form-message mt-4 mb-0 alert-success" role="alert"><i class="fas fa-check-circle"></i>&nbsp;&nbsp;<?=$success?><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>
                    <?php } ?>

                    <?php if (isset($table)) { ?>
                        <form action="../../helpers/table.php?table_id=<?=$table['table_id']?>" class="m-5" id="table-form" method="POST">
                    <?php } else { ?>
                        <form action="../../helpers/table.php" class="m-5" id="table-form" method="POST">
                    <?php } ?>    
                        <div class="mb-4">
                            <input type="text" style="display: none;" name="submit_form_table">
                            <label for="table-number-name" class="form-label fw-bold">Table Number/Name <span class="required-star">*</span></label>
                            <?php if (isset($table)) { ?>
                                <input type="text" class="form-control" id="table-number-name" value="<?=$table['table_num_name']?>" name="table-number-name">
                            <?php } else { ?>
                                    <input type="text" class="form-control" id="table-number-name" name="table-number-name">
                            <?php } ?>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <label for="capacity" class="form-label fw-bold">Capacity <span class="required-star">*</span></label>
                            <?php if (isset($table)) { ?>
                                <input type="number" class="form-control" id="capacity" value="<?=$table['capacity']?>" name="capacity">
                            <?php } else { ?>
                                <input type="number" class="form-control" id="capacity" value="1" name="capacity">
                            <?php } ?>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <label for="status" class="form-label fw-bold">Status</label>
                            <select class="form-select" id="status" name="status">
                                <?php if (isset($table)) { 
                                    if ($table['status'] == 0) { ?>
                                        <option value="1">Available</option>
                                        <option value="0" selected="selected">Unavailable</option>
                                <?php } else { ?>
                                        <option value="1" selected="selected">Available</option>
                                        <option value="0">Unavailable</option>                        
                                <?php } 
                                    } else { ?>
                                        <option value="1" selected="selected">Available</option>
                                        <option value="0">Unavailable</option> 
                                <?php } ?>
                            </select>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <label for="sort-order" class="form-label fw-bold">Sort Order</label>
                            <?php if (isset($table)) { ?>
                                <input type="number" class="form-control" id="sort-order" value="<?=$table['sort_order']?>" name="sort-order">
                            <?php } else { ?>
                                <input type="number" class="form-control" id="sort-order" value="0" name="sort-order">
                            <?php } ?>
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
                $('#table-form').removeClass('mt-4');
            }

            $(document).ready(function() {
                
                $("#table-form").submit(function(event){
                    event.preventDefault();

                    $('#table-form').removeClass('mt-4');
                    $('.form-message').remove();
                    $('.text-danger').text('');
                    $('input').each(function() {
                        $(this).removeClass('red-box-shadow is-invalid');
                    });
                    $('select').removeClass('red-box-shadow is-invalid');
                    $.ajax({ 
                        url: '../../helpers/table.php',
                        data: {
                            'table-number-name': $('#table-number-name').val(),
                            capacity: $('#capacity').val(),
                            status: $('#status').val(),
                            'sort-order': $('#sort-order').val(),
                            edit_table_num: "<?php echo $edit_table_num?>",
                            submit_check_table: $("button[type=submit]").val()
                        },
                        type: 'post',
                        success: function(output){
                            var json = $.parseJSON(output);
                            
                            if (json['error']){
                                $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;There are errors on the form. Please fix them before continuing.<button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>').insertBefore('#table-form');
                                $('#table-form').addClass('mt-4');
                                $.each(json['error'], function(key, value) {
                                    $('#' + key).addClass('red-box-shadow is-invalid');
                                    $('#' + key).parent().find('p').text(value);
                                });
                                $('html, body').animate({ scrollTop: 0 }, 0);
                            } else {
                                $('#spinner').removeClass('d-none');
                                $('#spinner').parent().prop('disabled', true);
                                $('#table-form').unbind().submit();
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