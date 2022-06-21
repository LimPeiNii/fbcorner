<?php

    if (isset($_POST['confirm_time_slot'])){
        include_once '../../../db_connect.php';
        include_once '../../../session.php';
        include_once '../../../admin/php/helpers/time_slot.php';

        updateLastActivity();

        $time_slots = json_decode($_POST['confirm_time_slot'], true);
        $start_time = getTimeSlot($connection, $time_slots[0])['start_time'];
        $end_time = getTimeSlot($connection, end($time_slots))['end_time'];

        $start_time = date('h:i a', strtotime($start_time));
        $end_time = date('h:i a', strtotime($end_time));

        echo json_encode(array($start_time, $end_time));
        exit;
    }

    if (isset($_POST['check_past_time_slot'])){
        include_once '../../../db_connect.php';
        include_once '../../../session.php';
        include_once '../../../admin/php/helpers/time_slot.php';

        updateLastActivity();

        $json = [];

        $start_time = getTimeSlot($connection, $_POST['time_slot'])['start_time'];
        date_default_timezone_set("Asia/Kuala_Lumpur");
        $current_timestamp = strtotime('now');

        $reservation_time = $_POST['date'] . " " . $start_time;
        $reservation_timestamp = strtotime($reservation_time);

        if ($reservation_timestamp <= $current_timestamp)
            $json['hours-error'] = 'Selected datetime must not be in the past!';
        
        echo json_encode($json);
        exit;
    }

    if (isset($_POST['reservation_submit'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once 'customer.php';
        include_once '../../../email/send_email.php';
        include_once '../../../session.php';
        include_once '../../../admin/php/helpers/time_slot.php';
        include_once '../../../admin/php/helpers/table.php';
        include_once '../../../admin/php/helpers/reservation.php';
        include_once '../../../admin/php/helpers/order.php';
        include_once '../../../admin/php/helpers/restaurant.php';

        updateLastActivity();

        // assign tables
        $tables_results = getAvailableTablesBySize($connection, $_POST['restaurant'], $_POST['table_size']);
        $available_table_ids = array_column($tables_results, 'table_id');
        foreach ($_POST['time_slot_ids'] as $time_slot_id){
            $time_slot_info = getTimeSlot($connection, $time_slot_id);
            $tables_status = json_decode($time_slot_info['tables'], true);
            foreach ($tables_status as $key => $value){
                if ($value == '0' && in_array($key, $available_table_ids)){
                    unset($available_table_ids[array_search($key, $available_table_ids)]);
                }
            }
        }

        foreach ($available_table_ids as $table_id){
            $counter = 0;
            foreach ($_POST['time_slot_ids'] as $time_slot_id){
                $filter_data = array(
                    'date'         => $_POST['date'],
                    'time_slot_id' => $time_slot_id,
                    'table_id'     => $table_id,
                    'status'       => '1'
                );

                $result = getReservations($connection, $_POST['restaurant'], $filter_data);

                if (count($result) != 0)
                    $counter++;
            }

            if ($counter == 0){
                $assign_table_id = $table_id;
                break;
            }
        }

        $sql = "INSERT INTO reservation(cus_id, rest_id, `date`, time_slot_id, table_id, additional_notes, `status`) VALUES ('" . $_SESSION['login_cus_id'] . "','" .  $_POST['restaurant'] . "','" . $_POST['date'] . "','" . json_encode($_POST['time_slot_ids']) . "','" . $assign_table_id . "','" . $_POST['additional_request'] . "','1')";
        
        $result = $connection->query($sql);
        $reservation_id = $connection->query('SELECT LAST_INSERT_ID() AS id')->fetch_array(MYSQLI_ASSOC)['id'];

        $cus_rest_history_result = getCustomerRestaurantHistory($connection, $_SESSION['login_cus_id'], $_POST['restaurant']);
        if (count($cus_rest_history_result) == 0){
            $sql2 = "INSERT INTO cus_rest_history(cus_id, rest_id) VALUES ('" . $_SESSION['login_cus_id'] . "','" .  $_POST['restaurant'] . "')";
            
            $result = $connection->query($sql2);
        }

        //add order (preorder)
        if (isset($_POST['preorder_food_qty'])){
            if (isset($_POST['voucher_id']))
                $voucher_id = $_POST['voucher_id'];
            else
                $voucher_id = 0;

            $sql3 = "INSERT INTO `order`(rest_id, cus_id, table_id, item_quantity, voucher_id, reservation_id, `status`) VALUES ('" . $_POST['restaurant'] . "','" .  $_SESSION['login_cus_id'] . "','" . $assign_table_id . "','" . json_encode($_POST['preorder_food_qty']) . "','" . $voucher_id . "','" . $reservation_id . "','Upcoming')";

            $connection->query($sql3);
            $preorder_id = $connection->insert_id;
        }

        //send email to customer
        //get email body
        $_SESSION['send_email'] = array(
            'rest_id' => $_POST['restaurant'],
            'cus_id'  => $_SESSION['login_cus_id'],
            'reservation_id' => $reservation_id,
            'reservation_data' => getReservationFullInfo($connection, $reservation_id),
            'type' => 'add',
            'reservation_or_preorder' => 'reservation',
            'to' => 'customer'
        );

        //add preorder data
        if (isset($_POST['preorder_food_qty'])){
            $_SESSION['send_email']['order_id'] = $preorder_id;
            $_SESSION['send_email']['order_data'] = getOrderFullInfo($connection, $preorder_id, 'store');
        }

        $restaurant_name = getRestaurant($connection, $_POST['restaurant'])['rest_name'];
        $customer_email = getCustomer($connection, $_SESSION['login_cus_id'])['email'];

        ob_start(); //Init the output buffering
        include '../../../email/template/reservation.php'; //Include (and compiles) the given file
        $email_body = ob_get_clean(); //Get the buffer and erase it)
        unset($_SESSION['send_email']);

        sendmail($customer_email, $restaurant_name . ' - Reservation ' . $reservation_id, $email_body, '');

        //send email to restaurant
        //get email body
        $_SESSION['send_email'] = array(
            'rest_id' => $_POST['restaurant'],
            'cus_id'  => $_SESSION['login_cus_id'],
            'reservation_id' => $reservation_id,
            'reservation_data' => getReservationFullInfo($connection, $reservation_id),
            'type' => 'add',
            'reservation_or_preorder' => 'reservation',
            'to' => 'restaurant'
        );

        //add preorder data
        if (isset($_POST['preorder_food_qty'])){
            $_SESSION['send_email']['order_id'] = $preorder_id;
            $_SESSION['send_email']['order_data'] = getOrderFullInfo($connection, $preorder_id, 'store');
        }
        
        $restaurant_email = getRestaurant($connection, $_POST['restaurant'])['email'];
    
        ob_start(); //Init the output buffering
        include '../../../email/template/reservation.php'; //Include (and compiles) the given file
        $email_body = ob_get_clean(); //Get the buffer and erase it)
        unset($_SESSION['send_email']);

        sendmail($restaurant_email, $restaurant_name . ' - Reservation ' . $reservation_id, $email_body, '');

        $_SESSION['success'] = 'You have successfully placed the reservation. <a href="../reservation/my_reservation.php">View</a>';

        exit;
    }

    //my reservation page
    if (isset($_POST['get_data_for_edit'])){
        include_once '../../../db_connect.php';
        include_once '../../../session.php';
        include_once '../../../admin/php/helpers/time_slot.php';
        include_once '../../../admin/php/helpers/table.php';
        include_once '../../../admin/php/helpers/reservation.php';

        updateLastActivity();

        $data = [];

        $reservation_info = getReservation($connection, $_POST['reservation_id']);
        $data['reservation_info'] = $reservation_info;

        $time_slots = getTimeSlots($connection, $_POST['restaurant_id']);
        foreach ($time_slots as $key => $time_slot){
            $time_slots[$key]['start_time'] = date('h:i a', strtotime($time_slot['start_time']));
            $time_slots[$key]['end_time'] = date('h:i a', strtotime($time_slot['end_time']));
        }
        $data['time_slots'] = $time_slots;

        $table = getTable($connection, $_POST['table_id']);
        $data['table'] = $table;

        echo json_encode($data);
        exit;
    }

    //find available tables for my reservation page modal table dropdown
    if (isset($_POST['get_available_tables_store'])){

        include_once '../../../db_connect.php';
        include_once '../../../session.php';
        include_once '../../../admin/php/helpers/time_slot.php';
        include_once '../../../admin/php/helpers/table.php';
        include_once '../../../admin/php/helpers/reservation.php';

        updateLastActivity();

        // find tables
        $tables_results = getAvailableTables($connection, $_POST['the_rest_id']);
        foreach ($tables_results as $key => $table){
            $continue = true;
            foreach ($_POST['time'] as $time_slot_id){
                $time_slot_info = getTimeSlot($connection, $time_slot_id);
                $tables_status = json_decode($time_slot_info['tables'], true);
                if ($tables_status[$table['table_id']] == '0'){
                    unset($tables_results[$key]);
                    $continue = false;
                    break;
                }
            }

            //check if booked by someone
            if ($continue){
                $counter = 0;
                foreach ($_POST['time'] as $time_slot_id){
                    $filter_data = array(
                        'date'         => $_POST['date'],
                        'time_slot_id' => $time_slot_id,
                        'table_id'     => $table['table_id'],
                        'status'       => '1',
                        'not_reservation_id' => $_POST['edit_reservation_id']
                    );
    
                    $result = getReservations($connection, $_POST['the_rest_id'], $filter_data);
    
                    if (count($result) != 0)
                        $counter++;
                }
    
                if ($counter != 0)
                    unset($tables_results[$key]);
            }
        }
        
        echo json_encode($tables_results);
        exit;
    }

    if (isset($_POST['cancel_reservation'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../email/send_email.php';
        include_once 'customer.php';
        include_once '../../../session.php';
        include_once '../../../admin/php/helpers/reservation.php';
        include_once '../../../admin/php/helpers/order.php';
        include_once '../../../admin/php/helpers/restaurant.php';

        updateLastActivity();

        date_default_timezone_set("Asia/Kuala_Lumpur");
        
        $sql = "UPDATE `reservation` SET `status` = 0, remarks = '" . $_POST['remarks'] . "', modified_date = '" . date('Y-m-d H:i:s') . "' WHERE reservation_id = '" . $_POST['reservation_id'] . "'";
        $connection->query($sql);

        $reservation_info = getReservation($connection, $_POST['reservation_id']);

        $order_info = getOrdersByReservationID($connection, $_POST['reservation_id'], ['not_statuses' => ['Cancelled', 'Disabled']]);

        if ($order_info){
            $sql2 = "UPDATE `order` SET modified_date = '" . date('Y-m-d H:i:s') . "', remarks = 'Reservation was cancelled', status = 'Cancelled', promotions = null, voucher_id = 0 WHERE order_id = '" . $order_info[0]['order_id'] . "'";

            $connection->query($sql2);
        }

        //send email to customer
        //get email body
        $_SESSION['send_email'] = array(
            'rest_id' => $reservation_info['rest_id'],
            'cus_id'  => $_SESSION['login_cus_id'],
            'reservation_id' => $_POST['reservation_id'],
            'reservation_data' => getReservationFullInfo($connection, $_POST['reservation_id']),
            'type' => 'edit',
            'reservation_or_preorder' => 'reservation',
            'to' => 'customer'
        );

        //if cancel reservation also need to show the cancelled preorder data in email
        if ($order_info){
            $_SESSION['send_email']['order_id'] = $order_info[0]['order_id'];
            $_SESSION['send_email']['order_data'] = getOrderFullInfo($connection, $order_info[0]['order_id'], 'store');
        }

        $restaurant_name = getRestaurant($connection, $reservation_info['rest_id'])['rest_name'];
        $customer_email = getCustomer($connection, $_SESSION['login_cus_id'])['email'];

        ob_start(); //Init the output buffering
        include '../../../email/template/reservation.php'; //Include (and compiles) the given file
        $email_body = ob_get_clean(); //Get the buffer and erase it)
        unset($_SESSION['send_email']);

        sendmail($customer_email, $restaurant_name . ' - Reservation ' . $_POST['reservation_id'] . ' Updated', $email_body, '');

        //send email to restaurant
        //get email body
        $_SESSION['send_email'] = array(
            'rest_id' => $reservation_info['rest_id'],
            'cus_id'  => $_SESSION['login_cus_id'],
            'reservation_id' => $_POST['reservation_id'],
            'reservation_data' => getReservationFullInfo($connection, $_POST['reservation_id']),
            'type' => 'edit',
            'reservation_or_preorder' => 'reservation',
            'to' => 'restaurant'
        );

        //if cancel reservation also need to show the cancelled preorder data in email
        if ($order_info){
            $_SESSION['send_email']['order_id'] = $order_info[0]['order_id'];
            $_SESSION['send_email']['order_data'] = getOrderFullInfo($connection, $order_info[0]['order_id'], 'store');
        }
        
        $restaurant_email = getRestaurant($connection, $reservation_info['rest_id'])['email'];
    
        ob_start(); //Init the output buffering
        include '../../../email/template/reservation.php'; //Include (and compiles) the given file
        $email_body = ob_get_clean(); //Get the buffer and erase it)
        unset($_SESSION['send_email']);

        sendmail($restaurant_email, $restaurant_name . ' - Reservation ' . $_POST['reservation_id'] . ' Updated', $email_body, '');

        $_SESSION['success'] = 'You have successfully cancelled the reservation';
        exit;
    }

    //check form
    if (isset($_POST['submit_check_store'])){

        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';
        include_once '../../../admin/php/helpers/settings.php';
        include_once '../../../admin/php/helpers/reservation.php';

        updateLastActivity();

        $date     = $_POST['date'];
        $time     = $_POST['time'];
        $table_id = $_POST['table_id'];        

        $json = [];

        //error checking
        //date
        if (empty($date))
            $json['error']['date'] = "Please select a date!";
        
        //time
        $reservation = getReservation($connection, $_POST['reservation_id']);
        $max_time_slot = getSetting($connection, $reservation['rest_id'], 'time_slot')['maximum-time-slot'];
        if ($time == '[]')
            $json['error']['time'] = "Please select at lease one time slot!";
        elseif (count($time) > (int)$max_time_slot)
            $json['error']['time'] = "You can only select maximum " . $max_time_slot . " time slots!";
        else{
            for ($i = 1; $i <= count($time)-1; $i++){
                if (((int)$time[$i] - (int)($time[$i-1])) > 1){
                    //error
                    $json['error']['time'] = "You can only select consecutive time!";
                }
            }
        }
        
        //table id
        if ($table_id == '0')
            $json['error']['table-num-name'] = "Please select a table!";

        if (empty($json)){
            include_once '../../../admin/php/helpers/time_slot.php';

            $start_time = getTimeSlot($connection, $time[0])['start_time'];
            date_default_timezone_set("Asia/Kuala_Lumpur");
            $current_timestamp = strtotime('now');

            $reservation_time = $date . " " . $start_time;
            $reservation_timestamp = strtotime($reservation_time);

            if ($reservation_timestamp <= $current_timestamp)
                $json['hours-error'] = 'Selected datetime must not be in the past!';
        }

        echo json_encode($json);
        exit;
    }
    
    //submit form
    if (isset($_POST['submit_form_store_edit_reservation'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../email/send_email.php';
        include_once 'customer.php';
        include_once '../../../session.php';
        include_once '../../../admin/php/helpers/reservation.php';
        include_once '../../../admin/php/helpers/order.php';
        include_once '../../../admin/php/helpers/restaurant.php';

        updateLastActivity();

        $date             = $_POST['date'];
        $time             = json_encode($_POST['time']);
        $table_id         = $_POST['table-num-name'];
        $additional_notes = trim($_POST['special-request']);

        date_default_timezone_set("Asia/Kuala_Lumpur");

        $sql = "UPDATE `reservation` SET `date` = '" . $date . "', time_slot_id = '" . $time . "', table_id = '" . $table_id . "', additional_notes = '" . $additional_notes . "', modified_date = '" . date('Y-m-d H:i:s') . "' WHERE reservation_id = '" . $_POST['reservation_id'] . "'";
        $connection->query($sql);

        $reservation_info = getReservation($connection, $_POST['reservation_id']);

        //send email to customer
        //get email body
        $_SESSION['send_email'] = array(
            'rest_id' => $reservation_info['rest_id'],
            'cus_id'  => $_SESSION['login_cus_id'],
            'reservation_id' => $_POST['reservation_id'],
            'reservation_data' => getReservationFullInfo($connection, $_POST['reservation_id']),
            'type' => 'edit',
            'reservation_or_preorder' => 'reservation',
            'to' => 'customer'
        );

        //add preorder data
        $preorder_info = getOrdersByReservationID($connection, $_POST['reservation_id'], ['not_statuses' => ['Cancelled', 'Completed', 'Disabled']]);
        if ($preorder_info){
            foreach ($preorder_info as $preorder){
                $_SESSION['send_email']['order_id'] = $preorder['order_id'];
                $_SESSION['send_email']['order_data'] = getOrderFullInfo($connection, $preorder['order_id'], 'store');
            }
        }

        $restaurant_name = getRestaurant($connection, $reservation_info['rest_id'])['rest_name'];
        $customer_email = getCustomer($connection, $_SESSION['login_cus_id'])['email'];

        ob_start(); //Init the output buffering
        include '../../../email/template/reservation.php'; //Include (and compiles) the given file
        $email_body = ob_get_clean(); //Get the buffer and erase it)
        unset($_SESSION['send_email']);

        sendmail($customer_email, $restaurant_name . ' - Reservation ' . $_POST['reservation_id'] . ' Updated', $email_body, '');

        //send email to restaurant
        //get email body
        $_SESSION['send_email'] = array(
            'rest_id' => $reservation_info['rest_id'],
            'cus_id'  => $_SESSION['login_cus_id'],
            'reservation_id' => $_POST['reservation_id'],
            'reservation_data' => getReservationFullInfo($connection, $_POST['reservation_id']),
            'type' => 'edit',
            'reservation_or_preorder' => 'reservation',
            'to' => 'restaurant'
        );

        if ($preorder_info){
            foreach ($preorder_info as $preorder){
                $_SESSION['send_email']['order_id'] = $preorder['order_id'];
                $_SESSION['send_email']['order_data'] = getOrderFullInfo($connection, $preorder['order_id'], 'store');
            }
        }
        
        $restaurant_email = getRestaurant($connection, $reservation_info['rest_id'])['email'];
    
        ob_start(); //Init the output buffering
        include '../../../email/template/reservation.php'; //Include (and compiles) the given file
        $email_body = ob_get_clean(); //Get the buffer and erase it)
        unset($_SESSION['send_email']);

        sendmail($restaurant_email, $restaurant_name . ' - Reservation ' . $_POST['reservation_id'] . ' Updated', $email_body, '');

        $_SESSION['success'] = 'You have successfully edited the reservation.';
        header("Location: ../pages/reservation/my_reservation.php");
        exit;
    }

    if (isset($_POST['get_preorder_list_details'])){
        include_once '../../../db_connect.php';
        include_once '../../../session.php';
        include_once '../../../admin/php/helpers/food_menu.php';

        updateLastActivity();

        $preorder_list = $_POST['food_qty'];
        $preorder_list_food_details = [];

        foreach ($preorder_list as $key => $qty) {
            $item_info = getFoodMenuItem($connection, $key);
            $item_info['qty'] = $qty;
            $item_info['total'] = number_format($item_info['price'] * $qty, 2, '.', '');
            $item_info['price'] = number_format($item_info['price'], 2, '.', '');

            $preorder_list_food_details[] = $item_info;
        }

        echo json_encode($preorder_list_food_details);

        exit();
    }

    //to get data for food item details modal
    if (isset($_POST['preorder_item_details'])){

        include_once '../../../db_connect.php';
        include_once '../../../session.php';
        include_once '../../../admin/php/helpers/food_menu.php';
        include_once '../../../admin/php/helpers/category.php';
        include_once '../../../admin/php/helpers/inventory.php';

        updateLastActivity();

        $food_menu_item = getFoodMenuItem($connection, $_POST['preorder_item_id']);

        $category = getCategory($connection, $food_menu_item['category_id']);

        //ingredients
        $ingredients = [];

        if (!empty($food_menu_item['ingredients'])){
            $ingredients_results = json_decode($food_menu_item['ingredients'], true);

            foreach ($ingredients_results as $key => $ingredients_result){
                $ingredients[] = getInventory($connection, $key)['item_name'];
            }
        }

        $final_result = [];

        $final_result['Image'] = $food_menu_item['image'];
        $final_result['Item Name'] = $food_menu_item['item_name'];
        $final_result['Category'] = $category['category_name'];
        $final_result['Description'] = $food_menu_item['description'];
        $final_result['Ingredient'] = $ingredients;
        // promotions (in table form?)

        echo json_encode($final_result);
        exit;
    }

    if(isset($_POST['update_time_slots'])){
        include_once '../../../db_connect.php';
        include_once '../../../session.php';
        include_once '../../../admin/php/helpers/table.php';
        include_once '../../../admin/php/helpers/time_slot.php';
        include_once '../../../admin/php/helpers/reservation.php';

        updateLastActivity();

        $tables_results = getAvailableTablesBySize($connection, $_POST['restaurant'], $_POST['table_size']);
        $available_table_ids = array_column($tables_results, 'table_id');
        foreach ($_POST['time_slot_ids'] as $time_slot_id){
            $time_slot_info = getTimeSlot($connection, $time_slot_id);
            $tables_status = json_decode($time_slot_info['tables'], true);
            foreach ($tables_status as $key => $value){
                if ($value == '0' && in_array($key, $available_table_ids)){
                    unset($available_table_ids[array_search($key, $available_table_ids)]);
                }
            }
        }      
        
        foreach ($available_table_ids as $key => $table_id){
            $counter = 0;
            foreach ($_POST['time_slot_ids'] as $time_slot_id){
                $filter_data = array(
                    'date'         => $_POST['date'],
                    'time_slot_id' => $time_slot_id,
                    'table_id'     => $table_id,
                    'status'       => '1'
                );

                $result = getReservations($connection, $_POST['restaurant'], $filter_data);

                if (count($result) != 0)
                    $counter++;
            }

            if ($counter != 0){
                unset($available_table_ids[$key]);
            }
        }

        $unavailable_time_slots = [];
        $time_slots = getTimeSlots($connection, $_POST['restaurant']);

        foreach ($time_slots as $time_slot){
            if ($time_slot['status'] == '1'){
                $table_left = count($available_table_ids);
                $tables_status = json_decode($time_slot['tables'], true);
    
                //check available tables for this timeslot
                foreach ($available_table_ids as $key => $available_table_id){
                    if ($tables_status[$available_table_id] == '0'){
                        $table_left -= 1;
                    }
                }

                if ($table_left <= 0){
                    $unavailable_time_slots['full'][] = 'time-slot-' . $time_slot['time_slot_id'];
                    continue;
                }
                
                //check if tables already booked
                $filter_data = array(
                    'date'          => $_POST['date'],
                    'time_slot_id'  => $time_slot['time_slot_id'],
                    'status'        => '1'
                );
                $reservations_results = getReservations($connection, $_POST['restaurant'], $filter_data);
    
                if (count($reservations_results) > 0){
                    foreach ($reservations_results as $result){
                        if (in_array($result['table_id'], $available_table_ids))
                            $table_left -= 1;
                    }
                }
    
                if ($table_left <= 0){
                    $unavailable_time_slots['full'][] = 'time-slot-' . $time_slot['time_slot_id'];
                }
    
            } else {
                $unavailable_time_slots['unavailable'][] = 'time-slot-' . $time_slot['time_slot_id'];
            }
        }

        echo json_encode($unavailable_time_slots);
        
        exit;
    }

    if (isset($_POST['check_restaurant_closed'])){
        include_once '../../../db_connect.php';
        include_once '../../../session.php';
        include_once '../../../admin/php/helpers/calendar.php';
        include_once '../../../admin/php/helpers/time_slot.php';

        updateLastActivity();

        $restaurant_closed = 'false';

        $start_time = getTimeSlot($connection, $_POST['time_slot_ids'][0])['start_time'];
        $end_time = getTimeSlot($connection, $_POST['time_slot_ids'][count($_POST['time_slot_ids']) - 1])['end_time'];

        $not_repeat_closed_event = getEvents($connection, $_POST['rest_id'], ['closed_type' => 1, 'not_recurring_event' => true, 'date' => $_POST['date'], 'start_time' => $start_time, 'end_time' => $end_time, 'check_restaurant_closed_reservation' => true]);

        if ($not_repeat_closed_event){
            $restaurant_closed = 'true';
        } else {
            //check for recurring type
            $repeat_closed_event = getEvents($connection, $_POST['rest_id'], ['closed_type' => 1, 'recurring_event' => true, 'date' => $_POST['date'], 'start_time' => $start_time, 'end_time' => $end_time, 'check_restaurant_closed_reservation' => true]);

            if ($repeat_closed_event){
                $restaurant_closed = 'true';
            }
        }
        echo $restaurant_closed;
        exit;
    }
    
    include_once '../../../db_connect.php';
    include_once '../../../session.php';
    include_once '../../../admin/php/helpers/time_slot.php';
    include_once '../../../admin/php/helpers/table.php';
    include_once '../../../admin/php/helpers/reservation.php';
    
    updateLastActivity();

    $time_slots = getTimeSlots($connection, $_POST['visit_rest_id']);
    $tables_results = getAvailableTablesBySize($connection, $_POST['visit_rest_id'], $_POST['table_size']);
    $tables_ids_matched = array_column($tables_results, 'table_id');

    $unavailable_time_slots = array();

    foreach ($time_slots as $time_slot){
        if ($time_slot['status'] == '1'){
            $table_left = count($tables_results);
            $tables_status = json_decode($time_slot['tables'], true);

            //check available tables for this timeslot
            foreach ($tables_results as $tables_result){
                if ($tables_status[$tables_result['table_id']] == '0')
                    $table_left -= 1;
            }
            
            //check if tables already booked
            $filter_data = array(
                'date'          => $_POST['date'],
                'time_slot_id'  => $time_slot['time_slot_id'],
                'status'        => '1'
            );
            $reservations_results = getReservations($connection, $_POST['visit_rest_id'], $filter_data);

            if (count($reservations_results) > 0){
                foreach ($reservations_results as $result){
                    if (in_array($result['table_id'], $tables_ids_matched))
                        $table_left -= 1;
                }
            }

            if ($table_left <= 0){
                $unavailable_time_slots['full'][] = 'time-slot-' . $time_slot['time_slot_id'];
            }

        } else {
            $unavailable_time_slots['unavailable'][] = 'time-slot-' . $time_slot['time_slot_id'];
        }
    }
    
    echo json_encode($unavailable_time_slots);

?>