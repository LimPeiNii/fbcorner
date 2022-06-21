<?php 

    date_default_timezone_set("Asia/Kuala_Lumpur");

    session_start();

    include_once '../../../../session.php';
    updateLastActivity();

    if (isset($_SESSION['login_rest_id']) && isset($_SESSION['login_user_id'])) {

        include_once '../../../../db_connect.php';
        include_once '../../helpers/reservation.php';
        include_once '../../helpers/customer.php';
        include_once '../../helpers/user.php';

        $has_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'calendar/calendar', 'access_permission');
        $has_modify_permission = checkUserPermission($connection, $_SESSION['login_user_id'], 'calendar/calendar', 'modify_permission');

        if (!empty($_SESSION['success'])){
            $success = $_SESSION['success'];
            unset($_SESSION['success']);
        }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar &VerticalLine; F&amp;B Corner</title>

    <!-- link -->
    <?php include_once '../../../../link.php'?>

    <?php if ((int)$has_permission['has_permission']){ ?>
        <style>
            .side-bar .dropdown-container.active, .side-bar ul li:hover .tool-tip{
                z-index: 2 !important;
            }

            .fc-daygrid-event{
                cursor: pointer;
            }

            .fc-header-toolbar{
                flex-wrap: wrap !important;
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
        <div class="content p-4 pt-0">
            <?php if ((int)$has_permission['has_permission']){ ?>
                <div class="my-4">
                    <div id="event-calendar" class="w-100"></div>
                </div>
            <?php } else { ?>
                <div class="text-center mt-3 pt-4">
                    <h4 class="mx-4">Sorry, you do not have the permission to access this page.</h4>
                </div>
            <?php } ?>
        </div>
    </div>

    <?php if ((int)$has_permission['has_permission']){ ?>
        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasViewEvent" aria-labelledby="offcanvasViewEventLabel">
            <div class="offcanvas-header">
                <h5 id="offcanvasViewEventLabel">Event</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body pt-0">
                
            </div>
        </div>

        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddEvent" aria-labelledby="offcanvasAddEventLabel">
            <div class="offcanvas-header">
                <h5 id="offcanvasAddEventLabel">Add Event</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body pt-0">
                <form action="../../helpers/calendar.php" method="post">
                    <div class="mb-3 d-flex">
                        <input type="text" style="display: none;" name="submit_add_event">
                        <div class="form-check align-self-center">
                            <input class="form-check-input" type="checkbox" value="closed" id="close-restaurant" name="close-restaurant">
                            <label class="form-check-label" for="close-restaurant">
                                Close Restaurant
                            </label>
                        </div>
                        <button type="submit" class="btn bg-primary bg-opacity-75 ms-auto" data-bs-toggle="tooltip" data-bs-placement="top" title="Save"><i class="fas fa-save font-size-20 text-white m-0 p-0" style="line-height: 30px;"></i></button>
                    </div>
                    <div class="mb-4">
                        <label for="title" class="form-label fw-bold">Title <span class="required-star">*</span></label>
                        <input type="text" class="form-control" id="title" name="title">
                        <p class="d-block text-danger"></p>
                    </div>
                    <div class="mb-4 form-check form-switch ps-0">
                        <label class="form-check-label fw-bold" for="all-day">All Day</label>
                        <input class="form-check-input float-end" type="checkbox" role="switch" id="all-day" name="all-day">
                        <div class="mt-2" id="not-repeat-start-end-datetime-wrapper">
                            <div class="d-flex">
                                <label for="start-datetime" class="form-label fw-bold align-self-center mb-0 me-2" style="width: fit-content">Start&nbsp;<span class="required-star">*</span></label>
                                <input type="datetime-local" class="form-control" name="start-datetime" id="start-datetime">
                            </div>
                            <div class="d-flex mt-1">
                                <label for="end-datetime" class="form-label fw-bold align-self-center mb-0 me-3" style="width: fit-content">End&nbsp;<span class="required-star">*</span></label>
                                <input type="datetime-local" class="form-control" name="end-datetime" id="end-datetime">
                            </div>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mt-2 d-none" id="repeat-start-end-datetime-wrapper">
                            <div class="d-flex">
                                <label for="start-date" class="form-label fw-bold align-self-center mb-0 me-2" style="width: fit-content; white-space: nowrap;">Start Date</label>
                                <input type="date" class="form-control" name="start-date" id="start-date">
                            </div>
                            <div class="d-flex mt-1">
                                <label for="end-date" class="form-label fw-bold align-self-center mb-0 me-3" style="width: fit-content; white-space: nowrap;">End Date</label>
                                <input type="date" class="form-control" name="end-date" id="end-date">
                            </div>
                            <div class="d-flex mt-1">
                                <label for="start-time" class="form-label fw-bold align-self-center mb-0 me-2" style="width: fit-content; white-space: nowrap;">Start Time&nbsp;<span class="required-star">*</span></label>
                                <input type="time" class="form-control" name="start-time" id="start-time">
                            </div>
                            <div class="d-flex mt-1">
                                <label for="end-time" class="form-label fw-bold align-self-center mb-0 me-3" style="width: fit-content; white-space: nowrap;">End Time&nbsp;<span class="required-star">*</span></label>
                                <input type="time" class="form-control" name="end-time" id="end-time">
                            </div>
                            <p class="d-block text-danger"></p>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="form-check form-switch ps-0 mb-2">
                            <label class="form-check-label fw-bold" for="repeat">Repeat</label>
                            <input class="form-check-input float-end" type="checkbox" role="switch" id="repeat" name="repeat">
                        </div>
                        <div class="d-none" id="repeat-day-wrapper">
                            <?php $week_day = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']; ?>
                            <?php foreach ($week_day as $key => $day) { ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="repeat-day[]" id="repeat-<?=$key?>" value="<?=$key?>">
                                    <label class="form-check-label" for="repeat-<?=$key?>">
                                        <?=$day?>
                                    </label>
                                </div>
                            <?php } ?>
                        </div>
                        <p class="d-block text-danger"></p>
                    </div>
                    <div>
                        <label for="event-color" class="form-label fw-bold">Event Color</label>
                        <input type="color" class="form-control form-control-color" id="event-color" value="#563d7c" name="event-color">
                    </div>
                </form>
            </div>
        </div>

        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEditEvent" aria-labelledby="offcanvasEditEventLabel">
            <div class="offcanvas-header">
                <h5 id="offcanvasEditEventLabel">Edit Event</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body pt-0">
                <form action="../../helpers/calendar.php" method="post">
                    <div class="mb-3 d-flex">
                        <input type="text" style="display: none;" name="submit_edit_event">
                        <input type="text" style="display: none;" name="event_id" id="event_id">
                        <div class="form-check align-self-center">
                            <input class="form-check-input" type="checkbox" value="closed" id="edit-close-restaurant" name="edit-close-restaurant">
                            <label class="form-check-label" for="edit-close-restaurant">
                                Close Restaurant
                            </label>
                        </div>
                        <button type="submit" class="btn bg-primary bg-opacity-75 ms-auto" data-bs-toggle="tooltip" data-bs-placement="top" title="Save"><i class="fas fa-save font-size-20 text-white m-0 p-0" style="line-height: 30px;"></i></button>
                    </div>
                    <div class="mb-4">
                        <label for="edit-title" class="form-label fw-bold">Title <span class="required-star">*</span></label>
                        <input type="text" class="form-control" id="edit-title" name="edit-title">
                        <p class="d-block text-danger"></p>
                    </div>
                    <div class="mb-4 form-check form-switch ps-0">
                        <label class="form-check-label fw-bold" for="edit-all-day">All Day</label>
                        <input class="form-check-input float-end" type="checkbox" role="switch" id="edit-all-day" name="edit-all-day">
                        <div class="mt-2" id="not-edit-repeat-start-end-datetime-wrapper">
                            <div class="d-flex">
                                <label for="edit-start-datetime" class="form-label fw-bold align-self-center mb-0 me-2" style="width: fit-content">Start&nbsp;<span class="required-star">*</span></label>
                                <input type="datetime-local" class="form-control" name="edit-start-datetime" id="edit-start-datetime">
                            </div>
                            <div class="d-flex mt-1">
                                <label for="edit-end-datetime" class="form-label fw-bold align-self-center mb-0 me-3" style="width: fit-content">End&nbsp;<span class="required-star">*</span></label>
                                <input type="datetime-local" class="form-control" name="edit-end-datetime" id="edit-end-datetime">
                            </div>
                            <p class="d-block text-danger"></p>
                        </div>
                        <div class="mt-2 d-none" id="edit-repeat-start-end-datetime-wrapper">
                            <div class="d-flex">
                                <label for="edit-start-date" class="form-label fw-bold align-self-center mb-0 me-2" style="width: fit-content; white-space: nowrap;">Start Date</label>
                                <input type="date" class="form-control" name="edit-start-date" id="edit-start-date">
                            </div>
                            <div class="d-flex mt-1">
                                <label for="edit-end-date" class="form-label fw-bold align-self-center mb-0 me-3" style="width: fit-content; white-space: nowrap;">End Date</label>
                                <input type="date" class="form-control" name="edit-end-date" id="edit-end-date">
                            </div>
                            <div class="d-flex mt-1">
                                <label for="edit-start-time" class="form-label fw-bold align-self-center mb-0 me-2" style="width: fit-content; white-space: nowrap;">Start Time&nbsp;<span class="required-star">*</span></label>
                                <input type="time" class="form-control" name="edit-start-time" id="edit-start-time">
                            </div>
                            <div class="d-flex mt-1">
                                <label for="edit-end-time" class="form-label fw-bold align-self-center mb-0 me-3" style="width: fit-content; white-space: nowrap;">End Time&nbsp;<span class="required-star">*</span></label>
                                <input type="time" class="form-control" name="edit-end-time" id="edit-end-time">
                            </div>
                            <p class="d-block text-danger"></p>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="form-check form-switch ps-0 mb-2">
                            <label class="form-check-label fw-bold" for="edit-repeat">Repeat</label>
                            <input class="form-check-input float-end" type="checkbox" role="switch" id="edit-repeat" name="edit-repeat">
                        </div>
                        <div class="d-none" id="edit-repeat-day-wrapper">
                            <?php foreach ($week_day as $key => $day) { ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="edit-repeat-day[]" id="edit-repeat-<?=$key?>" value="<?=$key?>">
                                    <label class="form-check-label" for="edit-repeat-<?=$key?>">
                                        <?=$day?>
                                    </label>
                                </div>
                            <?php } ?>
                        </div>
                        <p class="d-block text-danger"></p>
                    </div>
                    <div>
                        <label for="edit-event-color" class="form-label fw-bold">Event Color</label>
                        <input type="color" class="form-control form-control-color" id="edit-event-color" value="#563d7c" name="edit-event-color">
                    </div>
                </form>
            </div>
        </div>
    <?php } ?>

    <!-- script -->
    <?php include_once '../../../../script.php'?>

    <?php include_once '../../common/side_bar_script.php'?>

    <?php include_once '../../../script.php'?>    

    <?php if ((int)$has_permission['has_permission']){ ?>
        <script>
            function formatDate(the_date){
                var weekday = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
                var month = ["January","February","March","April","May","June","July","August","September","October","November","December"];

                var day = weekday[the_date.getDay()];
                var date = the_date.getDate();
                var month = month[the_date.getMonth()];
                var year = the_date.getFullYear();

                return day + ', ' + date + ' ' + month + ' ' + year;
            }

            function convertMsToTime(milliseconds){
                var seconds = Math.floor(milliseconds / 1000);
                var minutes = Math.floor(seconds / 60);
                var hours = Math.floor(minutes / 60);

                seconds = seconds % 60;
                minutes = minutes % 60;

                if (hours < 10)
                    hours = '0' + hours;
                if (seconds < 10)
                    seconds = '0' + seconds;
                if (minutes < 10)
                    minutes = '0' + minutes;

                return hours + ':' + minutes + ':' + seconds;
            }

            function close_message() {
                $('.form-message').remove();
            }

            function delete_event(event_id){
                <?php if ((int)$has_modify_permission['has_permission']){ ?>
                    if (confirm("Are you sure you want to delete the event?")){
                        $.ajax({ 
                            url: '../../helpers/calendar.php',
                            data: {delete_event_id: event_id},
                            type: 'post',
                            success: function(){
                                window.location.reload();
                            }
                        });
                    }
                <?php } else { ?>
                    alert('Sorry, you do not have the permission to modify the event calendar.');
                <?php } ?>
            }

            function showEditEventOffCanvas(){
                <?php if (!(int)$has_modify_permission['has_permission']){ ?>
                    alert('Sorry, you do not have the permission to modify the event calendar.');
                <?php } else { ?>
                    $('#offcanvasViewEvent').offcanvas('hide'); 
                    $('#offcanvasEditEvent').offcanvas('show'); 
                    close_message();
                    $('.text-danger').text(''); 
                    $('#offcanvasEditEvent .offcanvas-body').animate({ scrollTop: 0 }, 0);
                <?php } ?>
            }

            $(document).on('DOMContentLoaded', function() {
                //show alert message if session success is not empty
                <?php if (isset($success)) { ?>
                    alert('<?=$success?>');
                <?php } ?>

                var data;

                $.ajax({
                    url: '../../helpers/calendar.php',
                    data: {get_calendar_data: true},
                    method: 'post',
                    async: false,
                    success: function(output){
                        data = $.parseJSON(output);
                    }
                });

                var calendar = new FullCalendar.Calendar($('#event-calendar')[0], {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'addEventButton dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    views: {
                        dayGrid: {
                            fixedWeekCount: false,
                            dayMaxEvents: 3
                        },
                        timeGrid: {
                            eventMaxStack: 2,
                            slotEventOverlap: false
                        }
                    },
                    events: data,
                    contentHeight: 750,
                    customButtons: {
                        addEventButton: {
                            text: 'Add Event',
                            click: function() {
                                <?php if (!(int)$has_modify_permission['has_permission']){ ?>
                                    alert('Sorry, you do not have the permission to modify the event calendar.');
                                <?php } else { ?>
                                    $('#close-restaurant, #all-day, #repeat').prop('checked', false);
                                    $('#close-restaurant, #all-day, #repeat').trigger('change');
                                    close_message();
                                    $('#start-date, #end-date, #start-time, #end-time').val('');
                                    $('.text-danger').text('');
                                    $('input').removeClass('red-box-shadow is-invalid');
                                    $('#event-color').val('#563d7c');
                                    $('#offcanvasAddEvent').offcanvas('show');
                                <?php } ?>
                            }
                        }
                    },
                    eventDataTransform: function(event) { 
                        if (event.allDay && formatDate(new Date(event.start)) != formatDate(new Date(event.end))){
                            event.end = moment(event.end).add(1, 'days');
                            event.end = datetimeFormatter(event.end._d, 'datetime_local');
                        }
                        return event;
                    },
                    eventClick: function(info) {
                      if (info.event.id.indexOf('event') == -1)
                        $('#offcanvasViewEvent #offcanvasViewEventLabel').text(info.event.title);

                        var html = '';

                      //title + buttons for not reservation type event
                      if (info.event.id.indexOf('event') != -1){
                            html += '<div class="d-flex">';
                            html +=     '<i class="bi bi-fonts align-self-center" style="font-size: 30px;"></i>';
                            html +=     '<span class="align-self-center ms-1">' + info.event.title + '</span>';
                            html +=     '<button type="button" class="btn bg-primary bg-opacity-75 ms-auto" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit" onclick="showEditEventOffCanvas();"><i class="fas fa-pen font-size-20 text-white m-0 p-0" style="line-height: 30px;"></i></button>';
                            html +=     '<button type="button" class="btn bg-danger bg-opacity-75 ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" onclick="delete_event(' + info.event.id.substring(6) + ')"><i class="fas fa-trash-alt font-size-20 text-white m-0 p-0" style="line-height: 30px;"></i></button>';
                            html += '</div>';
                      } else {
                            html += '<div class="float-end">';
                            html +=     '<a href="../reservation/reservation.php?search_id=' + info.event.id.substring(12) + '" target="_blank"><button type="button" class="btn bg-success bg-opacity-75 ms-2" data-bs-toggle="tooltip" data-bs-placement="top" title="View"><i class="fas fa-eye font-size-20 text-white m-0 p-0" style="line-height: 30px;"></i></button></a>';
                            html += '</div>';
                      }

                      //time
                            html += '<div class="d-flex' + (info.event.id.indexOf('event') != -1 ? ' mt-4' : '') + '">';
                            html +=     '<i class="bi bi-clock px-1" style="font-size: 23px;"></i>';
                            html +=     '<div class="d-flex flex-column ms-1">'
                      
                      if (info.event.allDay){
                            html +=         '<span>Start: &nbsp;&nbsp;' + formatDate(info.event.start) + '</span>';
                        if (info.event.end == null)
                            html +=         '<span>End: &nbsp;&nbsp;' + formatDate(info.event.start) + '</span>';
                        else
                            html +=         '<span>End: &nbsp;&nbsp;' + formatDate(moment(info.event.end).subtract(1, 'days')._d) + '</span>';
                            html +=         '<small class="text-muted">All Day</small>';
                      } else {
                        if (info.event.id.indexOf('event') == -1 || (info.event.id.indexOf('event') != -1 && formatDate(info.event.start) == formatDate(info.event.end))){
                            html +=         '<span>' + datetimeFormatter(info.event.start, 'time') + (info.event.end == null ? '' : ' - ' + datetimeFormatter(info.event.end, 'time')) + '</span>';
                            html +=         '<small class="text-muted">' + formatDate(info.event.start) + '</small>';
                        } else {
                            html +=         '<span>Start: &nbsp;&nbsp;' + formatDate(info.event.start) + ', ' + datetimeFormatter(info.event.start, 'time') + '</span>';
                            html +=         '<span>End: &nbsp;&nbsp;' + formatDate(info.event.end) + ', ' + datetimeFormatter(info.event.end, 'time') + '</span>';
                        }
                      }

                            html +=     '</div>';
                            html += '</div>';

                      //repeat days
                      if (info.event.id.indexOf('event') != -1 && info.event.groupId != ''){
                            var weekday = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
                            var description = info.event.extendedProps.description;
                            var repeat_days = $.parseJSON(description.repeat_days);
                            var repeat_days_string = 'Every ';
                            $.each(repeat_days, function(key, value){
                                repeat_days_string += weekday[value];
                                if (key != repeat_days.length - 1)
                                    repeat_days_string += ', ';
                            })

                            html += '<div class="d-flex mt-3">';
                            html +=     '<i class="bi bi-arrow-repeat align-self-center" style="font-size: 30px;"></i>';
                            html +=     '<div class="d-flex flex-column align-self-center ms-1">';
                            html +=         '<span>' + repeat_days_string + '</span>';

                          if (info.event._def.recurringDef.typeData.startRecur != null)
                            html +=         '<small class="text-muted">from ' + datetimeFormatter(info.event._def.recurringDef.typeData.startRecur, 'date');
                          if (info.event._def.recurringDef.typeData.endRecur != null)
                            html +=         ' to ' + datetimeFormatter(info.event._def.recurringDef.typeData.endRecur, 'date');
                          if (info.event._def.recurringDef.typeData.startRecur != null || info.event._def.recurringDef.typeData.endRecur != null)
                            html +=         '</small>';

                            html +=     '</div>';
                            html += '</div>';
                      }

                      //reservation type event data
                      if (info.event.id.indexOf('event') == -1){
                          $.ajax({
                            url: '../../helpers/calendar.php',
                            data: {
                                reservation_id: info.event.id.substring(12),
                                get_reservation_data: true
                            },
                            method: 'post',
                            async: false,
                            success: function(output) {
                                var reservation_data = $.parseJSON(output);
                                
                                html += '<div class="d-flex mt-4">';
                                html +=     '<i class="bi bi-person pe-1" style="font-size: 30px;"></i>';
                                html +=     '<span class="align-self-center">' + reservation_data['name'] + '</span>';
                                html += '</div>';
                                html += '<div class="d-flex mt-4 pt-2">';
                                html +=     '<i class="fas fa-chair pe-2" style="font-size: 29px; padding-left: 0.15rem;"></i>';
                                html +=     '<span class="align-self-center">' + reservation_data['table_name'] + '</span>';
                                html += '</div>';
                                html += '<div class="d-flex mt-4 pt-2">';
                                html +=     '<i class="bi bi-asterisk pe-2" style="font-size: 29px;"></i>';
                                html +=     '<span class="align-self-center">' + reservation_data['special_request'] + '</span>';
                                html += '</div>';
                                html += '<div class="d-flex mt-4">';
                                html +=     '<i class="bi bi-info-circle pe-2" style="font-size: 29px;"></i>';
                                html +=     '<span class="align-self-center">' + reservation_data['status'] + '</span>';
                                html += '</div>';
                                html += '<div class="d-flex mt-4">';
                                html +=     '<i class="bi bi-pencil-square pe-2" style="font-size: 29px;"></i>';
                                html +=     '<span class="align-self-center">' + reservation_data['remarks'] + '</span>';
                                html += '</div>';
                            }
                          });
                            
                      }

                        $('#offcanvasViewEvent .offcanvas-body').html(html);
                        $('[data-bs-toggle="tooltip"]').tooltip();
                        $('[data-bs-toggle="tooltip"]').on('click', function () {
                            $(this).tooltip('hide');
                        })
                        $('#offcanvasViewEvent').offcanvas('show');

                        //if this event is not reservation type event, update edit event offcanvas
                        if (info.event.id.indexOf('event') != -1){
                            //update event id
                            $('#offcanvasEditEvent #event_id').val(info.event.id.substring(6));
                            
                            //update title
                            var closed_type = info.event.extendedProps.description.closed_type;
                            if (closed_type)
                                $('#offcanvasEditEvent #edit-close-restaurant').prop('checked', true);
                            else 
                                $('#offcanvasEditEvent #edit-close-restaurant').prop('checked', false);
                            $('#offcanvasEditEvent #edit-close-restaurant').trigger('change');

                            if (!closed_type)
                                $('#offcanvasEditEvent #edit-title').val(info.event.title);

                            //update all day select + start and end date time
                            if (info.event.allDay)
                                $('#offcanvasEditEvent #edit-all-day').prop('checked', true);
                            else 
                                $('#offcanvasEditEvent #edit-all-day').prop('checked', false);
                            $('#offcanvasEditEvent #edit-all-day').trigger('change');

                            //when this event is recurring event
                            if (info.event.groupId != ''){
                                //update repeat days
                                $('#offcanvasEditEvent #edit-repeat').prop('checked', true);
                                $('#offcanvasEditEvent #edit-repeat').trigger('change');

                                $.each(repeat_days, function(key, value){
                                    $('#edit-repeat-' + value).prop('checked', true);
                                });

                                //input start and end date time
                                $('#offcanvasEditEvent #edit-start-date').val(datetimeFormatter(info.event._def.recurringDef.typeData.startRecur, 'input_date'));
                                $('#offcanvasEditEvent #edit-end-date').val(datetimeFormatter(info.event._def.recurringDef.typeData.endRecur, 'input_date'));

                                if (!info.event.allDay){
                                    $('#offcanvasEditEvent #edit-start-time').val(convertMsToTime(info.event._def.recurringDef.typeData.startTime.milliseconds));
                                    $('#offcanvasEditEvent #edit-end-time').val(convertMsToTime(info.event._def.recurringDef.typeData.endTime.milliseconds));
                                }
                            } else {
                                //update repeat days
                                $('#offcanvasEditEvent #edit-repeat').prop('checked', false);
                                $('#offcanvasEditEvent #edit-repeat').trigger('change');

                                //input start and end date time
                                if (!info.event.allDay){
                                    $('#offcanvasEditEvent #edit-start-datetime').val(datetimeFormatter(info.event.start, 'datetime_local'));
                                    $('#offcanvasEditEvent #edit-end-datetime').val(datetimeFormatter(info.event.end, 'datetime_local'));
                                } else {
                                    $('#offcanvasEditEvent #edit-start-datetime').val(datetimeFormatter(info.event.start, 'input_date'));

                                    if (info.event.end == null)
                                        $('#offcanvasEditEvent #edit-end-datetime').val(datetimeFormatter(info.event.start, 'input_date'));
                                    else
                                        $('#offcanvasEditEvent #edit-end-datetime').val(datetimeFormatter(moment(info.event.end).subtract(1, 'days')._d, 'input_date'));
                                }

                            }                            

                            //update event color
                            $('#offcanvasEditEvent #edit-event-color').val(info.event.backgroundColor);

                            //remove error messages etc.
                            $('.form-message').remove();
                            $('input').removeClass('red-box-shadow is-invalid');
                            $('.text-danger').text('');
                        }
                    }
                });

                calendar.render();
                
                $('table').addClass('transition');

                $('#side-bar-expand').on('click', function() {
                    setTimeout(function(){
                        calendar.render();
                    }, 460);
                });

                $('#repeat, #edit-repeat').change(function() {
                    //reset repeat days
                    if ($('input[name=\'' + $(this).attr('id') + '-day[]\']:checked').length > 0)
                        $('input[name=\'' + $(this).attr('id') + '-day[]\']:checked').prop('checked', false);

                    //show and hide date time fields
                    if ($(this).prop('checked')){
                        $('#' + $(this).attr('id') + '-day-wrapper').removeClass('d-none');
                        $('#' + $(this).attr('id') + '-start-end-datetime-wrapper').removeClass('d-none');
                        $('#not-' + $(this).attr('id') + '-start-end-datetime-wrapper').addClass('d-none');
                    } else {
                        $('#' + $(this).attr('id') + '-day-wrapper').addClass('d-none');
                        $('#' + $(this).attr('id') + '-start-end-datetime-wrapper').addClass('d-none');
                        $('#not-' + $(this).attr('id') + '-start-end-datetime-wrapper').removeClass('d-none');
                    }
                        
                });

                $('#close-restaurant, #edit-close-restaurant').change(function() {
                    var title_id;
                    if ($(this).attr('id') == 'close-restaurant')
                        title_id = 'title';
                    else
                        title_id = 'edit-title';

                    //update title
                    if ($(this).prop('checked')){
                        $('#' + title_id).val('Restaurant Closed');
                        $('#' + title_id).prop('disabled', true);
                        $('#' + title_id).parent().find('label span').addClass('d-none');
                    } else {
                        $('#' + title_id).val('');
                        $('#' + title_id).prop('disabled', false);
                        $('#' + title_id).parent().find('label span').removeClass('d-none');
                    }
                });

                $('#all-day, #edit-all-day').change(function() {
                    var edit_string;
                    if ($(this).attr('id') == 'all-day')
                        edit_string = '';
                    else
                        edit_string = 'edit-';

                    //reset datetime field values
                    $('#' + edit_string + 'start-datetime, #' + edit_string + 'end-datetime').val('');

                    //change type of datetime fields
                    if ($(this).prop('checked')){
                        $('#' + edit_string + 'start-datetime, #' + edit_string + 'end-datetime').prop('type', 'date');
                        $('#' + edit_string + 'start-time, #' + edit_string + 'end-time').parent().addClass('d-none');
                    } else {
                        $('#' + edit_string + 'start-datetime, #' + edit_string + 'end-datetime').prop('type', 'datetime-local');
                        $('#' + edit_string + 'start-time, #' + edit_string + 'end-time').parent().removeClass('d-none');
                    }
                });

                $("#offcanvasAddEvent form, #offcanvasEditEvent form").submit(function(event){
                    event.preventDefault();

                    var edit_string;
                    if ($(this).parent().parent().attr('id') == 'offcanvasAddEvent')
                        edit_string = '';
                    else
                        edit_string = 'edit-';

                    $('.form-message').remove();
                    $('input').removeClass('red-box-shadow is-invalid');
                    $('.text-danger').text('');

                    var error = false;

                    //check title
                    if ($.trim($('#' + edit_string + 'title').val()) == ''){
                        $('#' + edit_string + 'title').addClass('red-box-shadow is-invalid');
                        $('#' + edit_string + 'title').parent().find('p').text('Please enter the event title!');
                        error = true;
                    } else if ($('#' + edit_string + 'title').val().length > 100){
                        $('#' + edit_string + 'title').addClass('red-box-shadow is-invalid');
                        $('#' + edit_string + 'title').parent().find('p').text('Event title must be between 1 and 100 characters!');
                        error = true;
                    }

                    //check start datetime and end datetime
                    if (!$('#' + edit_string + 'repeat').prop('checked')){
                        if ($('#' + edit_string + 'start-datetime').val() == ''){
                            $('#' + edit_string + 'start-datetime').addClass('red-box-shadow is-invalid');
                            $('#' + edit_string + 'start-datetime').parent().parent().find('p').text('Please select the start datetime!');
                            error = true;
                        } else if ($('#' + edit_string + 'end-datetime').val() == ''){
                            $('#' + edit_string + 'end-datetime').addClass('red-box-shadow is-invalid');
                            $('#' + edit_string + 'end-datetime').parent().parent().find('p').text('Please select the end datetime!');
                            error = true;
                        } else if ($('#' + edit_string + 'end-datetime').val() < $('#' + edit_string + 'start-datetime').val()){
                            $('#' + edit_string + 'end-datetime, #' + edit_string + 'start-datetime').addClass('red-box-shadow is-invalid');

                            if ($('#' + edit_string + 'all-day').prop('checked'))
                                var error_msg = 'The end date should be later than the start date!';
                            else
                                var error_msg = 'The end datetime should be later than the start datetime!';

                            $('#' + edit_string + 'end-datetime').parent().parent().find('p').text(error_msg);
                            error = true;
                        }
                    }

                    //check repeat days
                    if ($('#' + edit_string + 'repeat').prop('checked') && $('input[name=\'' + edit_string + 'repeat-day[]\']:checked').length == 0){
                        $('#' + edit_string + 'repeat').parent().parent().find('p').text('Please select at least one day');
                    }

                    //check start date, start time, end date and end time
                    if ($('#' + edit_string + 'repeat').prop('checked')){
                        if ($('#' + edit_string + 'start-date').val() == ''){
                            $('#' + edit_string + 'start-date').addClass('red-box-shadow is-invalid');
                            $('#' + edit_string + 'start-date').parent().parent().find('p').text('Please select the start date!');
                            error = true;
                        } else if ($('#' + edit_string + 'end-date').val() == ''){
                            $('#' + edit_string + 'end-date').addClass('red-box-shadow is-invalid');
                            $('#' + edit_string + 'end-date').parent().parent().find('p').text('Please select the end date!');
                            error = true;
                        } else if ($('#' + edit_string + 'start-time').val() == '' && !$('#' + edit_string + 'all-day').prop('checked')){
                            $('#' + edit_string + 'start-time').addClass('red-box-shadow is-invalid');
                            $('#' + edit_string + 'start-time').parent().parent().find('p').text('Please select the start time!');
                            error = true;
                        } else if ($('#' + edit_string + 'end-time').val() == '' && !$('#' + edit_string + 'all-day').prop('checked')){
                            $('#' + edit_string + 'end-time').addClass('red-box-shadow is-invalid');
                            $('#' + edit_string + 'end-time').parent().parent().find('p').text('Please select the end time!');
                            error = true;
                        } else if ($('#' + edit_string + 'end-date').val() < $('#' + edit_string + 'start-date').val()){
                            $('#' + edit_string + 'end-date, #' + edit_string + 'start-date').addClass('red-box-shadow is-invalid');
                            $('#' + edit_string + 'end-date').parent().parent().find('p').text('The end date should be later than the start date!');
                            error = true;
                        } else if ($('#' + edit_string + 'end-time').val() < $('#' + edit_string + 'start-time').val() && !$('#' + edit_string + 'all-day').prop('checked')){
                            $('#' + edit_string + 'end-time, #' + edit_string + 'start-time').addClass('red-box-shadow is-invalid');
                            $('#' + edit_string + 'end-time').parent().parent().find('p').text('The end time should be later than the start time!');
                            error = true;
                        }
                    }

                    if (error){
                        if (edit_string == ''){
                            $('<div class="alert form-message mb-2 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;There are errors on the form. Please fix them before continuing.</div>').insertBefore('#offcanvasAddEvent form');
                            $('#offcanvasAddEvent .offcanvas-body').animate({ scrollTop: 0 }, 0);
                        } else {
                            $('<div class="alert form-message mb-2 alert-danger" role="alert"><button type="button" onclick="close_message();" class="btn-close btn float-end" aria-label="Close"></button><i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;There are errors on the form. Please fix them before continuing.</div>').insertBefore('#offcanvasEditEvent form');
                            $('#offcanvasEditEvent .offcanvas-body').animate({ scrollTop: 0 }, 0);
                        }
                    } else {
                        if (edit_string == '')
                            $('#offcanvasAddEvent form').unbind().submit();
                        else
                            $('#offcanvasEditEvent form').unbind().submit();
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