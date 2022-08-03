<?php

    function getTables($connection, $rest_id){
        $sql = "SELECT * FROM `table` WHERE `rest_id` = '" . $rest_id . "' ORDER BY sort_order ASC";
        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    function getTable($connection, $table_id){
        $sql = "SELECT * FROM `table` WHERE `table_id` = '" . $table_id . "'";
        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        return $results;
    }

    function getAvailableTablesBySize($connection, $rest_id, $size){
        $sql = "SELECT * FROM `table` WHERE `rest_id` = '" . $rest_id . "' AND status = '1' AND capacity = '" . $size . "' ORDER BY sort_order ASC";
        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    function getAvailableTables($connection, $rest_id){
        $sql = "SELECT * FROM `table` WHERE `rest_id` = '" . $rest_id . "' AND status = '1' ORDER BY sort_order ASC";
        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    function sendDisabledReservationEmail($connection, $rest_id, $cus_id, $reservation_id, $order_id = 0){
        include_once '../helpers/order.php';
        include_once '../helpers/restaurant.php';
        include_once '../helpers/reservation.php';
        include_once '../../../email/send_email.php';
        include_once '../../../store/php/helpers/customer.php';

        //send email to customer
        //get email body
        $_SESSION['send_email'] = array(
            'rest_id' => $rest_id,
            'cus_id'  => $cus_id,
            'reservation_id' => $reservation_id,
            'reservation_data' => getReservationFullInfo($connection, $reservation_id),
            'type' => 'edit',
            'reservation_or_preorder' => 'reservation',
            'to' => 'customer'
        );

        if ($order_id != 0){
            //add preorder data
            $_SESSION['send_email']['order_id'] = $order_id;
            $_SESSION['send_email']['order_data'] = getOrderFullInfo($connection, $order_id, 'admin');
        }

        $restaurant_name = getRestaurant($connection, $rest_id)['rest_name'];
        $customer_email = getCustomer($connection, $cus_id)['email'];

        ob_start(); //Init the output buffering
        include '../../../email/template/reservation.php'; //Include (and compiles) the given file
        $email_body = ob_get_clean(); //Get the buffer and erase it)
        unset($_SESSION['send_email']);

        sendmail($customer_email, $restaurant_name . ' - Reservation ' . $reservation_id . ' Updated', $email_body, '');

        //send email to restaurant
        //get email body
        $_SESSION['send_email'] = array(
            'rest_id' => $rest_id,
            'cus_id'  => $cus_id,
            'reservation_id' => $reservation_id,
            'reservation_data' => getReservationFullInfo($connection, $reservation_id),
            'type' => 'edit',
            'reservation_or_preorder' => 'reservation',
            'to' => 'restaurant'
        );

        if ($order_id != 0){
            //add preorder data
            $_SESSION['send_email']['order_id'] = $order_id;
            $_SESSION['send_email']['order_data'] = getOrderFullInfo($connection, $order_id, 'admin');
        }
        
        $restaurant_email = getRestaurant($connection, $rest_id)['email'];
    
        ob_start(); //Init the output buffering
        include '../../../email/template/reservation.php'; //Include (and compiles) the given file
        $email_body = ob_get_clean(); //Get the buffer and erase it)
        unset($_SESSION['send_email']);

        if (!in_array($restaurant_email, ['veganFood@gmail.com', 'thewestern@gmail.com', 'hotchicken@gmail.com', 'thejapanese@gmail.com'])){
            sendmail($restaurant_email, $restaurant_name . ' - Reservation ' . $reservation_id . ' Updated', $email_body, '');
        }
    }


    //check form
    if (isset($_POST['submit_check_table'])){
        
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $table_num_name = trim($_POST['table-number-name']);
        $capacity       = $_POST['capacity'];
        $status         = $_POST['status'];
        $sort_order     = $_POST['sort-order'];
        

        $json = [];

        //error checking
        //table number or table name
        if (empty($table_num_name))
            $json['error']['table-number-name'] = "Please enter the Table Number or Table Name!";
        elseif (strlen($table_num_name) > 10)
            $json['error']['table-number-name'] = "Table Number or Table Name must be between 1 and 10 characters!";
        elseif (strlen($_POST['edit_table_num']) == 0){
            $all_tables = getTables($connection, $_SESSION['login_rest_id']);
            $all_table_num_name = array_column($all_tables, 'table_num_name');
            if (in_array($table_num_name, $all_table_num_name)){
                $json['error']['table-number-name'] = "This Table Number or Table Name has already been used!";
            }
        }
        elseif (strlen($_POST['edit_table_num']) > 0){
            $all_tables = getTables($connection, $_SESSION['login_rest_id']);
            $all_table_num_name = array_column($all_tables, 'table_num_name');
            unset($all_table_num_name[array_search($_POST['edit_table_num'], $all_table_num_name)]);
            if (in_array($table_num_name, $all_table_num_name)){
                $json['error']['table-number-name'] = "This Table Number or Table Name has already been used!";
            }
        }

        //capacity
        if ($capacity == "")
            $json['error']['capacity'] = "Please enter the table capacity!";
        elseif ($capacity < 1)
            $json['error']['capacity'] = "Table capacity must be greater than or equal to one!";
        
        //sort order
        if ($sort_order < 0)
            $json['error']['sort-order'] = "Sort order must be greater than or equal to zero!";
            
        
        echo json_encode($json);
        
    }

    //submit form
    if (isset($_POST['submit_form_table'])){
        
        session_start();

        include_once '../../../db_connect.php';
        include_once '../helpers/time_slot.php';
        include_once '../helpers/reservation.php';
        include_once '../helpers/order.php';
        include_once '../../../session.php';

        updateLastActivity();

        date_default_timezone_set("Asia/Kuala_Lumpur");

        $table_num_name = trim($_POST['table-number-name']);
        $capacity       = $_POST['capacity'];
        $status         = $_POST['status'];
        $sort_order     = $_POST['sort-order'];

        //auto set zero if empty sort order
        if ($sort_order == "")
            $sort_order = 0;

        //create or edit table
        if (isset($_GET['table_id'])){
            $sql = "UPDATE `table` SET `table_num_name` = '" . $table_num_name . "', capacity = '" . $capacity . "', status = '" . $status . "', sort_order = '" . $sort_order . "' WHERE table_id = '" . $_GET['table_id'] . "'";
            $success_msg = 'You have successfully edited (' . $table_num_name . ') table.';

            $connection->query($sql);

            //update reservation and preorder if disable a table
            if ($status == '0'){
                $reservations_array = getReservations($connection, $_SESSION['login_rest_id'], ['table_id' => $_GET['table_id'], 'status' => 1, 'not_remarks' => 'Ongoing']);

                if ($reservations_array){
                    foreach ($reservations_array as $reservations_info){
                        if (!empty($reservations_info['remarks']))
                            $remarks = $reservations_info['remarks'] . "<br>Selected table is currently unavailable";
                        else
                            $remarks = "Selected table is currently unavailable";

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
        } else {
            $sql = "INSERT INTO `table`(rest_id, table_num_name, capacity, `status`, sort_order) VALUES ('" . $_SESSION['login_rest_id'] . "','" . $table_num_name . "','" .  $capacity . "','" . $status . "','" . $sort_order . "')";
            $success_msg = 'You have successfully added (' . $table_num_name . ') table.';

            $connection->query($sql);

            //update time slot table array
            $time_slots_results = getTimeSlots($connection, $_SESSION['login_rest_id']);
            if ($time_slots_results){
                $new_inserted_table_id = $connection->query('SELECT LAST_INSERT_ID() AS id')->fetch_array(MYSQLI_ASSOC)['id'];

                foreach ($time_slots_results as $time_slots_result){
                    $table_array = json_decode($time_slots_result['tables'], true);
                    $table_array[$new_inserted_table_id] = '1';
                    
                    $sql2 = "UPDATE `time_slot` SET `tables` = '" . json_encode($table_array) . "' WHERE time_slot_id = '" . $time_slots_result['time_slot_id'] . "'";
                    $connection->query($sql2);
                }
            }
        }

        $_SESSION['success'] = $success_msg;
        header("Location: ../pages/reservation/table.php");
        exit;
    }

    //delete table
    if (isset($_POST['selected_group_table'])){
        
        session_start();

        include_once '../../../db_connect.php';
        include_once '../helpers/reservation.php';
        include_once '../helpers/time_slot.php';
        include_once '../helpers/order.php';
        include_once '../../../session.php';

        updateLastActivity();

        date_default_timezone_set("Asia/Kuala_Lumpur");

        $selected_groups = $_POST['selected_group_table'];
        $counter = 0;
        foreach ($selected_groups as $selected_group){
            $the_table_name = getTable($connection, $selected_group)['table_num_name'];

            $sql = "DELETE FROM `table` WHERE table_id = '" . $selected_group . "'";
            $connection->query($sql);

            //update reservation and preorder if delete a table
            $reservations_array = getReservations($connection, $_SESSION['login_rest_id'], ['table_id' => $selected_group, 'status' => 1, 'not_remarks' => 'Ongoing']);

            if ($reservations_array){
                foreach ($reservations_array as $reservations_info){
                    if (!empty($reservations_info['remarks']))
                        $remarks = $reservations_info['remarks'] . "<br>Selected table is no longer available";
                    else
                        $remarks = "Selected table is no longer available";
                    

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

            //save the table name to relevant reservation and order that having this table
            $reservations_array_2 = getReservations($connection, $_SESSION['login_rest_id'], ['table_id' => $selected_group]);
            if ($reservations_array_2){
                foreach ($reservations_array_2 as $reservations_info){
                    $deleted_data = json_decode($reservations_info['deleted_data'], true);
                    $deleted_data['table_num_name'] = $the_table_name;

                    $sql4 = "UPDATE `reservation` SET deleted_data = '" . json_encode($deleted_data) . "' WHERE reservation_id = '" . $reservations_info['reservation_id'] . "'";
                    $connection->query($sql4);
                }
            }

            $orders_array = getOrders($connection, $_SESSION['login_rest_id'], ['table_id' => $selected_group]);

            if ($orders_array){
                foreach ($orders_array as $orders_info){
                    $deleted_data = json_decode($orders_info['deleted_data'], true);
                    $deleted_data['table_num_name'] = $the_table_name;

                    $sql4 = "UPDATE `order` SET deleted_data = '" . json_encode($deleted_data) . "' WHERE order_id = '" . $orders_info['order_id'] . "'";
                    $connection->query($sql4);
                }
            }

            //update time slot table array
            $time_slots_results = getTimeSlots($connection, $_SESSION['login_rest_id']);
            if ($time_slots_results){
                foreach ($time_slots_results as $time_slots_result){
                    $table_array = json_decode($time_slots_result['tables'], true);
                    unset($table_array[$selected_group]);
                    
                    $sql3 = "UPDATE `time_slot` SET `tables` = '" . json_encode($table_array) . "' WHERE time_slot_id = '" . $time_slots_result['time_slot_id'] . "'";
                    $connection->query($sql3);
                }
            }

            $counter++;
        }
        
        $_SESSION['success'] = "You have successfully deleted " . (string)$counter . " table(s)."; 
        exit;
    }

?>