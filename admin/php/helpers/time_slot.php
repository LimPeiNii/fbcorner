<?php

    function getTimeSlots($connection, $rest_id){
        $sql = "SELECT * FROM time_slot WHERE `rest_id` = '" . $rest_id . "'";
        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    function getTimeSlot($connection, $time_slot_id){
        $sql = "SELECT * FROM time_slot WHERE `time_slot_id` = '" . $time_slot_id . "'";
        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        return $results;
    }

    function getTimeSlotSetting($connection, $rest_id){
        $sql = "SELECT * FROM `settings` WHERE `rest_id` = '" . $rest_id . "' AND `key` = 'time_slot'";
        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    function getUnavailableTimeSlots($connection, $rest_id){
        $sql = "SELECT * FROM time_slot WHERE `rest_id` = '" . $rest_id . "' AND status = '0'";
        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    //submit form
    if (isset($_POST['submit_form_time_slot'])){
        
        session_start();

        include_once '../../../db_connect.php';
        include_once '../helpers/reservation.php';
        include_once '../helpers/order.php';
        include_once '../helpers/table.php';
        include_once '../../../session.php';

        updateLastActivity();

        date_default_timezone_set("Asia/Kuala_Lumpur");

        $status = $_POST['status'];
        $tables = $_POST['table'];
        ksort($tables);

        //edit time slot
        if (isset($_GET['time_slot_id'])){
            $sql = "UPDATE `time_slot` SET `status` = '" . $status . "', tables = '" . json_encode($tables) . "' WHERE time_slot_id = '" . $_GET['time_slot_id'] . "'";
            $success_msg = 'You have successfully edited the time slot.';
        }
        
        $connection->query($sql);

        //update reservation if disable a time slot
        if ($status == '0'){
            $reservations_array = getReservations($connection, $_SESSION['login_rest_id'], ['time_slot_id' => $_GET['time_slot_id'], 'status' => 1, 'not_remarks' => 'Ongoing']);

            if ($reservations_array){
                foreach ($reservations_array as $reservations_info){
                    if (!empty($reservations_info['remarks']))
                        $remarks = $reservations_info['remarks'] . "<br>Selected time slot(s) is currently unavailable";
                    else
                        $remarks = "Selected time slot(s) is currently unavailable";

                    $sql2 = "UPDATE `reservation` SET `status` = 2, modified_date = '" . date('Y-m-d H:i:s') . "', remarks = '" . $remarks . "' WHERE reservation_id = '" . $reservations_info['reservation_id'] . "'";
                    $connection->query($sql2);

                    //update preorder
                    $preorder_info = getOrdersByReservationID($connection, $reservations_info['reservation_id'], ['not_statuses' => ['Cancelled', 'Completed', 'Disabled']]);
                    if ($preorder_info){
                        foreach ($preorder_info as $preorder){
                            if (!empty($preorder['remarks']))
                                $remarks = $preorder['remarks'] . "<br>Reservation has been disabled";
                            else
                                $remarks = "Reservation has been disabled";

                            $sql3 = "UPDATE `order` SET `status` = 'Disabled', voucher_id = 0, promotions = null, modified_date = '" . date('Y-m-d H:i:s') . "', remarks = '" . $remarks . "' WHERE order_id = '" . $preorder['order_id'] . "'";
                            $connection->query($sql3);
                        }
                    }

                    //send email
                    if ($preorder_info)
                        sendDisabledReservationEmail($connection, $_SESSION['login_rest_id'], $reservations_info['cus_id'], $reservations_info['reservation_id'], $preorder_info[0]['order_id']);
                    else
                        sendDisabledReservationEmail($connection, $_SESSION['login_rest_id'], $reservations_info['cus_id'], $reservations_info['reservation_id']);
                }
            }
        } else {
            //update reservation if disable a table in this time slot
            foreach ($tables as $table_id => $table_status){
                if ($table_status == '0'){
                    $reservations_array = getReservations($connection, $_SESSION['login_rest_id'], ['time_slot_id' => $_GET['time_slot_id'], 'table_id' => $table_id, 'status' => 1, 'not_remarks' => 'Ongoing']);

                    if ($reservations_array){
                        foreach ($reservations_array as $reservations_info){
                            if (!empty($reservations_info['remarks']))
                                $remarks = $reservations_info['remarks'] . "<br>Selected table is currently unavailable for the selected time slot";
                            else
                                $remarks = "Selected table is currently unavailable for the selected time slot";

                            $sql2 = "UPDATE `reservation` SET `status` = 2, modified_date = '" . date('Y-m-d H:i:s') . "', remarks = '" . $remarks . "' WHERE reservation_id = '" . $reservations_info['reservation_id'] . "'";
                            $connection->query($sql2);

                            //update preorder
                            $preorder_info = getOrdersByReservationID($connection, $reservations_info['reservation_id'], ['not_statuses' => ['Cancelled', 'Completed', 'Disabled']]);
                            if ($preorder_info){
                                foreach ($preorder_info as $preorder){
                                    if (!empty($preorder['remarks']))
                                        $remarks = $preorder['remarks'] . "<br>Reservation has been disabled";
                                    else
                                        $remarks = "Reservation has been disabled";

                                    $sql3 = "UPDATE `order` SET `status` = 'Disabled', voucher_id = 0, promotions = null, modified_date = '" . date('Y-m-d H:i:s') . "', remarks = '" . $remarks . "' WHERE order_id = '" . $preorder['order_id'] . "'";
                                    $connection->query($sql3);
                                }
                            }

                            //send email
                            if ($preorder_info)
                                sendDisabledReservationEmail($connection, $_SESSION['login_rest_id'], $reservations_info['cus_id'], $reservations_info['reservation_id'], $preorder_info[0]['order_id']);
                            else
                                sendDisabledReservationEmail($connection, $_SESSION['login_rest_id'], $reservations_info['cus_id'], $reservations_info['reservation_id']);
                        }
                    }
                }
            }
        }

        $_SESSION['success'] = $success_msg;
        header("Location: ../pages/reservation/time_slot.php");
        exit;
    }

    //delete time slot
    if (isset($_POST['selected_group_time_slot'])){
        
        session_start();

        include_once '../../../db_connect.php';
        include_once '../helpers/reservation.php';
        include_once '../helpers/order.php';
        include_once '../helpers/table.php';
        include_once '../../../session.php';

        updateLastActivity();

        date_default_timezone_set("Asia/Kuala_Lumpur");

        $selected_groups = $_POST['selected_group_time_slot'];
        $counter = 0;
        foreach ($selected_groups as $selected_group){
            //update reservation and preorder if delete a time slot
            $reservations_array = getReservations($connection, $_SESSION['login_rest_id'], ['time_slot_id' => $selected_group, 'status' => 1, 'not_remarks' => 'Ongoing']);

            if ($reservations_array){
                foreach ($reservations_array as $reservations_info){
                    if (!empty($reservations_info['remarks']))
                        $remarks = $reservations_info['remarks'] . "<br>Selected time slot is no longer available";
                    else
                        $remarks = "Selected time slot is no longer available";

                    $sql2 = "UPDATE `reservation` SET `status` = 2, modified_date = '" . date('Y-m-d H:i:s') . "', remarks = '" . $remarks . "' WHERE reservation_id = '" . $reservations_info['reservation_id'] . "'";
                    $connection->query($sql2);

                    //update preorder
                    $preorder_info = getOrdersByReservationID($connection, $reservations_info['reservation_id'], ['not_statuses' => ['Cancelled', 'Completed', 'Disabled']]);
                    if ($preorder_info){
                        foreach ($preorder_info as $preorder){
                            if (!empty($preorder['remarks']))
                                $remarks = $preorder['remarks'] . "<br>Reservation has been disabled";
                            else
                                $remarks = "Reservation has been disabled";

                            $sql3 = "UPDATE `order` SET `status` = 'Disabled', voucher_id = 0, promotions = null, modified_date = '" . date('Y-m-d H:i:s') . "', remarks = '" . $remarks . "' WHERE order_id = '" . $preorder['order_id'] . "'";
                            $connection->query($sql3);
                        }
                    }

                    //send email
                    if ($preorder_info)
                        sendDisabledReservationEmail($connection, $_SESSION['login_rest_id'], $reservations_info['cus_id'], $reservations_info['reservation_id'], $preorder_info[0]['order_id']);
                    else
                        sendDisabledReservationEmail($connection, $_SESSION['login_rest_id'], $reservations_info['cus_id'], $reservations_info['reservation_id']);
                }
            }

            //save the start time and end time to relevant reservation
            $reservations_array_2 = getReservations($connection, $_SESSION['login_rest_id'], ['time_slot_id' => $selected_group]);
            if ($reservations_array_2){
                foreach ($reservations_array_2 as $reservations_info){
                    if (!empty($reservations_info['startTime']) && !empty($reservations_info['endTime'])){
                        $deleted_data = json_decode($reservations_info['deleted_data'], true);
                        $deleted_data['startTime'] = $reservations_info['startTime'];
                        $deleted_data['endTime'] = $reservations_info['endTime'];

                        $sql4 = "UPDATE `reservation` SET deleted_data = '" . json_encode($deleted_data) . "' WHERE reservation_id = '" . $reservations_info['reservation_id'] . "'";
                        $connection->query($sql4);
                    }
                }
            }

            $sql5 = "DELETE FROM `time_slot` WHERE time_slot_id = '" . $selected_group . "'";
            $connection->query($sql5);

            $counter++;
        }

        //if no time slot left after deletion, then delete time slot settings
        $time_slots = getTimeSlots($connection, $_SESSION['login_rest_id']);

        if (!$time_slots){
            $sql6 = "DELETE FROM `settings` WHERE rest_id = '" . $_SESSION['login_rest_id'] . "' AND `key` = 'time_slot'";
            $connection->query($sql6);
        }
        
        $_SESSION['success'] = "You have successfully deleted " . (string)$counter . " time slot(s)."; 
        exit;
    }

    //check settings form
    if (isset($_POST['submit_check_settings'])){
        
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $earliest_time = $_POST['earliest-time'];
        $latest_time   = $_POST['latest-time'];
        $h             = $_POST['h'];
        $m             = $_POST['m'];
        

        $json = [];

        //error checking
        //earliest time
        if (empty($earliest_time))
            $json['error']['earliest-time'] = "Please select the earliest reservation time!";

        //latest time
        if (empty($latest_time))
            $json['error']['latest-time'] = "Please select the latest reservation time!";

        //hour & minute duration
        if (empty($h) && empty($m)){
            $json['error']['h'] = "Please enter the duration per time slot!";
            $json['error']['m'] = "Please enter the duration per time slot!";
        }

        echo json_encode($json);
        
    }

    //submit settings form
    if (isset($_POST['submit_form_settings'])){
        
        session_start();

        include_once '../../../db_connect.php';
        include_once '../helpers/table.php';
        include_once '../helpers/reservation.php';
        include_once '../helpers/order.php';
        include_once '../../../session.php';

        updateLastActivity();

        date_default_timezone_set("Asia/Kuala_Lumpur");

        $tables_results = getTables($connection, $_SESSION['login_rest_id']);
        $tables = [];
        foreach ($tables_results as $table){
            $tables[$table['table_id']] = '1';
        }

        $earliest_time = $_POST['earliest-time'];
        $latest_time   = $_POST['latest-time'];
        $h             = $_POST['h'];
        $m             = $_POST['m'];
        $max_selected  = $_POST['maximum-time-slot'];

        if (empty($max_selected)){
            $max_selected = 1;
        }

        $settings_data = array(
            'earliest-time'     => $earliest_time,
            'latest-time'       => $latest_time,
            'h'                 => $h,
            'm'                 => $m,
            'maximum-time-slot' => $max_selected
        );

        $timeslot_limit = [date('H:i',strtotime($earliest_time))];
        while ($timeslot_limit[count($timeslot_limit)-1] < date('H:i',strtotime($latest_time))){
            $last_element = $timeslot_limit[count($timeslot_limit)-1];
            $timestamp = strtotime($last_element) + ((int)$h * 60 * 60) + ((int)$m * 60);
            $timeslot_limit[] = date('H:i',$timestamp);
        }

        $result = getTimeSlotSetting($connection, $_SESSION['login_rest_id']);

        if (count($result) > 0){
            //save settings data
            $sql = "UPDATE `settings` SET `value` = '" . json_encode($settings_data) . "' WHERE rest_id = '" . $_SESSION['login_rest_id'] . "' AND `key` = 'time_slot'";
            $connection->query($sql);

            //update reservation and preorder if reset time slots
            $reservations_array = getReservations($connection, $_SESSION['login_rest_id'], ['status' => 1, 'not_remarks' => 'Ongoing']);

            if ($reservations_array){
                foreach ($reservations_array as $reservations_info){
                    if (!empty($reservations_info['remarks']))
                        $remarks = $reservations_info['remarks'] . "<br>Selected time slot is no longer available";
                    else
                        $remarks = "Selected time slot is no longer available";
                    

                    $sql2 = "UPDATE `reservation` SET `status` = 2, modified_date = '" . date('Y-m-d H:i:s') . "', remarks = '" . $remarks . "' WHERE reservation_id = '" . $reservations_info['reservation_id'] . "'";
                    $connection->query($sql2);

                    //update preorder
                    $preorder_info = getOrdersByReservationID($connection, $reservations_info['reservation_id'], ['not_statuses' => ['Cancelled', 'Completed', 'Disabled']]);
                    if ($preorder_info){
                        foreach ($preorder_info as $preorder){
                            if (!empty($preorder['remarks']))
                                $remarks = $preorder['remarks'] . "<br>Reservation has been disabled";
                            else
                                $remarks = "Reservation has been disabled";

                            $sql3 = "UPDATE `order` SET `status` = 'Disabled', voucher_id = 0, promotions = null, modified_date = '" . date('Y-m-d H:i:s') . "', remarks = '" . $remarks . "' WHERE order_id = '" . $preorder['order_id'] . "'";
                            $connection->query($sql3);
                        }
                    }

                    //send email
                    if ($preorder_info)
                        sendDisabledReservationEmail($connection, $_SESSION['login_rest_id'], $reservations_info['cus_id'], $reservations_info['reservation_id'], $preorder_info[0]['order_id']);
                    else
                        sendDisabledReservationEmail($connection, $_SESSION['login_rest_id'], $reservations_info['cus_id'], $reservations_info['reservation_id']);
                }
            }

            //save the timestamp for relevant reservation
            $reservations_array_2 = getReservations($connection, $_SESSION['login_rest_id'], ['no_deleted_time_data' => true]);
            if ($reservations_array_2){
                foreach ($reservations_array_2 as $reservations_info){
                    if (!empty($reservations_info['startTime']) && !empty($reservations_info['endTime'])){
                        $deleted_data = json_decode($reservations_info['deleted_data'], true);
                        $deleted_data['startTime'] = $reservations_info['startTime'];
                        $deleted_data['endTime'] = $reservations_info['endTime'];

                        $sql4 = "UPDATE `reservation` SET deleted_data = '" . json_encode($deleted_data) . "' WHERE reservation_id = '" . $reservations_info['reservation_id'] . "'";
                        $connection->query($sql4);
                    }
                }
            }

            //create time slots
            $sql3 = "DELETE FROM `time_slot` WHERE rest_id = '" . $_SESSION['login_rest_id'] . "'";
            $connection->query($sql3);
            
            $length = count($timeslot_limit);
            for ($i=0; $i<$length-1; $i++){
                $sql4 = "INSERT INTO `time_slot`(rest_id, `start_time`, end_time, `status`, `tables`) VALUES ('" . $_SESSION['login_rest_id'] . "','" . $timeslot_limit[$i] . "','" . $timeslot_limit[$i+1] . "', '1' ,'" . json_encode($tables) . "')";
                $connection->query($sql4);
            }

            $success_msg = 'You have successfully reset all time slots.';

        } else {
            //save settings data
            $sql = "INSERT INTO `settings`(rest_id, `key`, `value`) VALUES ('" . $_SESSION['login_rest_id'] . "','time_slot','" . json_encode($settings_data) . "')";
            $connection->query($sql);

            //create time slots
            $length = count($timeslot_limit);
            for ($i=0; $i<$length-1; $i++){
                $sql2 = "INSERT INTO `time_slot`(rest_id, `start_time`, end_time, `status`, `tables`) VALUES ('" . $_SESSION['login_rest_id'] . "','" . $timeslot_limit[$i] . "','" . $timeslot_limit[$i+1] . "', '1' ,'" . json_encode($tables) . "')";
                $connection->query($sql2);
            }
            $success_msg = 'You have successfully added time slots.';

        }

        $_SESSION['success'] = $success_msg;
        header("Location: ../pages/reservation/time_slot.php");
        
    }

    // to get data for details modal
    if (isset($_POST['id'])){

        include_once '../../../db_connect.php';
        include_once '../helpers/table.php';
        include_once '../../../session.php';

        updateLastActivity();

        $time_slot_result = getTimeSlot($connection, $_POST['id']);
        $time_slot_result['tables'] = json_decode($time_slot_result['tables'], true);
        $tables_temp = [];
        foreach ($time_slot_result['tables'] as $key => $value){
            $table_result = getTable($connection, $key);
            $tables_temp[$table_result['table_num_name']]['status_in_time_slot'] = ($value == 0 ? 'Disabled' : 'Enabled');
            $tables_temp[$table_result['table_num_name']]['table_status'] = ($table_result['status'] == 0 ? 'Unavailable' : '');
        }

        $final_result = [];

        $final_result['Start Time'] = date('h:i a', strtotime($time_slot_result['start_time']));
        $final_result['End Time'] = date('h:i a', strtotime($time_slot_result['end_time']));
        $final_result['Status'] = $time_slot_result['status'] == 1 ? '<span class="badge bg-success">Available</span>' : '<span class="badge bg-secondary">Unavailable</span>';
        $final_result['Tables'] = $tables_temp;

        echo json_encode($final_result);
    }

?>