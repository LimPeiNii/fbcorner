<?php

    function getReservations($connection, $rest_id, $filter_data = array()){
        $sql = "SELECT *, r.status AS reservation_status, ts.start_time AS startTime, tts.end_time AS endTime, SUBSTRING(JSON_EXTRACT(r.deleted_data, '$.startTime'), 2, 8) AS second_startTime FROM `reservation` r LEFT JOIN time_slot ts ON (CAST(ts.time_slot_id AS CHAR) = (JSON_EXTRACT(r.time_slot_id, '$[0]'))) LEFT JOIN time_slot tts ON (CAST(tts.time_slot_id AS CHAR) = (JSON_EXTRACT(r.time_slot_id, CONCAT('$[', (JSON_LENGTH(r.time_slot_id)-1), ']')))) WHERE r.rest_id = '" . $rest_id . "'";

        if (isset($filter_data['cus_id'])){
            $sql .= " AND r.cus_id = '" . $filter_data['cus_id'] . "'";
        }

        if (isset($filter_data['date'])){
            $sql .= " AND `date` = '" . $filter_data['date'] . "'";
        }

        if (isset($filter_data['time_slot_id'])){
            $sql .= " AND JSON_VALID(r.time_slot_id) AND JSON_CONTAINS(r.time_slot_id, '\"" . $filter_data['time_slot_id'] . "\"', '$')";
        }
        
        if (isset($filter_data['table_id'])){
            $sql .= " AND r.table_id = '" . $filter_data['table_id'] . "'";
        }

        if (isset($filter_data['status'])){
            $sql .= " AND r.status = '" . $filter_data['status'] . "'";
        }

        if (isset($filter_data['not_statuses'])){
            $sql .= " AND r.status NOT IN (" . $filter_data['not_statuses'][0] . "";

            foreach ($filter_data['not_statuses'] as $key => $not_status){
                if ($key != 0)
                    $sql .= "," . $not_status . "";
            }

            $sql .= ")";
        }

        if (isset($filter_data['created_date'])){
            $sql .= " AND r.created_date LIKE '" . $filter_data['created_date'] . "%'";
        }

        if (isset($filter_data['not_remarks'])){
            $sql .= " AND r.remarks != '" . $filter_data['not_remarks'] . "'";
        }

        if (isset($filter_data['remarks'])){
            $sql .= " AND r.remarks = '" . $filter_data['remarks'] . "'";
        }

        if (isset($filter_data['not_reservation_id'])){
            $sql .= " AND r.reservation_id != '" . $filter_data['not_reservation_id'] . "'";
        }

        if (isset($filter_data['no_deleted_time_data'])){
            $sql .= " AND JSON_VALID(r.deleted_data) AND JSON_CONTAINS_PATH(r.deleted_data, 'all', '$.\"startTime\"') = 0 AND JSON_CONTAINS_PATH(r.deleted_data, 'all', '$.\"endTime\"') = 0";
        }

        if (isset($filter_data['group_by'])){
            $sql .= " GROUP BY " . $filter_data['group_by'];
        }

        if (isset($filter_data['order_by']) && isset($filter_data['asc_desc'])){
            $sql .= " ORDER BY " . $filter_data['order_by'] . " " . $filter_data['asc_desc'];
        }

        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    function getReservation($connection, $reservation_id){
        $sql = "SELECT r.*, ts.start_time, ts.end_time, t.table_num_name, CONCAT(c.firstname, ' ', c.lastname) AS customer_name, r.status AS reservation_status, ts.start_time AS startTime, tts.end_time AS endTime FROM `reservation` r LEFT JOIN time_slot ts ON (CAST(ts.time_slot_id AS CHAR) = (JSON_EXTRACT(r.time_slot_id, '$[0]'))) LEFT JOIN time_slot tts ON (CAST(tts.time_slot_id AS CHAR) = (JSON_EXTRACT(r.time_slot_id, CONCAT('$[', (JSON_LENGTH(r.time_slot_id)-1), ']')))) LEFT JOIN customer c ON (c.cus_id = r.cus_id) LEFT JOIN `table` t ON (t.table_id = r.table_id) WHERE r.reservation_id = '" . $reservation_id . "'";

        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        return $results;
    }

    
    function getCustomerReservations($connection, $cus_id){
        $sql = "SELECT r.*, ts.start_time AS startTime, SUBSTRING(JSON_EXTRACT(r.deleted_data, '$.startTime'), 2, 8) AS second_startTime FROM `reservation` r LEFT JOIN time_slot ts ON (CAST(ts.time_slot_id AS CHAR) = (JSON_EXTRACT(r.time_slot_id, '$[0]'))) WHERE cus_id = '" . $cus_id . "' ORDER BY `date` DESC, (CASE WHEN startTime IS NULL THEN second_startTime ELSE startTime END) DESC";
        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    function getReservationFullInfo($connection, $reservation_id){
        $result = getReservation($connection, $reservation_id);

        $reservation['Name'] = $result['customer_name'];
        $reservation['Date'] = date('d/m/Y', strtotime($result['date']));
        $reservation['Time'] = empty($result['startTime']) || empty($result['endTime']) ? (date('h:i a', strtotime(json_decode($result['deleted_data'], true)['startTime'])) . " to " . date('h:i a', strtotime(json_decode($result['deleted_data'], true)['endTime']))) : (date('h:i a', strtotime($result['startTime'])) . " to " . date('h:i a', strtotime($result['endTime'])));
        $reservation['Table Number (Name)'] = empty($result['table_num_name']) ? json_decode($result['deleted_data'], true)['table_num_name'] : $result['table_num_name'];
        $reservation['Special Request'] = $result['additional_notes'] ? $result['additional_notes'] : "-";
        $reservation['Created Date'] = date('d/m/Y h:i a', strtotime($result['created_date']));
        $reservation['Modified Date'] = date('d/m/Y h:i a', strtotime($result['modified_date'])) == $reservation['Created Date'] ? "-" : date('d/m/Y h:i a', strtotime($result['modified_date']));
        $reservation['Status'] = $result['status'] == '0' ? '<span class="badge bg-danger">Cancelled</span>' : ($result['status'] == '1' ? '<span class="badge bg-success">Reserved</span>' : ($result['status'] == '2' ? '<span class="badge bg-secondary">Disabled</span>' : '<span class="badge bg-primary">Completed</span>'));
        $reservation['Remarks'] = $result['remarks'] ? $result['remarks'] : '-';
        
        return json_encode($reservation);
    }

    //get reservation details for popup modal
    if (isset($_POST['id'])){
        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        echo getReservationFullInfo($connection, $_POST['id']);
        
        exit;
    }

    //check preorder exist or not for modal
    if (isset($_POST['reservation_id']) && isset($_POST['check_preorder'])){
        include_once '../../../db_connect.php';
        include_once '../helpers/order.php';
        include_once '../../../session.php';

        updateLastActivity();

        $result = [];

        $preorder_result = getOrdersByReservationID($connection, $_POST['reservation_id'], ['not_statuses' => ['Cancelled', 'Disabled']]);
        $reservation_result = getReservation($connection, $_POST['reservation_id']);

        $result['preorder'] = $preorder_result;
        $result['reservation_remarks'] = $reservation_result['remarks'];
        $result['reservation_status'] = $reservation_result['status'];

        echo json_encode($result);
        exit;
    }

    //delete reservation
    if (isset($_POST['selected_group'])){
        
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $selected_groups = $_POST['selected_group'];
        $counter = 0;
        foreach ($selected_groups as $selected_group){
            $sql = "DELETE FROM `reservation` WHERE reservation_id = '" . $selected_group . "'";
            $connection->query($sql);

            $sql2 = "DELETE FROM `order` WHERE reservation_id = '" . $selected_group . "'";
            $connection->query($sql2);

            $counter++;
        }
        
        $_SESSION['success'] = "You have successfully deleted " . (string)$counter . " reservation(s)."; 
        exit;
    }

    //find available tables for reservation form dropdown
    if (isset($_POST['get_available_tables'])){

        session_start();

        include_once '../../../db_connect.php';
        include_once '../helpers/table.php';
        include_once '../helpers/time_slot.php';
        include_once '../../../session.php';

        updateLastActivity();

        // find tables
        $tables_results = getAvailableTables($connection, $_SESSION['login_rest_id']);
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
                    );

                    if (!empty($_POST['edit_reservation_id']))
                        $filter_data['not_reservation_id'] = $_POST['edit_reservation_id'];
    
                    $result = getReservations($connection, $_SESSION['login_rest_id'], $filter_data);
    
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

    //check form
    if (isset($_POST['submit_check_reservation'])){
            
        session_start();

        include_once '../../../db_connect.php';
        include_once '../helpers/settings.php';
        include_once '../helpers/order.php';
        include_once '../../../session.php';

        updateLastActivity();

        $cus_id   = $_POST['cus_id'];
        $cus_name = trim($_POST['cus_name']);
        $date     = $_POST['date'];
        $time     = $_POST['time'];
        $table_id = $_POST['table_id'];        

        $json = [];

        //error checking
        //customer name
        if (empty($cus_name))
            $json['error']['cus-name'] = "Please enter a customer name!";
            
        //customer id
        if (empty($cus_id) && !empty($cus_name))
            $json['error']['cus-name'] = "This customer is not registered!";
        else {
            //add reservation + set to ongoing -> check if there is other ongoing reservation
            if (empty($_POST['edit_reservation_id'])){
                if ($_POST['ongoing'] == '1'){
                    $ongoing_reservations = getReservations($connection, $_SESSION['login_rest_id'], ['cus_id' => $cus_id, 'remarks' => 'Ongoing']);
                    if ($ongoing_reservations){
                        $json['warning'] = "This customer currently has other ongoing reservation!";
                        echo json_encode($json);
                        exit;
                    }
                }
            } else {
                if ($_POST['ongoing'] == '1'){
                    $ongoing_reservations = getReservations($connection, $_SESSION['login_rest_id'], ['cus_id' => $cus_id, 'remarks' => 'Ongoing', 'not_reservation_id' => $_POST['edit_reservation_id']]);
                    //edit reservation + set to ongoing -> check if there is other ongoing reservation
                    if ($ongoing_reservations){
                        $json['warning'] = "This customer currently has other ongoing reservation!";
                        echo json_encode($json);
                        exit;
                    } else {
                        //check if there is uncompleted order
                        $filter_data = array(
                            'not_statuses' => ['Completed', 'Cancelled', 'Disabled', 'Upcoming'], 
                            'rest_id' => $_SESSION['login_rest_id']
                        );
                            
                        $preorders = getOrdersByReservationID($connection, $_POST['edit_reservation_id'], ['not_statuses' => ['Cancelled', 'Disabled']]);

                        if ($preorders){
                            $filter_data['not_order_id'] = $preorders[0]['order_id'];
                        }

                        $result = getCustomerOrders($connection, $cus_id, $filter_data);

                        if ($result){
                            $json['warning'] = "This customer has uncompleted order!";
                            echo json_encode($json);
                            exit;
                        }
                    }
                }
            }
                
        }
            
        //date
        if (empty($date))
            $json['error']['date'] = "Please select a date!";
        
        //time
        $time_slot_setting = getSetting($connection, $_SESSION['login_rest_id'], 'time_slot');
        if (empty($time_slot_setting))
            $max_time_slot = 0;
        else 
            $max_time_slot = $time_slot_setting['maximum-time-slot'];
            
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
            include_once '../helpers/time_slot.php';

            $start_time = getTimeSlot($connection, $time[0])['start_time'];
            date_default_timezone_set("Asia/Kuala_Lumpur");
            $current_timestamp = strtotime('now');

            $reservation_time = $date . " " . $start_time;
            $reservation_timestamp = strtotime($reservation_time);

            if ($reservation_timestamp <= $current_timestamp)
                $json['warning'] = 'Selected datetime must not be in the past!';
        }

        echo json_encode($json);
        exit;
    }

    //submit form
    if (isset($_POST['submit_form_reservation'])){
        
        session_start();

        include_once '../../../db_connect.php';
        include_once '../helpers/order.php';
        include_once '../helpers/restaurant.php';
        include_once '../../../email/send_email.php';
        include_once '../../../session.php';
        include_once '../../../store/php/helpers/customer.php';

        updateLastActivity();

        $cus_id           = $_POST['cus-id'];
        $date             = $_POST['date'];
        $time             = json_encode($_POST['time']);
        $table_id         = $_POST['table-num-name'];
        $additional_notes = trim($_POST['special-request']);
        $status           = $_POST['status'];
        if (isset($_POST['ongoing']))
            $ongoing = $_POST['ongoing'];
        else
            $ongoing = 0;

        if (isset($_POST['remarks']))
            $remarks = trim($_POST['remarks']);
        elseif ($status == 1 && $ongoing == 1)
            $remarks = 'Ongoing';
        else
            $remarks = '';

        date_default_timezone_set("Asia/Kuala_Lumpur");

        //create or edit reservation
        if (isset($_GET['reservation_id'])){
            $sql = "UPDATE `reservation` SET `date` = '" . $date . "', time_slot_id = '" . $time . "', table_id = '" . $table_id . "', additional_notes = '" . $additional_notes . "', modified_date = '" . date('Y-m-d H:i:s') . "', status = '" . $status . "', remarks = '" . $remarks . "' WHERE reservation_id = '" . $_GET['reservation_id'] . "'";
            $connection->query($sql);
            $success_msg = 'You have successfully edited the reservation.';

            $order_info = getOrdersByReservationID($connection, $_GET['reservation_id'], ['not_statuses' => ['Cancelled', 'Disabled']]);

            if ($order_info){
                $sql2 = "UPDATE `order` SET `table_id` = '" . $table_id . "', modified_date = '" . date('Y-m-d H:i:s') . "'";

                if ($status == '0'){
                    $sql2 .= ", promotions = null, voucher_id = 0, remarks = 'Reservation was cancelled', status = 'Cancelled'";
                }
                if ($remarks == 'Ongoing' && $order_info[0]['status'] == 'Upcoming'){
                    $sql2 .= ", status = 'Pending'";
                } elseif ($status != '0' && $remarks != 'Ongoing' && $order_info[0]['status'] != 'Upcoming') {
                    $sql2 .= ", status = 'Upcoming'";
                }
    
                $sql2 .= " WHERE order_id = '" . $order_info[0]['order_id'] . "'";

                $connection->query($sql2);
            }

            //send email to customer
            //get email body
            $_SESSION['send_email'] = array(
                'rest_id' => $_SESSION['login_rest_id'],
                'cus_id'  => $cus_id,
                'reservation_id' => $_GET['reservation_id'],
                'reservation_data' => getReservationFullInfo($connection, $_GET['reservation_id']),
                'type' => 'edit',
                'reservation_or_preorder' => 'reservation',
                'to' => 'customer'
            );

            //add preorder data
            $preorder_info = getOrdersByReservationID($connection, $_GET['reservation_id'], ['not_statuses' => ['Cancelled', 'Completed', 'Disabled']]);
            if ($preorder_info){
                foreach ($preorder_info as $preorder){
                    $_SESSION['send_email']['order_id'] = $preorder['order_id'];
                    $_SESSION['send_email']['order_data'] = getOrderFullInfo($connection, $preorder['order_id'], 'admin');
                }
            }

            //if cancel reservation also need to show the cancelled preorder data in email
            if ($order_info && $status == '0'){
                $_SESSION['send_email']['order_id'] = $order_info[0]['order_id'];
                $_SESSION['send_email']['order_data'] = getOrderFullInfo($connection, $order_info[0]['order_id'], 'admin');
            }

            $restaurant_name = getRestaurant($connection, $_SESSION['login_rest_id'])['rest_name'];
            $customer_email = getCustomer($connection, $cus_id)['email'];

            ob_start(); //Init the output buffering
            include '../../../email/template/reservation.php'; //Include (and compiles) the given file
            $email_body = ob_get_clean(); //Get the buffer and erase it)
            unset($_SESSION['send_email']);

            $success_msg = sendmail($customer_email, $restaurant_name . ' - Reservation ' . $_GET['reservation_id'] . ' Updated', $email_body, $success_msg);

            //send email to restaurant
            //get email body
            $_SESSION['send_email'] = array(
                'rest_id' => $_SESSION['login_rest_id'],
                'cus_id'  => $cus_id,
                'reservation_id' => $_GET['reservation_id'],
                'reservation_data' => getReservationFullInfo($connection, $_GET['reservation_id']),
                'type' => 'edit',
                'reservation_or_preorder' => 'reservation',
                'to' => 'restaurant'
            );

            if ($preorder_info){
                foreach ($preorder_info as $preorder){
                    $_SESSION['send_email']['order_id'] = $preorder['order_id'];
                    $_SESSION['send_email']['order_data'] = getOrderFullInfo($connection, $preorder['order_id'], 'admin');
                }
            }

            //if cancel reservation also need to show the cancelled reservation data in email
            if ($order_info && $status == '0'){
                $_SESSION['send_email']['order_id'] = $order_info[0]['order_id'];
                $_SESSION['send_email']['order_data'] = getOrderFullInfo($connection, $order_info[0]['order_id'], 'admin');
            }
            
            $restaurant_email = getRestaurant($connection, $_SESSION['login_rest_id'])['email'];
        
            ob_start(); //Init the output buffering
            include '../../../email/template/reservation.php'; //Include (and compiles) the given file
            $email_body = ob_get_clean(); //Get the buffer and erase it)
            unset($_SESSION['send_email']);

            if (!in_array($restaurant_email, ['veganFood@gmail.com', 'thewestern@gmail.com', 'hotchicken@gmail.com', 'thejapanese@gmail.com'])){
                sendmail($restaurant_email, $restaurant_name . ' - Reservation ' . $_GET['reservation_id'] . ' Updated', $email_body, $success_msg);
            }
        } else {
            $sql = "INSERT INTO `reservation`(cus_id, rest_id, `date`, time_slot_id, `table_id`, additional_notes, `status`, remarks) VALUES ('" . $cus_id . "','" . $_SESSION['login_rest_id'] . "','" . $date . "','" .  $time . "','" . $table_id . "','" . $additional_notes . "','" .  $status . "','" .  $remarks . "')";
            $connection->query($sql);

            $inserted_id = $connection->query('SELECT LAST_INSERT_ID() AS id')->fetch_array(MYSQLI_ASSOC)['id'];

            $cus_rest_history_result = getCustomerRestaurantHistory($connection, $cus_id, $_SESSION['login_rest_id']);
            if (count($cus_rest_history_result) == 0){
                $sql2 = "INSERT INTO cus_rest_history(cus_id, rest_id) VALUES ('" . $cus_id . "','" .  $_SESSION['login_rest_id'] . "')";
                $connection->query($sql2);
            }
            
            $success_msg = 'You have successfully added the reservation.';

            //send email to customer
            //get email body
            $_SESSION['send_email'] = array(
                'rest_id' => $_SESSION['login_rest_id'],
                'cus_id'  => $cus_id,
                'reservation_id' => $inserted_id,
                'reservation_data' => getReservationFullInfo($connection, $inserted_id),
                'type' => 'add',
                'reservation_or_preorder' => 'reservation',
                'to' => 'customer'
            );

            $restaurant_name = getRestaurant($connection, $_SESSION['login_rest_id'])['rest_name'];
            $customer_email = getCustomer($connection, $cus_id)['email'];

            ob_start(); //Init the output buffering
            include '../../../email/template/reservation.php'; //Include (and compiles) the given file
            $email_body = ob_get_clean(); //Get the buffer and erase it)
            unset($_SESSION['send_email']);

            $success_msg = sendmail($customer_email, $restaurant_name . ' - Reservation ' . $inserted_id, $email_body, $success_msg);

            //send email to restaurant
            //get email body
            $_SESSION['send_email'] = array(
                'rest_id' => $_SESSION['login_rest_id'],
                'cus_id'  => $cus_id,
                'reservation_id' => $inserted_id,
                'reservation_data' => getReservationFullInfo($connection, $inserted_id),
                'type' => 'add',
                'reservation_or_preorder' => 'reservation',
                'to' => 'restaurant'
            );

            $restaurant_email = getRestaurant($connection, $_SESSION['login_rest_id'])['email'];
        
            ob_start(); //Init the output buffering
            include '../../../email/template/reservation.php'; //Include (and compiles) the given file
            $email_body = ob_get_clean(); //Get the buffer and erase it)
            unset($_SESSION['send_email']);

            if (!in_array($restaurant_email, ['veganFood@gmail.com', 'thewestern@gmail.com', 'hotchicken@gmail.com', 'thejapanese@gmail.com'])){
                sendmail($restaurant_email, $restaurant_name . ' - Reservation ' . $inserted_id, $email_body, $success_msg);
            }
        }

        $_SESSION['success'] = $success_msg;
        header("Location: ../pages/reservation/reservation.php");
        exit;
    }

    if (isset($_POST['update_reservation_list'])){
        session_start();

        include_once '../../../db_connect.php';

        $reservations = getReservations($connection, $_SESSION['login_rest_id']);

        if (count($reservations) > $_POST['total_count'])
            echo true;
        else
            echo false;

        exit;
    }
?>