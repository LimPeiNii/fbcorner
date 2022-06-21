<?php

    include_once '../../helpers/user.php';
    include_once '../../../../db_connect.php';
    include_once '../../helpers/user_group.php';
    include_once '../../helpers/restaurant.php';

    $current_user = getUser($connection, $_SESSION['login_user_id']);
    $current_user_group = getUserGroup($connection, $current_user['user_group_id']);
    $permission_files = getPermissionFiles($connection);
    $access_permission_ids = json_decode($current_user_group['access_permission']);
    $access_permission_files = [];

    foreach ($permission_files as $permission_file){
        if (in_array($permission_file['permission_id'], $access_permission_ids)){
            $access_permission_files[$permission_file['permission_id']] = $permission_file['file_name'];
        }
    }

    $side_bar_elements = array(
        'Dashboard'   => array('fas fa-th-large', 'dashboard/dashboard'),
        'Reservation' => array('fas fa-calendar-check', 3, array(
            'Table'                 => 'reservation/table',
            'Time Slot'             => 'reservation/time_slot',
            'Reservation'           => 'reservation/reservation'
        )),
        'Order'       => array('fas fa-clipboard', 2, array(
            'Dine-In &amp; Self Pickup' => 'order/order',
            'Pre-Order'             => 'order/preorder'
        )),
        'Calendar'    => array('fas fa-calendar-alt', 'calendar/calendar'),
        'Food Menu'   => array('fas fa-book', 'food_menu/food_menu'),
        'Message'     => array('fas fa-comments', 'message/message'),
        'Reviews'     => array('fas fa-star-half-alt', 'reviews/reviews'),
        'Inventory'   => array('fas fa-box', 'inventory/inventory'),
        'Promotion'   => array('fas fa-bullhorn', 3 ,array(
            'Promotion'             => 'promotion/promotion',
            'Email'                 => 'promotion/email',
            'Promotional Ads'       => 'promotion/promotional_ads'
        )),
        'Customer'    => array('fas fa-user-friends', 'customer/customer'),
        'Staff'       => array('fas fa-user-tie', 'staff/staff'),
        'User'        => array('fas fa-users-cog', 2, array(
            'User Group'            => 'user/user_group',
            'User'                  => 'user/user'
        )),
        'Restaurant'  => array('fas fa-info-circle', 'restaurant/restaurant')
    );

    $restaurant = getRestaurant($connection, $_SESSION['login_rest_id']);

?>


<div class="side-bar">
    <div class="profile-content">
        <div class="font-size-22 text-start ps-3 fw-bold"><?=$restaurant['rest_name']?></div>
        <div class="profile">
            <img src="../../../uploads/profile_pic/<?=$restaurant['rest_profile']?>" height="50px" alt="">
            <div class="name_user-group">
                <div class="user-name font-size-20"><?=$current_user['username']?></div>
                <div class="user-group font-size-13"><?=$current_user_group['user_group_name']?></div>
            </div>
        </div>
    </div>
    <ul class="navlist">
        <?php foreach ($side_bar_elements as $key => $value) { ?>
            <?php if (count($value) == 2) { ?>
                <?php if (in_array($value[1], $access_permission_files)) { ?>
                <li class="font-size-18" id="<?=strpos($key, ' ') > 0 ? strtolower(implode('_', explode(' ', $key))) : strtolower($key)?>">
                    <a href="../<?=$value[1]?>.php">
                        <i class="<?=$value[0]?>"></i>
                        <span class="nav-element-name"><?=$key?></span>
                    </a>
                    <span class="tool-tip"><?=$key?></span>
                </li>
                <?php } ?>
            <?php } else { ?>
                <?php 
                    $counter = 0;
                    foreach($value[2] as $subvalue){
                        if (in_array($subvalue, $access_permission_files)){
                            $counter++;
                        }
                    }
                ?>
                <?php if ($counter > 0) { ?>
                    <div class="dropdown-parent-container">
                        <li class="font-size-18" id="<?=strtolower($key)?>">
                            <button class="dropdown-btn text-start">
                                <i class="<?=$value[0]?>"></i>
                                <span class="nav-element-name"><?=$key?></span>
                                <i class="fa fa-caret-down"></i>
                            </button>
                            <span class="tool-tip"><?=$key?></span>
                        </li>
                        <div class="dropdown-container <?=$key?> font-size-16">
                            <?php foreach($value[2] as $subkey => $subvalue) { ?>
                                <?php if (in_array($subvalue, $access_permission_files)) { ?>
                                <li>
                                    <a href="../<?=$subvalue?>.php">
                                        <span><?=$subkey?></span>
                                    </a>
                                    <span class="tool-tip"><?=$subkey?></span>
                                </li>
                                <?php } ?>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>
        <?php } ?>
    </ul>
</div>