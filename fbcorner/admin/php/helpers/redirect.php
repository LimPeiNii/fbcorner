<?php

    session_start();

    include_once 'user.php';
    include_once '../../../db_connect.php';
    include_once 'user_group.php';

    $current_user = getUser($connection, $_SESSION['login_user_id']);
    $current_user_group = getUserGroup($connection, $current_user['user_group_id']);
    $access_permission_ids = json_decode($current_user_group['access_permission']);
    $access_permission_file = '';
    $to_load_pages = array(
        '1'  => 'dashboard/dashboard',
        '4'  => 'reservation/table',
        '5'  => 'reservation/time_slot',
        '6'  => 'reservation/reservation',
        '10' => 'order/order',
        '11' => 'order/preorder',
        '18' => 'calendar/calendar',
        '9'  => 'food_menu/food_menu',
        '14' => 'message/message',
        '13' => 'reviews/reviews',
        '8'  => 'inventory/inventory',
        '12' => 'promotion/promotion',
        '15' => 'promotion/promotional_ads',
        '17' => 'customer/customer',
        '16' => 'staff/staff',
        '2'  => 'user/user_group',
        '3'  => 'user/user',
        '7'  => 'restaurant/restaurant'
    );

    $access_permission_file = 'dashboard/dashboard';
    foreach ($to_load_pages as $key => $to_load_page){
        if (in_array((int)$key, $access_permission_ids)){
            $access_permission_file = $to_load_page;
            $_SESSION['redirect'] = $access_permission_file;
            break;
        }
    }

    header("Location: ../pages/" . $access_permission_file . ".php");

?>