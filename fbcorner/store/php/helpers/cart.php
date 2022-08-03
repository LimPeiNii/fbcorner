<?php

    function getCarts($connection, $cus_id, $filter_data = array()){
        $sql = "SELECT * FROM `cart` WHERE `cus_id` = '" . $cus_id . "'";

        if (isset($filter_data['placed'])){
            $sql .= " AND placed = '" . $filter_data['placed'] . "'";
        }

        $sql .= " ORDER BY modified_date DESC";

        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    function getUnplacedCart($connection, $cus_id, $rest_id){
        $sql = "SELECT * FROM `cart` WHERE `cus_id` = '" . $cus_id . "' AND rest_id = '" . $rest_id . "' AND placed = '0'";

        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        return $results;
    }

    if (isset($_POST['update_cart'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $sql = "SELECT * FROM `cart` WHERE `cus_id` = '" . $_SESSION['login_cus_id'] . "' AND rest_id = '" . $_POST['rest_id'] . "' AND placed = '0'";

        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        $item_quantity_json = $results['item_quantity'];
        $item_quantity = json_decode($item_quantity_json, true);

        unset($item_quantity[$_POST['food_item_id']]);

        if (count($item_quantity) == 0){
            $sql2 = "DELETE FROM `cart` WHERE cart_id = '" . $results['cart_id'] . "'";
        } else{
            $sql2 = "UPDATE `cart` SET item_quantity = '" . json_encode($item_quantity) . "' WHERE cart_id = '" . $results['cart_id'] . "'";
        }

        $connection->query($sql2);
        
        exit;
    }

    if (isset($_POST['check_pickup_time'])){
        include_once '../../../db_connect.php';
        include_once '../../../session.php';
        include_once '../../../admin/php/helpers/order.php';

        updateLastActivity();

        $json = [];

        $pickup_orders = getOrders($connection, $_POST['rest_id'], ['pickup_time' => $_POST['selected_time'] . ':00', 'not_statuses' => ['Cancelled', 'Disabled']]);

        if (count($pickup_orders) >= 5){
            $json['error'] = 'The pickup slots are full at this time, please select another time.';
        }

        echo json_encode($json);
        exit;
    }

    if (isset($_POST['checkout_cart'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../helpers/customer.php';
        include_once '../../../email/send_email.php';
        include_once '../../../session.php';
        include_once '../../../admin/php/helpers/promotion.php';
        include_once '../../../admin/php/helpers/order.php';
        include_once '../../../admin/php/helpers/restaurant.php';
        include_once '../../../admin/php/helpers/reservation.php';

        updateLastActivity();
        
        date_default_timezone_set("Asia/Kuala_Lumpur");

        $sql = "SELECT * FROM `order` WHERE cus_id = '" . $_SESSION['login_cus_id'] . "' AND rest_id = '" . $_POST['rest_id'] . "' AND (status != 'Completed' AND status != 'Cancelled' AND status != 'Disabled' AND status != 'Upcoming')";
        
        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        if ($results){
            $_SESSION['error'] = 'You have a not completed order in this restaurant! To add more items, please edit your order from <a href="../order/my_order.php">My Orders</a>';
        } else {
            if (empty($_POST['additional_notes']))
                $_POST['additional_notes'] = '';
            
            if (empty($_POST['table_id']))
                $_POST['table_id'] = '0';
            
            if (empty($_POST['pickup_time']))
                $_POST['pickup_time'] = 'null';
            else
                $_POST['pickup_time'] = "'" . date('Y-m-d') . ' ' . $_POST['pickup_time'] . "'" ;

            if (isset($_POST['reservation_id']))
                $reservation_id = $_POST['reservation_id'];
            else
                $reservation_id = 0;

            if (isset($_POST['voucher_id']))
                $voucher_id = $_POST['voucher_id'];
            else
                $voucher_id = 0;

            $sql = "SELECT * FROM `cart` WHERE `cus_id` = '" . $_SESSION['login_cus_id'] . "' AND rest_id = '" . $_POST['rest_id'] . "' AND placed = '0'";

            $statement = $connection->query($sql);
            $results = $statement->fetch_array(MYSQLI_ASSOC);

            $item_quantity = $results['item_quantity'];

            //update cart table
            $sql2 = "UPDATE `cart` SET placed = '1', modified_date = '" . date('Y-m-d H:i:s') . "' WHERE cart_id = '" . $results['cart_id'] . "'";

            $connection->query($sql2);

            //promo code
            if (isset($_POST['promo_code_id'])){
                $promotion_info = getPromotion($connection, $_POST['promo_code_id']);
                $settings_data = json_decode($promotion_info['settings_data'], true);
                $promotions['promo_code']['id'] = $promotion_info['promotion_id'];
                $promotions['promo_code']['promo_code_name'] = $settings_data['promo_code_name'];
                $promotions['promo_code']['actual_discount'] = $settings_data['actual_discount'];
                $promotions = json_encode($promotions);
            }

            if (!isset($promotions)){
                $sql3 = "INSERT INTO `order`(rest_id, cus_id, `table_id`, pickup_time, item_quantity, voucher_id, reservation_id, `status`, additional_notes, remarks) VALUES ('" . $_POST['rest_id'] . "','" . $_SESSION['login_cus_id'] . "','" . $_POST['table_id'] . "'," . $_POST['pickup_time'] . ",'" .  $item_quantity . "','" . $voucher_id . "', " . (int)$reservation_id . ",'Pending','" . $_POST['additional_notes'] . "','')";
            } else {
                $sql3 = "INSERT INTO `order`(rest_id, cus_id, `table_id`, pickup_time, item_quantity, voucher_id, reservation_id, `status`, additional_notes, remarks, promotions) VALUES ('" . $_POST['rest_id'] . "','" . $_SESSION['login_cus_id'] . "','" . $_POST['table_id'] . "'," . $_POST['pickup_time'] . ",'" .  $item_quantity . "','" . $voucher_id . "'," . (int)$reservation_id . ",'Pending','" . $_POST['additional_notes'] . "','','" . $promotions . "')";
            }

            $connection->query($sql3);
            $inserted_id = $connection->insert_id;

            //update cus_rest_history
            $cus_rest_history_result = getCustomerRestaurantHistory($connection, $_SESSION['login_cus_id'], $_POST['rest_id']);
            if (count($cus_rest_history_result) == 0){
                $sql4 = "INSERT INTO cus_rest_history(cus_id, rest_id) VALUES ('" . $_SESSION['login_cus_id'] . "','" .  $_POST['rest_id'] . "')";
                $connection->query($sql4);
            }

            //send email to customer
            //get email body
            $_SESSION['send_email'] = array(
                'rest_id' => $_POST['rest_id'],
                'cus_id'  => $_SESSION['login_cus_id'],
                'order_id' => $inserted_id,
                'order_data' => getOrderFullInfo($connection, $inserted_id, 'store'),
                'type' => 'add',
                'to' => 'customer'
            );

            if (isset($_POST['reservation_id'])){    
                //add reservation data
                $_SESSION['send_email']['reservation_id'] = $_POST['reservation_id'];
                $_SESSION['send_email']['reservation_data'] = getReservationFullInfo($connection, $_POST['reservation_id']);
                $_SESSION['send_email']['reservation_or_preorder'] = 'preorder';
            }

            $restaurant_name = getRestaurant($connection, $_POST['rest_id'])['rest_name'];
            $customer_email = getCustomer($connection, $_SESSION['login_cus_id'])['email'];
        
            ob_start(); //Init the output buffering
            if (isset($_POST['reservation_id'])){
                include '../../../email/template/reservation.php'; //Include (and compiles) the given file
            } else {
                include '../../../email/template/order.php'; //Include (and compiles) the given file
            }
            $email_body = ob_get_clean(); //Get the buffer and erase it)
            unset($_SESSION['send_email']);

            if (isset($_POST['reservation_id'])){
                sendmail($customer_email, $restaurant_name . ' - Reservation ' . $_POST['reservation_id'] . ' Updated', $email_body, '');
            } else {
                sendmail($customer_email, $restaurant_name . ' - Order ' . $inserted_id, $email_body, '');
            }

            //send email to restaurant
            //get email body
            $_SESSION['send_email'] = array(
                'rest_id' => $_POST['rest_id'],
                'cus_id'  => $_SESSION['login_cus_id'],
                'order_id' => $inserted_id,
                'order_data' => getOrderFullInfo($connection, $inserted_id, 'store'),
                'type' => 'add',
                'to' => 'restaurant'
            );

            if (isset($_POST['reservation_id'])){    
                //add reservation data
                $_SESSION['send_email']['reservation_id'] = $_POST['reservation_id'];
                $_SESSION['send_email']['reservation_data'] = getReservationFullInfo($connection, $_POST['reservation_id']);
                $_SESSION['send_email']['reservation_or_preorder'] = 'preorder';
            }

            $restaurant_email = getRestaurant($connection, $_POST['rest_id'])['email'];
        
            ob_start(); //Init the output buffering
            if (isset($_POST['reservation_id'])){
                include '../../../email/template/reservation.php'; //Include (and compiles) the given file
            } else {
                include '../../../email/template/order.php'; //Include (and compiles) the given file
            }
            $email_body = ob_get_clean(); //Get the buffer and erase it)
            unset($_SESSION['send_email']);

            if (!in_array($restaurant_email, ['veganFood@gmail.com', 'thewestern@gmail.com', 'hotchicken@gmail.com', 'thejapanese@gmail.com'])){
                if (isset($_POST['reservation_id'])){
                    sendmail($restaurant_email, $restaurant_name . ' - Reservation ' . $_POST['reservation_id'] . ' Updated', $email_body, '');
                } else {
                    sendmail($restaurant_email, $restaurant_name . ' - Order ' . $inserted_id, $email_body, '');
                }
            }

            $_SESSION['success'] = 'You have successfully placed the order. <a href="../order/my_order.php">View</a>';
        }
        exit;
    }

?>