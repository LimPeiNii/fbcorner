<?php 

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    if (isset($_SESSION['login_rest_id']) && isset($_SESSION['login_user_id'])) {

        include_once '../../../../db_connect.php';
        include_once '../../helpers/time_slot.php';
        include_once '../../helpers/user.php';

        $has_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'reservation/time_slot', 'access_permission');
        $has_modify_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'reservation/time_slot', 'modify_permission');

        if ((int)$has_permission['has_permission']){
            $time_slots = getTimeSlots($connection, $_SESSION['login_rest_id']);

            $time_slot_setting = getTimeSlotSetting($connection, $_SESSION['login_rest_id']);
            if (count($time_slot_setting) > 0)
                $values = json_decode($time_slot_setting[0]['value'], true);
            else
                $values = [];
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

            .time-slot-list{
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
                <button type="button" onclick="delete_data();" class="btn bg-danger bg-opacity-75 float-end ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete"><span class="spinner-border spinner-border-sm me-3 text-white d-none" id="spinner" style="margin-bottom: 0.1rem;" role="status" aria-hidden="true"></span><i class="fas fa-trash-alt font-size-20 text-white m-0 p-0" style="line-height: 30px;"></i></button>
                <button type="button" id="btn-settings" class="btn btn-secondary float-end" data-bs-toggle="tooltip" data-bs-placement="top" title="Settings"><i class="fas fa-cog font-size-20 text-white m-0 p-0" style="line-height: 30px;"></i></button>
                <h3 style="cursor: default; line-height: 45px;" class="m-0 ms-3 font-bernard font-size-35">
                    Time Slot
                </h3>

                <?php if (isset($_SESSION['success'])) { 
                    $success = $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
                    <div class="alert form-message mt-4 mb-0 alert-success" role="alert"><i class="fas fa-check-circle"></i>&nbsp;&nbsp;<?=$success?><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button></div>
                <?php } ?>

                <div class="table-responsive time-slot-list">
                    <?php if (count($time_slots) == 0 || count($time_slots) == 1) { ?>
                    <table class="table table-bordered" style="white-space: nowrap;">
                    <?php } else { ?>
                    <table class="table table-bordered table-hover" style="white-space: nowrap;">
                    <?php } ?>
                        <thead>
                            <tr>
                                <th style="width: 20px;" class="px-4"><input type="checkbox" class="form-check-input checkbox-default" id="select-unselect-all"></td>
                                <th width="5%" class="px-4">No.</td>
                                <th class="px-3">Start Time</td>
                                <th class="px-3">End Time</td>
                                <th class="px-3">Status</td>
                                <th width="15%" class="text-center">Action</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $counter = 0;
                                foreach ($time_slots as $time_slot) { 
                                    $counter++;
                            ?>
                                <tr>
                                    <td style="width: 20px;" class="px-4 align-middle"><input type="checkbox" class="form-check-input checkbox-default data-checkbox" value="<?=$time_slot['time_slot_id']?>"></td>
                                    <td width="5%" class="text-center align-middle"><?=$counter?></td>
                                    <td class="align-middle px-3"><?=date('h:i a',strtotime($time_slot['start_time']))?></td>
                                    <td class="align-middle px-3"><?=date('h:i a',strtotime($time_slot['end_time']))?></td>
                                    <td class="align-middle px-3"><?=$time_slot['status'] ? 'Available' : 'Unavailable'?></td>
                                    <td width="15%" class="text-center">
                                        <button type="button" class="btn bg-success bg-opacity-75" onclick="viewDetails(<?=$time_slot['time_slot_id']?>);" data-bs-toggle="tooltip" data-bs-placement="top" title="View"><i class="fas fa-eye font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button>
                                        <a href="time_slot_form.php?time_slot_id=<?=$time_slot['time_slot_id']?>"><button type="button" class="btn bg-primary bg-opacity-75" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"><i class="fas fa-pen font-size-20 text-white m-0 p-0" style="line-height: 25px;"></i></button></a>
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

    <?php if ((int)$has_permission['has_permission']){ ?>
        <div class="modal fade p-0" id="settingsModal" data-bs-backdrop="static" tabindex="-1" role = "dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Time Slot Settings</h5>
                        <button type="button" class="btn-close close-settings" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="../../helpers/time_slot.php" method="POST" id="settings-form">
                            <div class="mb-3">
                                <input type="text" style="display: none;" name="submit_form_settings">
                                <label for="earliest-time" class="form-label">Earliest Reservation Time <span class="required-star">*</span></label>
                                <input type="time" class="form-control" id="earliest-time" name="earliest-time">
                                <p class="d-block text-danger"></p>
                            </div>
                            <div class="mb-3">
                                <label for="latest-time" class="form-label">Latest Reservation Time <span class="required-star">*</span></label>
                                <input type="time" class="form-control" id="latest-time" name="latest-time">
                                <p class="d-block text-danger"></p>
                            </div>
                            <div class="mb-3">
                                <label for="duration" class="form-label">Duration Per Time Slot <span class="required-star">*</span></label>
                                <div class="d-flex">
                                    <div class="d-inline-block d-flex w-50 me-2">
                                        <input id='h' name='h' type='number' min='0' max='24' class="form-control d-inline me-2" style="width: 90%;">
                                        <label for='h' class="align-self-center">h</label>
                                    </div>
                                    <div class="me-0 d-inline-block text-end d-flex w-50">
                                        <input id='m' name='m' type='number' min='0' max='59' class="form-control d-inline me-2" style="width: 90%;">
                                        <label for='m' class="align-self-center">m</label>
                                    </div>
                                </div>
                                <p class="d-block text-danger"></p>
                            </div>
                            <div class="mb-3">
                                <label for="maximum-time-slot" class="form-label">Maximum Selected Time Slot Allowed <span data-bs-toggle="tooltip" data-bs-placement="top" title="Restrict maximum number of time slot can be selected by customer. Default value will be 1"><i class="fas fa-question-circle"></i></span></label>
                                <input id="maximum-time-slot" name="maximum-time-slot" type='number' min='1' class="form-control" value="1">
                            </div>


                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary close-settings" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" form="settings-form">Save changes</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade p-0" id="detailsModal" data-bs-backdrop="static" tabindex="-1" role = "dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Time Slot Details</h5>
                        <button type="button" class="btn-close close-details-modal" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary close-details-modal" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

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
                        if (confirm("Are you sure you want to delete the time slot(s)?\n* Relevant reservations and pre-orders will be disabled")){
                            $.ajax({ 
                                url: '../../helpers/time_slot.php',
                                data: {selected_group_time_slot: selected_data},
                                type: 'post',
                                beforeSend: function(){
                                    $('#spinner').removeClass('d-none');
                                    $('#spinner').parent().prop('disabled', true);
                                },
                                success: function(){
                                    window.location.reload();
                                }
                            });
                        }
                    <?php } else { ?>
                        close_message();
                        $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;Sorry, you do not have the permission to delete the time slot(s).</div>').insertBefore('.time-slot-list');
                    <?php } ?>
                }
            }

            function viewDetails(id) {
                $.ajax({ 
                    url: '../../helpers/time_slot.php',
                    data: { id: id },
                    type: 'post',
                    success: function(output){
                        var json = $.parseJSON(output);

                        var html = "";

                        html += "<table class='table w-100 table-striped mt-1 align-middle'>";

                        for (i in json){
                            if (i != 'Tables'){
                                html += "<tr>";
                                html += "<td width='40%' class='px-3 fw-bold'>" + i + "</td>";
                                html += "<td width='60%' class='px-3 py-1'>" + json[i] + "</td>";
                                html += "</tr>";
                            }
                        }

                        html += "</table>";
    
                        // Displaying tables
                        html += "<br><table class='table w-100 mt-1 align-middle'>";
                        html += "<tr>";
                        html += "<th width='40%' class='px-3 fw-bold'>Tables</td>";
                        html += "<th width='60%' class='px-3 fw-bold'>Status</td>";
                        html += "</tr>";
                        for (i in json['Tables']){
                            html += "<tr>";
                            html += "<td width='40%' class='px-3 fw-bold'>" + i + "</td>";
                            html += "<td width='60%' class='px-3'>" + json['Tables'][i]['status_in_time_slot'] + (json['Tables'][i]['table_status'] ? " (Unavailable)" : '') + "</td>";
                            html += "</tr>";
                        }
                        html += "</table>";
                    
                        $("#detailsModal .modal-body").html(html);
                        $("#detailsModal .modal-title").text("Time Slot Details (#" + id + ")");
                        
                        $('.transition').removeClass('transition');
                        $('#detailsModal').modal('show');
                        $("nav, .side-bar, .content").addClass('blur');
                    }
                });
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

                $('#btn-settings').on('click', function() {
                    <?php if ((int)$has_modify_permission['has_permission']){ ?>
                        if (<?php echo count($time_slot_setting); ?> > 0){
                            <?php foreach ($values as $key => $value) { ?>
                                $('#' + '<?=$key?>').val("<?=$value?>");
                            <?php } ?>
                        }
                        $('.transition').removeClass('transition');
                        $('#settingsModal').modal('show');
                        $("nav, .side-bar, .content").addClass('blur');
                    <?php } else { ?>
                        close_message();
                        $('<div class="alert form-message mt-4 mb-0 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;Sorry, you do not have the permission to modify the time slot settings.</div>').insertBefore('.time-slot-list');
                    <?php } ?>
                });

                $('.close-settings').on('click', function() {
                    $("nav, .side-bar, .content").removeClass('blur');
                    $('.form-message').remove();
                    $('.text-danger').text('');
                    $('input').each(function() {
                        $(this).removeClass('red-box-shadow is-invalid');
                        $(this).val('');
                    });
                });

                $('.close-details-modal').click(function() {
                    $("nav, .side-bar, .content").removeClass('blur');
                });

                $("#settings-form").submit(function(event){
                    event.preventDefault();

                    $('.form-message').remove();
                    $('.text-danger').text('');
                    $('input').each(function() {
                        $(this).removeClass('red-box-shadow is-invalid');
                    });
                    $.ajax({ 
                        url: '../../helpers/time_slot.php',
                        data: {
                            'earliest-time': $('#earliest-time').val(),
                            'latest-time': $('#latest-time').val(),
                            h: $('#h').val(),
                            m: $('#m').val(),
                            submit_check_settings: $("button[type=submit]").val()
                        },
                        type: 'post',
                        success: function(output){
                            var json = $.parseJSON(output);
                            
                            if (json['error']){
                                $('<div class="alert form-message mt-0 mb-2 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;There are errors on the form. Please fix them before continuing.</div>').insertBefore('#settings-form');
                                $.each(json['error'], function(key, value) {
                                    $('#' + key).addClass('red-box-shadow is-invalid');
                                    $('#' + key).parent().find('p').text(value);
                                    if (key == 'h' || key == 'm')
                                        $('#' + key).parent().parent().parent().find('p').text(value);
                                });
                            } else {
                                if (<?php echo count($time_slot_setting); ?> > 0){
                                    if (confirm("Are you sure you want to reset the settings?\n* Reset time slot settings will remove all current time slot records and disable all reserved reservations.")){
                                        $('#settings-form').unbind().submit();
                                    } 
                                } else {
                                    $('#settings-form').unbind().submit();
                                }
                            }
                        }
                    });
                });

                $('.time-slot-list table').DataTable();
                $('table.dataTable').removeClass('dataTable');

                <?php if (count($time_slots) == 0) { ?>
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