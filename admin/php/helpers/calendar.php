<?php

    function getEvents($connection, $rest_id, $filter_data = array()){
        $sql = "SELECT * FROM `event` WHERE `rest_id` = '" . $rest_id . "'";

        if (isset($filter_data['closed_type'])){
            $sql .= " AND closed_type = " . $filter_data['closed_type'];
        }

        if (isset($filter_data['not_recurring_event'])){
            $sql .= " AND repeat_days IS NULL";
        }

        if (isset($filter_data['recurring_event'])){
            $sql .= " AND repeat_days IS NOT NULL";
        }

        if (isset($filter_data['check_restaurant_closed_reservation']) && isset($filter_data['not_recurring_event'])){
            $sql .= " AND ((DATE(`start_date`) <= '" . $filter_data['date'] . "' AND DATE(`end_date`) >= '" . $filter_data['date'] . "' AND (start_time IS NULL AND end_time IS NULL)) OR ((start_time IS NOT NULL AND end_time IS NOT NULL) AND ((CONCAT(start_date, ' ', start_time) <= '" . $filter_data['date'] . " " . $filter_data['start_time'] . "' AND CONCAT(end_date, ' ', end_time) >= '" . $filter_data['date'] . " " . $filter_data['start_time'] . "') OR (CONCAT(start_date, ' ', start_time) <= '" . $filter_data['date'] . " " . $filter_data['end_time'] . "' AND CONCAT(end_date, ' ', end_time) >= '" . $filter_data['date'] . " " . $filter_data['end_time'] . "'))))";
        }

        if (isset($filter_data['check_restaurant_closed_reservation']) && isset($filter_data['recurring_event'])){
            $sql .= " AND DATE(`start_date`) <= '" . $filter_data['date'] . "' AND DATE(`end_date`) >= '" . $filter_data['date'] . "' AND (JSON_VALID(repeat_days) AND JSON_CONTAINS(repeat_days, CONCAT('\"', (DAYOFWEEK('" . $filter_data['date'] . "') - 1), '\"'), '$')) AND ((all_day = 1) OR (all_day != 1 AND ((start_time <= '" . $filter_data['start_time'] . "' AND end_time >= '" . $filter_data['start_time'] . "') OR (start_time <= '" . $filter_data['end_time'] . "' AND end_time >= '" . $filter_data['end_time'] . "'))))";
        }

        if (isset($filter_data['check_restaurant_closed_order']) && isset($filter_data['not_recurring_event'])){
            $sql .= " AND ((DATE(`start_date`) <= '" . $filter_data['date'] . "' AND DATE(`end_date`) >= '" . $filter_data['date'] . "' AND all_day = 1) OR (all_day != 1 AND (CONCAT(start_date, ' ', start_time) <= '" . $filter_data['date'] . " " . $filter_data['time'] . "' AND CONCAT(end_date, ' ', end_time) >= '" . $filter_data['date'] . " " . $filter_data['time'] . "')))";
        }

        if (isset($filter_data['check_restaurant_closed_order']) && isset($filter_data['recurring_event'])){
            $sql .= " AND DATE(`start_date`) <= '" . $filter_data['date'] . "' AND DATE(`end_date`) >= '" . $filter_data['date'] . "' AND (JSON_VALID(repeat_days) AND JSON_CONTAINS(repeat_days, CONCAT('\"', (DAYOFWEEK('" . $filter_data['date'] . "') - 1), '\"'), '$')) AND ((all_day = 1) OR (all_day != 1 AND (start_time <= '" . $filter_data['time'] . "' AND end_time >= '" . $filter_data['time'] . "')))";
        }

        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    if (isset($_POST['get_reservation_data'])){
        include_once '../../../db_connect.php';
        include_once '../helpers/reservation.php';
        include_once '../../../session.php';

        updateLastActivity();

        $reservation_data = getReservation($connection, $_POST['reservation_id']);

        $data = array(
            'name' => $reservation_data['customer_name'],
            'table_name' => empty($reservation_data['table_num_name']) ? json_decode($reservation_data['deleted_data'], true)['table_num_name'] : $reservation_data['table_num_name'],
            'special_request' => $reservation_data['additional_notes'] ? $reservation_data['additional_notes'] : "-",
            'status' => $reservation_data['status'] == '0' ? 'Cancelled' : ($reservation_data['status'] == '1' ? 'Reserved' : ($reservation_data['status'] == '2' ? 'Disabled' : 'Completed')),
            'remarks' => $reservation_data['remarks'] ? $reservation_data['remarks'] : '-'
        );

        echo json_encode($data);
        exit;
    }

    if (isset($_POST['get_calendar_data'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../helpers/reservation.php';
        include_once '../../../session.php';

        updateLastActivity();

        date_default_timezone_set("Asia/Kuala_Lumpur");

        $data = [];

        $all_reservations = getReservations($connection, $_SESSION['login_rest_id']);

        if ($all_reservations){
            foreach ($all_reservations as $reservation){
                if (empty($reservation['startTime']))
                    $startTime = json_decode($reservation['deleted_data'], true)['startTime'];
                else
                    $startTime = $reservation['startTime'];

                if (strtotime($reservation['date'] . ' ' . $startTime) > strtotime(date('Y-m-d H:i:s')))
                    $color = 'green';
                else
                    $color = 'red';

                $data[] = array(
                    'id'    => 'reservation-' . $reservation['reservation_id'],
                    'title' => 'Reservation (#' . (string)$reservation['reservation_id'] . ')',
                    'start' => date('Y-m-d', strtotime($reservation['date'])) . 'T' . date('H:i:s', strtotime($startTime)),
                    'color' => $color
                );
            }
        }

        $events = getEvents($connection, $_SESSION['login_rest_id']);

        if ($events){
            $repeat_group_id = 0;
            foreach ($events as $event){
                $array = array(
                    'id'    => 'event-' . $event['event_id'],
                    'title' => $event['title'],
                    'allDay' => $event['all_day'] ? true : false,
                    'color' => $event['color']
                );

                if (!empty($event['repeat_days'])){
                    $repeat_group_id++;
                    $array['groupId'] = $repeat_group_id;
                    $array['daysOfWeek'] = json_decode($event['repeat_days']);
                    
                    if (!$event['all_day'] && !empty($event['start_time'])){
                        $array['startTime'] = date('H:i:s', strtotime($event['start_time']));
                    }
                    if (!$event['all_day'] &&  !empty($event['end_time'])){
                        $array['endTime'] = date('H:i:s', strtotime($event['end_time']));
                    }

                    if (!empty($event['start_date'])){
                        $array['startRecur'] = date('Y-m-d', strtotime($event['start_date']));
                    }

                    if (!empty($event['end_date'])){
                        $array['endRecur'] = date('Y-m-d', strtotime($event['end_date']));
                    }

                    $array['description']['repeat_days'] = $event['repeat_days'];
                } else {
                    $array['start'] = date('Y-m-d', strtotime($event['start_date'])) . 'T' . (empty($event['start_time']) ? '00:00:00' : date('H:i:s', strtotime($event['start_time'])));
                    $array['end'] = date('Y-m-d', strtotime($event['end_date'])) . 'T' . (empty($event['end_time']) ? '00:00:00' : date('H:i:s', strtotime($event['end_time'])));
                }

                if ($event['closed_type'] == 1){
                    $array['description']['closed_type'] = true;
                } else {
                    $array['description']['closed_type'] = false;
                }

                $data[] = $array;
            }
        }

        echo json_encode($data);
        exit;
    }

    if (isset($_POST['submit_add_event']) || isset($_POST['submit_edit_event'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        date_default_timezone_set("Asia/Kuala_Lumpur");

        if (isset($_POST['submit_add_event']))
            $edit_string = '';
        elseif (isset($_POST['submit_edit_event']))
            $edit_string = 'edit-';

        if (isset($_POST[$edit_string . 'close-restaurant'])){
            $title = 'Restaurant Closed';
            $closed_type = 1;
        } else {
            $title = trim($_POST[$edit_string . 'title']);
            $closed_type = 0;
        }

        if (!isset($_POST[$edit_string . 'repeat'])){            
            if (!isset($_POST[$edit_string . 'all-day'])){
                $start_date = "'" . date('Y-m-d', strtotime($_POST[$edit_string . 'start-datetime'])) . "'";
                $start_time = "'" . date('H:i:s', strtotime($_POST[$edit_string . 'start-datetime'])) . "'";
                $end_date = "'" . date('Y-m-d', strtotime($_POST[$edit_string . 'end-datetime'])) . "'";
                $end_time = "'" . date('H:i:s', strtotime($_POST[$edit_string . 'end-datetime'])) . "'";
                $all_day = 0;
            } else {
                $start_date = "'" . $_POST[$edit_string . 'start-datetime'] . "'";
                $start_time = 'null';
                $end_date = "'" . $_POST[$edit_string . 'end-datetime'] . "'";
                $end_time = 'null';
                $all_day = 1;
            }
        } else {
            $start_date = "'" . $_POST[$edit_string . 'start-date'] . "'";
            $end_date = "'" . $_POST[$edit_string . 'end-date'] . "'";
    
            if (!isset($_POST[$edit_string . 'all-day'])){
                $start_time = "'" . $_POST[$edit_string . 'start-time'] . "'";
                $end_time = "'" . $_POST[$edit_string . 'end-time'] . "'";
                $all_day = 0;
            } else {
                $start_time = 'null';
                $end_time = 'null';
                $all_day = 1;
            }
        }

        if (isset($_POST[$edit_string . 'repeat-day'])){
            $repeat_day = "'" . json_encode($_POST[$edit_string . 'repeat-day']) . "'";
        } else {
            $repeat_day = 'null';
        }

        $event_color = $_POST[$edit_string . 'event-color'];

        if (isset($_POST['submit_add_event'])){
            $sql = "INSERT INTO `event`(rest_id, title, closed_type, repeat_days, all_day, `start_date`, end_date, start_time, end_time, color) VALUES ('" . $_SESSION['login_rest_id'] . "','" . $title . "'," .  $closed_type . ", " . $repeat_day . ", " . $all_day . ", " . $start_date . ", " . $end_date . ", " . $start_time . ", " . $end_time . ", '" . $event_color . "')";

            $_SESSION['success'] = "You have successfully added the event.";
        } elseif (isset($_POST['submit_edit_event'])) { 
            $sql = "UPDATE `event` SET title = '" . $title . "', closed_type = " . $closed_type . ", repeat_days = " . $repeat_day . ", all_day = " . $all_day . ", `start_date` = " . $start_date . ", end_date = " . $end_date . ", start_time = " . $start_time . ", end_time = " . $end_time . ", color = '" . $event_color . "' WHERE event_id = " . (int)$_POST['event_id'];

            $_SESSION['success'] = "You have successfully edited the event.";
        }

        $connection->query($sql);
        header("Location: ../pages/calendar/calendar.php");

        exit;
    }

    if (isset($_POST['delete_event_id'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $sql = 'DELETE FROM `event` WHERE event_id = ' . (int)$_POST['delete_event_id'];
        $connection->query($sql);

        $_SESSION['success'] = "You have successfully deleted the event.";
        exit;
    }

?>