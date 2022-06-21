<?php

    if (isset($_POST['generate_chart'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../helpers/order.php';
        include_once '../helpers/reservation.php';
        include_once '../helpers/restaurant.php';
        include_once '../helpers/food_menu.php';
        include_once '../../../session.php';

        updateLastActivity();

        $months = array(
            'Jan'  => '01', 
            'Feb'  => '02', 
            'Mar'  => '03', 
            'Apr'  => '04', 
            'May'  => '05', 
            'Jun'  => '06', 
            'July' => '07', 
            'Aug'  => '08', 
            'Sept' => '09', 
            'Oct'  => '10', 
            'Nov'  => '11', 
            'Dec'  => '12'
        );

        $results = [];

        if ($_POST['type'] == 'Orders'){
            foreach ($months as $key => $month){
                $year_month = (string)$_POST['year'] . '-' . $month;
    
                $orders = getOrders($connection, $_SESSION['login_rest_id'], ['created_date' => $year_month, 'not_statuses' => ['Cancelled', 'Disabled']]);
    
                $results[] = array(
                    'y'     => count($orders),
                    'label' => $key
                );
            }
        } elseif ($_POST['type'] == 'Reservations'){
            foreach ($months as $key => $month){
                $year_month = (string)$_POST['year'] . '-' . $month;
    
                $reservations = getReservations($connection, $_SESSION['login_rest_id'], ['created_date' => $year_month, 'not_statuses' => [0, 2]]);
    
                $results[] = array(
                    'y'     => count($reservations),
                    'label' => $key
                );
            }
        } elseif ($_POST['type'] == 'Sharings'){
            foreach ($months as $key => $month){   
                $sharing_count = getSharingCount($connection, $_SESSION['login_rest_id'], (int)$month, (int)$_POST['year']);
    
                if (!empty($sharing_count))
                    $count = (int)$sharing_count['count'];
                else
                    $count = 0;

                $results[] = array(
                    'y'     => $count,
                    'label' => $key
                );
            }
        } elseif ($_POST['type'] == 'Visitors'){
            foreach ($months as $key => $month){   
                $visitor_count = getVisitorCount($connection, $_SESSION['login_rest_id'], (int)$month, (int)$_POST['year']);
    
                if (!empty($visitor_count))
                    $count = (int)$visitor_count['count'];
                else
                    $count = 0;

                $results[] = array(
                    'y'     => $count,
                    'label' => $key
                );
            }
        } elseif ($_POST['type'] == 'Earning'){
            $food_items = getFoodMenuItems($connection, $_SESSION['login_rest_id']);
            $food_prices = array_column($food_items, 'price', 'item_id');

            foreach ($months as $key => $month){   
                $year_month = (string)$_POST['year'] . '-' . $month;

                $orders = getOrders($connection, $_SESSION['login_rest_id'], ['created_date' => $year_month, 'status' => 'Completed']);

                $total = 0.0;

                foreach($orders as $order){
                    $temp_total = 0.0;

                    $item_qty = json_decode($order['item_quantity'], true);
                    foreach ($item_qty as $item => $qty){
                        $temp_total += ($food_prices[$item]) * (int)$qty;
                    }

                    //deduct if has promotion code/promotion applied
                    if (!empty($order['promotions'])){
                        $promotions = json_decode($order['promotions'], true);
                        foreach ($promotions as $promo_key => $promotion){
                            if ($promo_key === 'promo_code'){
                                $temp_total -= (double)$promotion['actual_discount'];
                            } elseif ($promotion['type_code'] == 'dollar_dis'){
                                foreach ($promotion['food_items'] as $food_id){
                                    $discounted_price = (double)$promotion['settings_data']['discounted_price'];
                                    $qty = (int)$item_qty[$food_id];
                                    $temp_total -= ($food_prices[$food_id] * $qty);
                                    $temp_total += ($discounted_price * $qty);
                                }
                            }elseif ($promotion['type_code'] == 'percent_off'){
                                $percentage = (double)$promotion['settings_data']['percentage'] / 100;
                                foreach ($promotion['food_items'] as $food_id){
                                    $discount = $food_prices[$food_id] * $percentage;
                                    $qty = (int)$item_qty[$food_id];
                                    $temp_total -= ($discount * $qty);
                                }
                            }
                        }
                    }

                    //deduct if has voucher
                    if (!empty($order['equal_price'])){
                        $temp_total -= $order['equal_price'];
                        if ($temp_total < 0){
                            $temp_total = 0.0;
                        }
                    }

                    $temp_total = round($temp_total, 2);
                    $total += $temp_total;
                }

                $results[] = array(
                    'y'     => $total,
                    'label' => $key
                );
            }
        }

        $final_result['labels'] = array_column($results, 'label');
        $final_result['data'] = array_column($results, 'y');
        
        echo json_encode($final_result);
        exit;
    }

    if (isset($_POST['update_dashboard_data'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../helpers/order.php';
        include_once '../helpers/reservation.php';
        include_once '../helpers/restaurant.php';
        include_once '../helpers/customer.php';
        include_once '../helpers/food_menu.php';

        date_default_timezone_set("Asia/Kuala_Lumpur");

        $result = [];

        $orders = getOrders($connection, $_SESSION['login_rest_id'], ['not_statuses' => ['Completed', 'Cancelled', 'Disabled']]);
        $result['order'] = count($orders);

        $reservations = getReservations($connection, $_SESSION['login_rest_id'], array('status' => 1, 'not_remarks' => 'Ongoing'));
        $result['reservation'] = count($reservations);

        $customers = getRestaurantCustomers($connection, $_SESSION['login_rest_id']);
        $result['customer'] = count($customers);

        $share_counts = getSharingCount($connection, $_SESSION['login_rest_id'], date('m'), date('Y'));
        $result['share'] = !empty($share_counts) ? $share_counts['count'] : 0;

        $visitor_counts = getVisitorCount($connection, $_SESSION['login_rest_id'], date('m'), date('Y'));
        $result['visitor'] = !empty($visitor_counts) ? $visitor_counts['count'] : 0;

        $food_items = getFoodMenuItems($connection, $_SESSION['login_rest_id']);
        $result['food'] = count($food_items);

        echo json_encode($result);
        exit;
    }
    

?>