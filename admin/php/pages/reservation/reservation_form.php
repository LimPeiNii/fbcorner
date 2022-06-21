<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    if (isset($_SESSION['login_rest_id']) && isset($_SESSION['login_user_id'])) {
        include_once '../../../../db_connect.php';
        include_once '../../helpers/reservation.php';
        include_once '../../helpers/time_slot.php';
        include_once '../../helpers/table.php';
        include_once '../../helpers/settings.php';
        include_once '../../helpers/user.php';
        include_once '../../helpers/order.php';
        include_once '../../../../store/php/helpers/customer.php';

        $has_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'reservation/reservation', 'modify_permission');

        if ((int)$has_permission['has_permission']){
            if (isset($_GET['reservation_id'])) {
                $reservation = getReservation($connection, $_GET['reservation_id']);
                $the_customer = getCustomer($connection, $reservation['cus_id']);
                $table = getTable($connection, $reservation['table_id']);
                $the_preorder = getOrdersByReservationID($connection, $_GET['reservation_id'], ['not_statuses' => ['Cancelled', 'Disabled']]);
            }

            $customers = getCustomers($connection, ['order_by' => 'firstname', 'asc_desc' => 'ASC']);
            $time_slots = getTimeSlots($connection, $_SESSION['login_rest_id']);
            $time_slot_setting = getSetting($connection, $_SESSION['login_rest_id'], 'time_slot');
            if (empty($time_slot_setting)){
                $time_slot_setting['maximum-time-slot'] = 1;
            }
        }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation &VerticalLine; F&amp;B Corner</title>

    <!-- link -->
    <?php include_once '../../../../link.php'?>

    <?php if ((int)$has_permission['has_permission']){ ?>
        <style>
            table{
                width: 100%;
            }

            textarea:disabled{
                border: 0px solid black !important;
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
                <a href="reservation.php"><button type="button" class="btn btn-secondary float-end ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Cancel"><i class="fas fa-undo-alt font-size-20" style="line-height: 30px;"></i></button></a>
                <button type="submit" form="reservation-form" class="btn bg-primary bg-opacity-75 text-white float-end" data-bs-toggle="tooltip" data-bs-placement="top" title="Save"><span class="spinner-border spinner-border-sm me-3 d-none" id="spinner" style="margin-bottom: 0.1rem;" role="status" aria-hidden="true"></span><i class="fas fa-save font-size-20" style="line-height: 30px;"></i></button>
                <h3 style="cursor: default; line-height: 45px;" class="m-0 ms-3 font-bernard font-size-35">
                    <?php if (isset($reservation)) { ?>
                        Edit Reservation
                    <?php } else { ?>
                        Add Reservation
                    <?php } ?>
                </h3>
            
                <div>
                    <?php if (isset($_SESSION['success'])) { 
                        $success = $_SESSION['success'];
                        unset($_SESSION['success']);
                    ?>
                        <div class="alert form-message mt-4 mb-0 alert-success" role="alert"><i class="fas fa-check-circle"></i>&nbsp;&nbsp;<?=$success?><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>
                    <?php } ?>

                    <?php if (isset($reservation)) { ?>
                        <form action="../../helpers/reservation.php?reservation_id=<?=$reservation['reservation_id']?>" class="m-5" id="reservation-form" method="POST">
                    <?php } else { ?>    
                        <form action="../../helpers/reservation.php" class="m-5" id="reservation-form" method="POST">
                    <?php } ?>    
                        <div class="mb-4">
                            <input type="text" style="display: none;" name="submit_form_reservation">
                            <?php if (isset($reservation)) { ?>
                                <input type="text" style="display: none;" name="cus-id" id="cus_id" value="<?=$reservation['cus_id']?>">
                            <?php } else { ?>
                                <input type="text" style="display: none;" name="cus-id" id="cus_id">
                            <?php } ?>
                            <label for="cus-name" class="form-label fw-bold">Customer Name <span class="required-star">*</span></label>
                            <?php if (isset($reservation)) { ?>
                                <input class="form-control" autocomplete="off" list="datalistOptions" id="cus-name" onchange="addCusID(value);" value="<?=$the_customer['firstname'] . ' ' . $the_customer['lastname']?>" disabled>
                            <?php } else { ?>
                                <input class="form-control" autocomplete="off" list="datalistOptions" id="cus-name" onchange="addCusID(value);">
                            <?php } ?>
                            <datalist id="datalistOptions">
                                <?php foreach ($customers as $customer) { ?>
                                    <option data-value="<?=$customer['firstname'] . ' ' . $customer['lastname']?>,<?=$customer['cus_id']?>,<?=$customer['email']?>,<?=$customer['contact_num']?>" value="<?=trim($customer['firstname'] . ' ' . $customer['lastname']) . ' (' . $customer['email'] . ')'?>">
                                <?php } ?>
                            </datalist>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <label for="email" class="form-label fw-bold">Email</label>
                            <?php if (isset($reservation)) { ?>
                                <input type="text" class="form-control" id="email" value="<?=$the_customer['email']?>" disabled>
                            <?php } else { ?>
                                <input type="text" class="form-control" id="email" disabled>
                            <?php } ?>
                        </div>
                        <div class="mb-4">
                            <label for="contact-num" class="form-label fw-bold">Contact Number</label>
                            <?php if (isset($reservation)) { ?>
                                <input type="text" class="form-control" id="contact-num" value="<?=$the_customer['contact_num']?>" disabled>
                            <?php } else { ?>
                                <input type="text" class="form-control" id="contact-num" disabled>
                            <?php } ?>
                        </div>
                        <div class="mb-4">
                            <label for="date" class="form-label fw-bold">Date <span class="required-star">*</span></label>
                            <?php if (isset($reservation)) { ?>
                                <input type="date" class="form-control" onchange="updateTableDropdown();" value="<?=$reservation['date']?>" id="date" name="date">
                            <?php } else { ?>
                                <input type="date" class="form-control" onchange="updateTableDropdown();" id="date" name="date">
                            <?php } ?>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <label for="time-start" class="form-label fw-bold">Time <span class="required-star">*</span></label>
                            <select class="form-select" onchange="updateTableDropdown();" multiple aria-label="multiple" id="time" name="time[]">
                                <?php
                                    if (isset($reservation)){
                                        $selected_time_slots = json_decode($reservation['time_slot_id'], true);
                                    }
                                ?>
                                <?php foreach ($time_slots as $time_slot) { ?>
                                <?php if (isset($reservation) && in_array($time_slot['time_slot_id'], $selected_time_slots)) { ?>
                                    <?php $selected_time_slots_status[$time_slot['time_slot_id']] = $time_slot['status']; ?>
                                    <option value="<?=$time_slot['time_slot_id']?>" selected="selected"<?=$time_slot['status'] == '0' ? ' disabled' : '' ?>><?=date('h:i a', strtotime($time_slot['start_time']))?> to <?=date('h:i a', strtotime($time_slot['end_time']))?></option>
                                <?php } elseif ($time_slot['status'] == '0') { ?>
                                    <option value="<?=$time_slot['time_slot_id']?>" disabled><?=date('h:i a', strtotime($time_slot['start_time']))?> to <?=date('h:i a', strtotime($time_slot['end_time']))?></option>                
                                <?php } else { ?>
                                    <option value="<?=$time_slot['time_slot_id']?>"><?=date('h:i a', strtotime($time_slot['start_time']))?> to <?=date('h:i a', strtotime($time_slot['end_time']))?></option>                
                                <?php } 
                                    } ?>
                            </select>
                            <p class="d-block text-danger time-error"></p>
                        </div>
                        <div class="mb-4">
                            <label for="table-num-name" class="form-label fw-bold">Table Number (Name) <span class="required-star">*</span></label>
                            <select class="form-select" id="table-num-name" name="table-num-name">
                                <?php if (isset($reservation) && !empty($table) && $table['status'] == '1') { ?>
                                    <option value="<?=$table['table_id']?>" selected="selected"><?=$table['table_num_name']?></option>
                                <?php } ?>
                            </select>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <label for="special-request" class="form-label fw-bold">Special Request</label>
                            <?php if (isset($reservation)) { ?>
                                <textarea class="form-control" id="special-request" name="special-request" rows="6"><?=$reservation['additional_notes']?></textarea>
                            <?php } else { ?>
                                <textarea class="form-control" id="special-request" name="special-request" rows="6"></textarea>                    
                            <?php } ?>
                            <small><div class="char-counter" style="color: grey;"></div></small>
                        </div>
                        <div class="mb-4">
                            <label for="status" class="form-label fw-bold">Status</label>
                            <select class="form-select" id="status" name="status">
                                <?php if (isset($reservation)) { ?>
                                    <?php if ($reservation['status'] == '1') { ?>
                                        <option value="1" selected="selected">Reserved</option>
                                        <?php if (isset($the_preorder) && !$the_preorder) { ?>
                                            <option value="3">Completed</option>
                                        <?php } ?>
                                        <option value="0">Cancelled</option>
                                    <?php } else { ?>
                                        <option value="1">Reserved</option>
                                        <option value="0" selected="selected">Cancelled</option>
                                    <?php } ?>
                                <?php } else { ?>
                                    <option value="1" selected="selected">Reserved</option>
                                    <option value="3">Completed</option>
                                    <option value="0">Cancelled</option>
                                <?php } ?>
                            </select>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <label for="ongoing" class="form-label fw-bold">Ongoing</label>
                            <select class="form-select" id="ongoing" name="ongoing"<?=isset($reservation) && $reservation['status'] != 1 ? ' disabled' : ''?>>
                                <?php if (isset($reservation)) { ?>
                                    <?php if ($reservation['status'] == 1 && $reservation['remarks'] == 'Ongoing') { ?>
                                        <option value="1" selected>Yes</option>
                                        <option value="0">No</option>
                                    <?php } else { ?>
                                        <option value="1">Yes</option>
                                        <option value="0" selected>No</option>
                                    <?php } ?>
                                <?php } else { ?>
                                    <option value="1">Yes</option>
                                    <option value="0" selected>No</option>
                                <?php } ?>
                            </select>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mb-4">
                            <label for="remarks" class="form-label fw-bold">Remarks</label>
                            <?php if (isset($reservation)) { ?>
                                <textarea class="form-control" name="remarks" id="remarks" rows="4"><?=$reservation['remarks']?></textarea>
                            <?php } else { ?>
                                <textarea class="form-control" name="remarks" id="remarks" rows="4"></textarea>                    
                            <?php } ?>
                            <small><div class="char-counter" style="color: grey;"></div></small>
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
                $('#reservation-form').removeClass('mt-4');
            }

            function addCusID(name){
                $('#cus_id').val('');
                $('#email').val('');
                $('#contact-num').val('');

                if ($('#datalistOptions [value="' + name + '"]').length != 0){
                    var data = $('#datalistOptions [value="' + name + '"]').data('value');
                    var data_array = data.split(',');
                    $('#cus-name').val(jQuery.trim(data_array[0]));
                    $('#cus_id').val(data_array[1]);
                    $('#email').val(data_array[2]);
                    $('#contact-num').val(data_array[3]);
                }
            }

            function updateTableDropdown(){
                $('#time').removeClass('red-box-shadow is-invalid');
                $('#time').parent().find('p').text('');

                if ($('#time').val().length > <?=$time_slot_setting['maximum-time-slot']?>){
                    $('#time').addClass('red-box-shadow is-invalid');
                    $('#time').parent().find('p').text('You can only select maximum ' + '<?=$time_slot_setting['maximum-time-slot']?>' + ' time slots')
                    $('#table-num-name').children().remove();
                } else {
                    var selected_time_slots = $('#time').val();
                    for (var i = 1; i <= selected_time_slots.length-1; i++){
                        if (parseInt(selected_time_slots[i]) - parseInt(selected_time_slots[i-1]) > 1){
                            //error
                            $('#time').parent().find('p').text('You can only select consecutive time');
                            $('#time').addClass('red-box-shadow is-invalid');
                            $('#table-num-name').children().remove();
                            return;
                        }
                    }

                    if ($('#date').val() != '' && $('#time').val().length !== 0){
                        $.ajax({
                            url: '../../helpers/reservation.php',
                            data: {
                                date: $('#date').val(),
                                time: $('#time').val(),
                                edit_reservation_id: '<?=isset($reservation) ? $reservation['reservation_id'] : '' ?>',
                                get_available_tables: true
                            },
                            method: 'post',
                            success: function(output) {
                                var json = $.parseJSON(output);

                                var selected_table = $('#table-num-name').val();
                                $('#table-num-name').children().remove();
                                $('#table-num-name').append('<option value="0">-- Please Select --</option>');

                                for (i in json){
                                    if (selected_table == json[i]['table_id']){
                                        $('#table-num-name').append('<option value="' + json[i]['table_id'] + '" selected>' + json[i]['table_num_name'] + ' (table size: ' + json[i]['capacity'] + ')</option>');
                                    } else {
                                        $('#table-num-name').append('<option value="' + json[i]['table_id'] + '">' + json[i]['table_num_name'] + ' (table size: ' + json[i]['capacity'] + ')</option>');
                                    }
                                }
                            }
                        });
                    }
                }
            }

            $(document).ready(function() {
                $("#reservation-form").submit(function(event){
                    event.preventDefault();

                    $('.form-message').remove();
                    $('input, select').removeClass('red-box-shadow is-invalid');
                    $('.text-danger').text('');

                    $.ajax({
                        url: '../../helpers/reservation.php',
                        data: {
                            cus_id: $('#cus_id').val(),
                            cus_name: $('#cus-name').val(),
                            date: $('#date').val(),
                            time: $('#time').val().length == 0 ? '[]' : $('#time').val(),
                            table_id: $('#table-num-name').val(),
                            ongoing: $('#ongoing').val(),
                            edit_reservation_id: '<?=isset($reservation) ? $reservation['reservation_id'] : '' ?>',
                            submit_check_reservation: $("button[type=submit]").val()
                        },
                        method: 'post',
                        success: function(output) {
                            var json = $.parseJSON(output);
                            
                            if (json['error']){
                                $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;There are errors on the form. Please fix them before continuing.<button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>').insertBefore('#reservation-form');
                                $('#reservation-form').addClass('mt-4');
                                $.each(json['error'], function(key, value) {
                                    $('#' + key).addClass('red-box-shadow is-invalid');
                                    $('#' + key).parent().find('p').text(value);
                                });
                                $('html, body').animate({ scrollTop: 0 }, 0);
                            } else if (json['warning']){
                                $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;' + json['warning'] + '<button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>').insertBefore('#reservation-form');
                                $('#reservation-form').addClass('mt-4');
                                $('html, body').animate({ scrollTop: 0 }, 0);
                            } else {
                                $('#spinner').removeClass('d-none');
                                $('#spinner').parent().prop('disabled', true);
                                $('#reservation-form').unbind().submit();
                            }
                        }
                    });
                });

                <?php if (isset($reservation)) { ?>
                    //if one of the selected time slot is disabled, need to empty time slot and table for reselect
                    <?php if (isset($selected_time_slots_status) && in_array(0, $selected_time_slots_status)) { ?>
                        $('#time').val('');
                        $('#table-num-name').children().remove();
                    <?php } ?>
                    
                    //if one of the selected time slot is deleted OR if all time slots are reset
                    if ($('#time').val().length < <?=count($selected_time_slots)?>){
                        $('#time').val('');
                        $('#table-num-name').children().remove();
                    }

                    updateTableDropdown();
                <?php } ?>

                $('#status').change(function() {
                    if ($('#status').val() == '0'){
                        $('#remarks').prop('disabled', false);
                        $('#remarks').parent().find('.char-counter').show();
                    } else {
                        $('#remarks').prop('disabled', true);
                        $('textarea[name="remarks"]').val('');
                        $('textarea[name="remarks"]').trigger('input');
                        $('#remarks').parent().find('.char-counter').hide();
                    }

                    if ($(this).val() == '1'){
                        $('#ongoing').prop('disabled', false);
                        if ($('#ongoing').val() == '1'){
                            $('#remarks').val('Ongoing');
                        }
                    } else {
                        $('#ongoing').prop('disabled', true);
                        $('#remarks').val('');
                        $('#ongoing').val('0');
                    }
                });

                $('#ongoing').change(function() {
                    if ($(this).val() == '1' && $('#status').val() == '1'){
                        $('#remarks').val('Ongoing');
                    } else {
                        $('#remarks').val('');
                    }
                })

                $('#status').trigger('change');

                $('textarea').each(function() {
                    var limit = 1000;
                    var current_char = $(this).val().length;
                    $(this).parent().find('.char-counter').text((limit - current_char).toString() + ' characters');                
                });

                $('textarea').on('input', function(){
                    var current_char = $(this).val().length;
                    var limit = 1000;
                    if (current_char > limit) {
                        $(this).val($(this).val().substring(0, limit));
                    }
                    var char_left = limit - current_char;
                    if (char_left < 0)
                        char_left = 0;
                    $(this).parent().find('.char-counter').text(char_left.toString() + ' characters');
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