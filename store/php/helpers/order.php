<?php

    if (isset($_POST['add_to_cart'])){
        include_once '../../../db_connect.php';
        include_once '../helpers/cart.php';
        include_once '../../../session.php';
        include_once '../../../admin/php/helpers/food_menu.php';

        updateLastActivity();

        $json = [];
        
        //check food availability
        $food_item_status = getFoodMenuItem($connection, $_POST['food_item_id'])['status'];
        
        if ($food_item_status == '0'){
            $json['error'] = 'Sorry, this food is currently unavailable.';
        } else {       
            $cart = getUnplacedCart($connection, $_POST['cus_id'], $_POST['rest_id']);

            date_default_timezone_set("Asia/Kuala_Lumpur");

            if ($cart){
                $item_qty = json_decode($cart['item_quantity'], true);
                if (array_key_exists($_POST['food_item_id'], $item_qty)){
                    $item_qty[$_POST['food_item_id']] = (string)((int)($item_qty[$_POST['food_item_id']]) + (int)$_POST['quantity']);
                } else {
                    $item_qty[$_POST['food_item_id']] = $_POST['quantity'];
                }
                $item_qty = json_encode($item_qty);

                $sql = "UPDATE `cart` SET `item_quantity` = '" . $item_qty . "', modified_date = '" . date("Y-m-d H:i:s") . "' WHERE cart_id = '" . $cart['cart_id'] . "'";

                $connection->query($sql);
            } else {
                $item_qty = [$_POST['food_item_id'] => $_POST['quantity']];
                $item_qty = json_encode($item_qty);

                $sql = "INSERT INTO `cart`(cus_id, rest_id, item_quantity, placed) VALUES ('" . $_POST['cus_id'] . "','" . $_POST['rest_id'] . "','" .  $item_qty . "','0')";

                $connection->query($sql);
            }

            $json['success'] = 'You have successfully added this food item into your cart.';
        }

        echo json_encode($json);
        exit;
    }

    if (isset($_POST['get_cart_total'])){
        session_start();
        
        include_once '../../../db_connect.php';
        include_once '../helpers/cart.php';
        include_once '../../../session.php';

        updateLastActivity();

        $carts = getCarts($connection, $_SESSION['login_cus_id'], ['placed' => '0']);
        $total_items = 0;
        foreach ($carts as $cart){
            $cart_items = json_decode($cart['item_quantity'], true);
            $total_items += count($cart_items);
        }

        echo (string)$total_items;
        exit;
    }

    if (isset($_POST['checkout_cart'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $sql = "UPDATE `cart` SET item_quantity = '" . $_POST['item_quantity'] . "' WHERE rest_id = '" . $_POST['rest_id'] . "' AND cus_id = '" . $_SESSION['login_cus_id'] . "'";

        $connection->query($sql);

        exit;
    }

    if (isset($_POST['check_out_checking'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../helpers/cart.php';
        include_once '../../../session.php';
        include_once '../../../admin/php/helpers/food_menu.php';
        include_once '../../../admin/php/helpers/restaurant.php';
        include_once '../../../admin/php/helpers/calendar.php';

        updateLastActivity();

        date_default_timezone_set("Asia/Kuala_Lumpur");

        $json = [];

        $restaurant_info = getRestaurant($connection, $_POST['rest_id']);

        //check if restaurant is closed based on calendar event
        $not_repeat_closed_event = getEvents($connection, $_POST['rest_id'], ['closed_type' => 1, 'not_recurring_event' => true, 'date' => date('Y-m-d'), 'time' => date('H:i:s'), 'check_restaurant_closed_order' => true]);

        if ($not_repeat_closed_event) {
            $json['error'] = 'This restaurant is currently closed, please try again later.';
        } else {
            //check for recurring type
            $repeat_closed_event = getEvents($connection, $_POST['rest_id'], ['closed_type' => 1, 'recurring_event' => true, 'date' => date('Y-m-d'), 'time' => date('H:i:s'), 'check_restaurant_closed_order' => true]);

            if ($repeat_closed_event){
                $json['error'] = 'This restaurant is currently closed, please try again later.';
            }
        }
        
        if (isset($json['error'])){
            echo json_encode($json);
            exit;
        }
          
        //check if restaurant is closed based on default opening and closing time
        if (!(!empty($restaurant_info['opening_time']) && !empty($restaurant_info['closing_time']) && strtotime(date('H:i')) >= strtotime($restaurant_info['opening_time']) && strtotime(date('H:i')) < strtotime($restaurant_info['closing_time']))){
            $json['error'] = 'This restaurant is currently closed, please try again later.';
        } else {
            $cart = getUnplacedCart($connection, $_SESSION['login_cus_id'], $_POST['rest_id']);

            $item_qty = json_decode($cart['item_quantity'], true);
            $item_ids = array_keys($item_qty);

            $unavailable = false;
            $unavailable_foods = [];
            foreach ($item_ids as $item_id){
                $food_item_info = getFoodMenuItem($connection, $item_id);
                $food_item_status = $food_item_info['status'];
                if ($food_item_status == '0'){
                    $unavailable = true;
                    $unavailable_foods[] = $food_item_info['item_name'];
                }
            }

            if ($unavailable){
                $json['error'] = 'Food item(s) in the cart is not available, please check again.<br>';
                foreach ($unavailable_foods as $key => $unavailable_food){
                    $json['error'] .= '<br>' . (string)($key + 1) . '. ' . $unavailable_food;
                }
            }
        }

        echo json_encode($json);
        exit;
    }

    //my order page
    if (isset($_POST['get_data_for_edit'])){
        include_once '../../../db_connect.php';
        include_once '../helpers/voucher.php';
        include_once '../../../session.php';
        include_once '../../../admin/php/helpers/food_menu.php';
        include_once '../../../admin/php/helpers/order.php';
        include_once '../../../admin/php/helpers/table.php';
        include_once '../../../admin/php/helpers/promotion.php';

        updateLastActivity();

        $data = [];

        $order_info = getOrder($connection, $_POST['order_id']);
        $data['order_info'] = $order_info;

        $tables_info = getAvailableTables($connection, $order_info['rest_id']);
        $data['tables_info'] = $tables_info;

        if (!empty($order_info['pickup_time']))
            $data['pickup_time'] = date('H:i', strtotime($order_info['pickup_time']));

        //food items
        $food_qty = json_decode($order_info['item_quantity'], true);
        $total = 0.0;
        foreach ($food_qty as $id => $qty){
            $food_item_result = getFoodMenuItem($connection, $id);

            $promotions = [];
            if ($order_info['status'] != 'Completed' && $order_info['status'] != 'Cancelled' && $order_info['status'] != 'Disabled' && $order_info['status'] != 'Upcoming'){
                //get promotion data from promotion table
                $promotion_infos = getPromotions($connection, $order_info['rest_id'], ['food_item_id' => $id, 'status' => '1']);

                if ($promotion_infos){
                    foreach ($promotion_infos as $promotion_info){
                        if (isset($promotion_info['settings_data'])){
                            $settings_data = json_decode($promotion_info['settings_data'], true);
                        }
                        if ($promotion_info['type_code'] == 'BOGO'){
                            $promotions['buy_free'] = '(Buy 1 Get 1 Free)';
                            $promotions['buy_free_value'] = $qty;
                        } elseif ($promotion_info['type_code'] == 'multi_buy'){
                            $promotions['buy_free'] = '(Buy ' . $settings_data['amount_1'] . ' Get ' . $settings_data['amount_2'] . ' Free)';
                            $promotions['buy_free_value'] = floor($qty / (int)$settings_data['amount_1']) * (int)$settings_data['amount_2'];
                        } elseif ($promotion_info['type_code'] == 'percent_off'){
                            $promotions['discount_price'] = $food_item_result['price'] * (1-((int)$settings_data['percentage'] / 100));
                        } elseif ($promotion_info['type_code'] == 'dollar_dis'){
                            $promotions['discount_price'] = $settings_data['discounted_price'];
                        } 
                    }
                }
            }

            $data['Order'][] = array(
                'food_id' => $food_item_result['item_id'],
                'Name' => $food_item_result['item_name'],
                'Price' => 'RM ' . number_format($food_item_result['price'], 2, '.', ''),
                'Quantity' => $qty,
                'Subtotal' => isset($promotions['discount_price']) ? 'RM ' . number_format(($promotions['discount_price'] * $qty), 2, '.', '') : 'RM ' . number_format(($food_item_result['price'] * $qty), 2, '.', ''),
                'Promotions' => $promotions
            );
            if (isset($promotions['discount_price'])){
                $total += number_format(($promotions['discount_price'] * $qty), 2, '.', '');
            } else {
                $total += number_format(($food_item_result['price'] * $qty), 2, '.', '');
            }
        }
        $data['Order']['Total'] = 'RM ' . number_format($total, 2, '.', '');

        if (!empty($order_info['promotions'])){
            $order_promos = json_decode($order_info['promotions'], true);
            if (isset($order_promos['promo_code'])){
                $data['Order']['promo_code']['id'] = $order_promos['promo_code']['id'];
                $data['Order']['promo_code']['name'] = $order_promos['promo_code']['promo_code_name'];
                $data['Order']['promo_code']['discount'] = '- RM ' . $order_promos['promo_code']['actual_discount'];
            }
        }

        if ($order_info['voucher_id'] != '0'){
            $voucher_info = getVoucher($connection, $order_info['voucher_id']);
            $data['Order']['voucher'] = array(
                'id'    => $voucher_info['voucher_id'],
                'price' => '- RM ' . number_format($voucher_info['equal_price'], 2, '.', '')
            );
        }

        echo json_encode($data);
        exit;
    }

    //update my order edit order modal food list
    if (isset($_POST['update_food_list'])){
        include_once '../../../db_connect.php';
        include_once '../../../session.php';
        include_once '../../../admin/php/helpers/food_menu.php';
        include_once '../../../admin/php/helpers/order.php';
        include_once '../../../admin/php/helpers/category.php';
        include_once '../../../admin/php/helpers/promotion.php';

        updateLastActivity();

        $rest_id = getOrder($connection, $_POST['the_order'])['rest_id'];

        $food_menu_items_results = getFoodMenuItems($connection, $rest_id, ['order_by' => 'item_code', 'asc_desc' => 'ASC']);
        $food_menu_items = [];

        foreach ($food_menu_items_results as $result){
            //promotions
            $promotion_infos = getPromotions($connection, $rest_id, ['food_item_id' => $result['item_id'], 'status' => '1']);

            if ($promotion_infos){
                foreach ($promotion_infos as $promotion_info){
                    if (isset($promotion_info['settings_data'])){
                        $settings_data = json_decode($promotion_info['settings_data'], true);
                    }
                    if ($promotion_info['type_code'] == 'BOGO'){
                        $result['buy_free'] = '(Buy 1 Get 1 Free)';
                    } elseif ($promotion_info['type_code'] == 'multi_buy'){
                        $result['buy_free'] = '(Buy ' . $settings_data['amount_1'] . ' Get ' . $settings_data['amount_2'] . ' Free)';
                    } elseif ($promotion_info['type_code'] == 'percent_off'){
                        $result['discount_price'] = $result['price'] * (1-((int)$settings_data['percentage'] / 100));
                    } elseif ($promotion_info['type_code'] == 'dollar_dis'){
                        $result['discount_price'] = $settings_data['discounted_price'];
                    } 
                }
            }

            $category_name = getCategory($connection, $result['category_id'])['category_name'];
            $food_menu_items[ucwords($category_name)][] = $result;
        }

        $food_menu_items['order_status'] = getOrder($connection, $_POST['the_order'])['status'];

        echo json_encode($food_menu_items);
        exit;
    }

    //update food items in edit order modal
    if (isset($_POST['get_new_food_qty_info'])){
        include_once '../../../db_connect.php';
        include_once '../../../session.php';
        include_once '../../../admin/php/helpers/food_menu.php';
        include_once '../../../admin/php/helpers/order.php';
        include_once '../../../admin/php/helpers/promotion.php';

        updateLastActivity();
        
        $data = [];

        $result = getOrder($connection, $_POST['the_order_id']);

        //food items
        if (isset($_POST['new_food_qty'])){
            $food_qty = $_POST['new_food_qty'];
            $total = 0.0;
            foreach ($food_qty as $id => $qty){
                $food_item_result = getFoodMenuItem($connection, $id);

                $promotions = [];
                //get promotion data from promotion table
                if ($result['status'] != 'Upcoming'){
                    $promotion_infos = getPromotions($connection, $result['rest_id'], ['food_item_id' => $id, 'status' => '1']);

                    if ($promotion_infos){
                        foreach ($promotion_infos as $promotion_info){
                            if (isset($promotion_info['settings_data'])){
                                $settings_data = json_decode($promotion_info['settings_data'], true);
                            }
                            if ($promotion_info['type_code'] == 'BOGO'){
                                $promotions['buy_free'] = '(Buy 1 Get 1 Free)';
                                $promotions['buy_free_value'] = $qty;
                            } elseif ($promotion_info['type_code'] == 'multi_buy'){
                                $promotions['buy_free'] = '(Buy ' . $settings_data['amount_1'] . ' Get ' . $settings_data['amount_2'] . ' Free)';
                                $promotions['buy_free_value'] = floor($qty / (int)$settings_data['amount_1']) * (int)$settings_data['amount_2'];
                            } elseif ($promotion_info['type_code'] == 'percent_off'){
                                $promotions['discount_price'] = $food_item_result['price'] * (1-((int)$settings_data['percentage'] / 100));
                            } elseif ($promotion_info['type_code'] == 'dollar_dis'){
                                $promotions['discount_price'] = $settings_data['discounted_price'];
                            } 
                        }
                    }
                }

                $data['Order'][] = array(
                    'food_id' => $food_item_result['item_id'],
                    'Name' => $food_item_result['item_name'],
                    'Price' => 'RM ' . number_format($food_item_result['price'], 2, '.', ''),
                    'Quantity' => $qty,
                    'Subtotal' => isset($promotions['discount_price']) ? 'RM ' . number_format(($promotions['discount_price'] * $qty), 2, '.', '') : 'RM ' . number_format(($food_item_result['price'] * $qty), 2, '.', ''),
                    'Promotions' => $promotions
                );
                if (isset($promotions['discount_price'])){
                    $total += number_format(($promotions['discount_price'] * $qty), 2, '.', '');
                } else {
                    $total += number_format(($food_item_result['price'] * $qty), 2, '.', '');
                }
            }
            $data['Order']['Total'] = 'RM ' . number_format($total, 2, '.', '');
        }

        echo json_encode($data);
        exit;
    }

    // check modal form
    if (isset($_POST['submit_check_order_store'])){
        include_once '../../../db_connect.php';
        include_once '../../../session.php';
        include_once '../../../admin/php/helpers/order.php';

        updateLastActivity();

        $order_info = getOrder($connection, $_POST['order_id']);

        $json = [];

        //error checking
        //table id
        if ($_POST['table-num-name'] == '*' && $_POST['order_type'] == 'dine_in' && $order_info['reservation_id'] == '0')
            $json['error']['table-num-name'] = "Please select a table!";

        //pickup time
        if ($_POST['order_type'] == 'self_pickup' && $order_info['reservation_id'] == '0'){
            if ($_POST['pickup_time'] == '')
                $json['error']['pickup_time'] = "Please select a pickup time!";
            else {
                date_default_timezone_set("Asia/Kuala_Lumpur");

                $current_time = strtotime(date('Y-m-d H:i'));
                $pickup_time = strtotime(date('Y-m-d') . $_POST['pickup_time']);
                if ($pickup_time < $current_time){
                    $json['error']['pickup_time'] = 'Selected time must not be in the past!';
                } else {
                    $mins_diff = abs($pickup_time - $current_time)/60;
                    if ($mins_diff < 30){
                        $json['error']['pickup_time'] = 'Please choose a time at least 30 minutes from now for the restaurant to prepare your food.';
                    }
                }

                if (!isset($json['error']['pickup_time']) && $order_info['pickup_time'] != (date('Y-m-d') . ' ' . $_POST['pickup_time'] . ':00')){
                    $pickup_orders = getOrders($connection, $order_info['rest_id'], ['pickup_time' => (date('Y-m-d') . ' ' . $_POST['pickup_time'] . ':00'), 'not_statuses' => ['Cancelled', 'Disabled']]);

                    if (count($pickup_orders) >= 5){
                        $json['error']['pickup_time'] = 'The pickup slots are full at this time, please select another time.';
                    }
                }
            }
        }
        
        //check food order items (at least 1)
        if (!json_decode($_POST['hidden_food_qty'], true))
            $json['error']['order-food-table'] = "Please select at least one food item!";

        echo json_encode($json);
        exit;
    }

    //edit order using modal
    if (isset($_POST['submit_form_order_store'])){

        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../email/send_email.php';
        include_once '../helpers/customer.php';
        include_once '../../../session.php';
        include_once '../../../admin/php/helpers/order.php';
        include_once '../../../admin/php/helpers/promotion.php';
        include_once '../../../admin/php/helpers/restaurant.php';
        include_once '../../../admin/php/helpers/reservation.php';

        updateLastActivity();

        $order_info = getOrder($connection, $_POST['order_id']);

        $order_type       = $_POST['order_type'];
        $table_id         = isset($_POST['table-num-name']) ? $_POST['table-num-name'] : '0';
        $food_qty         = $_POST['hidden_food_qty'];
        $additional_notes = trim($_POST['special-request']);

        //promotions
        $promotions = null;
        //promo code
        if (isset($_POST['promo_code_id'])){
            $promotion_info = getPromotion($connection, $_POST['promo_code_id']);
            $settings_data = json_decode($promotion_info['settings_data'], true);
            $promotions['promo_code']['id'] = $promotion_info['promotion_id'];
            $promotions['promo_code']['promo_code_name'] = $settings_data['promo_code_name'];
            $promotions['promo_code']['actual_discount'] = $settings_data['actual_discount'];
            $promotions = json_encode($promotions);
        }

        //voucher
        if (isset($_POST['voucher_id']))
            $voucher_id = $_POST['voucher_id'];
        else
            $voucher_id = 0;

        date_default_timezone_set("Asia/Kuala_Lumpur");

        if ($order_info['reservation_id'] != '0'){
            $sql = "UPDATE `order` SET item_quantity = '" . $food_qty . "', voucher_id = '" . $voucher_id . "', additional_notes = '" . $additional_notes . "', modified_date = '" . date('Y-m-d H:i:s');
        } else {
            //table id
            if ($order_type == 'self_pickup')
                $table_id = '0';

            if (empty($_POST['pickup_time']))
                $pickup_time = 'null';
            else
                $pickup_time = "'" . date('Y-m-d') . ' ' . $_POST['pickup_time'] . "'" ;

            $sql = "UPDATE `order` SET item_quantity = '" . $food_qty . "', voucher_id = '" . $voucher_id . "', additional_notes = '" . $additional_notes . "', modified_date = '" . date('Y-m-d H:i:s') . "', pickup_time = " . $pickup_time . ", table_id = '" . $table_id;
        }

        if (!empty($promotions)){
            $sql .= "', promotions = '" . $promotions . "' WHERE order_id = '" . $_POST['order_id'] . "'";
        } else
            $sql .= "', promotions = null WHERE order_id = '" . $_POST['order_id'] . "'";
        
        $connection->query($sql);

        //for preoreder
        if ($order_info['reservation_id'] != '0'){
            //send email to customer
            //get email body
            $_SESSION['send_email'] = array(
                'rest_id' => $order_info['rest_id'],
                'cus_id'  => $_SESSION['login_cus_id'],
                'reservation_id' => $order_info['reservation_id'],
                'reservation_data' => getReservationFullInfo($connection, $order_info['reservation_id']),
                'type' => 'edit',
                'reservation_or_preorder' => 'preorder',
                'to' => 'customer'
            );

            //add preorder data
            $preorder_info = getOrder($connection, $_POST['order_id']);
            $_SESSION['send_email']['order_id'] = $_POST['order_id'];
            $_SESSION['send_email']['order_data'] = getOrderFullInfo($connection, $_POST['order_id'], 'store');

            $restaurant_name = getRestaurant($connection, $order_info['rest_id'])['rest_name'];
            $customer_email = getCustomer($connection, $_SESSION['login_cus_id'])['email'];

            ob_start(); //Init the output buffering
            include '../../../email/template/reservation.php'; //Include (and compiles) the given file
            $email_body = ob_get_clean(); //Get the buffer and erase it)
            unset($_SESSION['send_email']);

            sendmail($customer_email, $restaurant_name . ' - Reservation ' . $order_info['reservation_id'] . ' Updated', $email_body, '');

            //send email to restaurant
            //get email body
            $_SESSION['send_email'] = array(
                'rest_id' => $order_info['rest_id'],
                'cus_id'  => $_SESSION['login_cus_id'],
                'reservation_id' => $order_info['reservation_id'],
                'reservation_data' => getReservationFullInfo($connection, $order_info['reservation_id']),
                'type' => 'edit',
                'reservation_or_preorder' => 'preorder',
                'to' => 'restaurant'
            );

            //add preorder data
            $preorder_info = getOrder($connection, $_POST['order_id']);
            $_SESSION['send_email']['order_id'] = $_POST['order_id'];
            $_SESSION['send_email']['order_data'] = getOrderFullInfo($connection, $_POST['order_id'], 'store');
            
            $restaurant_email = getRestaurant($connection, $order_info['rest_id'])['email'];
        
            ob_start(); //Init the output buffering
            include '../../../email/template/reservation.php'; //Include (and compiles) the given file
            $email_body = ob_get_clean(); //Get the buffer and erase it)
            unset($_SESSION['send_email']);

            sendmail($restaurant_email, $restaurant_name . ' - Reservation ' . $order_info['reservation_id'] . ' Updated', $email_body, '');
        } else {
            //send email to customer
            //get email body
            $_SESSION['send_email'] = array(
                'rest_id' => $order_info['rest_id'],
                'cus_id'  => $_SESSION['login_cus_id'],
                'order_id' => $_POST['order_id'],
                'order_data' => getOrderFullInfo($connection, $_POST['order_id'], 'store'),
                'type' => 'edit',
                'to' => 'customer'
            );

            $restaurant_name = getRestaurant($connection, $order_info['rest_id'])['rest_name'];
            $customer_email = getCustomer($connection, $_SESSION['login_cus_id'])['email'];
        
            ob_start(); //Init the output buffering
            include '../../../email/template/order.php'; //Include (and compiles) the given file
            $email_body = ob_get_clean(); //Get the buffer and erase it)
            unset($_SESSION['send_email']);

            sendmail($customer_email, $restaurant_name . ' - Order ' . $_POST['order_id'] . ' Updated', $email_body, '');

            //send email to restaurant
            //get email body
            $_SESSION['send_email'] = array(
                'rest_id' => $order_info['rest_id'],
                'cus_id'  => $_SESSION['login_cus_id'],
                'order_id' => $_POST['order_id'],
                'order_data' => getOrderFullInfo($connection, $_POST['order_id'], 'store'),
                'type' => 'edit',
                'to' => 'restaurant'
            );

            $restaurant_email = getRestaurant($connection, $order_info['rest_id'])['email'];
        
            ob_start(); //Init the output buffering
            include '../../../email/template/order.php'; //Include (and compiles) the given file
            $email_body = ob_get_clean(); //Get the buffer and erase it)
            unset($_SESSION['send_email']);

            sendmail($restaurant_email, $restaurant_name . ' - Order ' . $_POST['order_id'] . ' Updated', $email_body, '');
        }

        $_SESSION['success'] = 'You have successfully edited the order.';
        header("Location: ../pages/order/my_order.php");
        exit;
    }

    //cancel order
    if (isset($_POST['cancel_order'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../email/send_email.php';
        include_once 'customer.php';
        include_once '../../../session.php';
        include_once '../../../admin/php/helpers/order.php';
        include_once '../../../admin/php/helpers/reservation.php';
        include_once '../../../admin/php/helpers/restaurant.php';

        updateLastActivity();

        date_default_timezone_set("Asia/Kuala_Lumpur");
        
        $sql = "UPDATE `order` SET `status` = 'Cancelled', remarks = '" . $_POST['remarks'] . "', promotions = null, voucher_id = 0, modified_date = '" . date('Y-m-d H:i:s') . "' WHERE order_id = '" . $_POST['order_id'] . "'";
        $connection->query($sql);

        $order_info = getOrder($connection, $_POST['order_id']);

        if ($order_info['reservation_id'] != 0){
            $reservation_info = getReservation($connection, $order_info['reservation_id']);
            
            //send email to customer
            //get email body
            $_SESSION['send_email'] = array(
                'rest_id' => $order_info['rest_id'],
                'cus_id'  => $_SESSION['login_cus_id'],
                'reservation_id' => $order_info['reservation_id'],
                'reservation_data' => getReservationFullInfo($connection, $order_info['reservation_id']),
                'type' => 'edit',
                'reservation_or_preorder' => 'preorder',
                'to' => 'customer'
            );

            $_SESSION['send_email']['order_id'] = $_POST['order_id'];
            $_SESSION['send_email']['order_data'] = getOrderFullInfo($connection, $_POST['order_id'], 'store');

            $restaurant_name = getRestaurant($connection, $order_info['rest_id'])['rest_name'];
            $customer_email = getCustomer($connection, $_SESSION['login_cus_id'])['email'];

            ob_start(); //Init the output buffering
            include '../../../email/template/reservation.php'; //Include (and compiles) the given file
            $email_body = ob_get_clean(); //Get the buffer and erase it)
            unset($_SESSION['send_email']);

            sendmail($customer_email, $restaurant_name . ' - Reservation ' . $order_info['reservation_id'] . ' Updated', $email_body, '');

            //send email to restaurant
            //get email body
            $_SESSION['send_email'] = array(
                'rest_id' => $order_info['rest_id'],
                'cus_id'  => $_SESSION['login_cus_id'],
                'reservation_id' => $order_info['reservation_id'],
                'reservation_data' => getReservationFullInfo($connection, $order_info['reservation_id']),
                'type' => 'edit',
                'reservation_or_preorder' => 'preorder',
                'to' => 'restaurant'
            );

            $_SESSION['send_email']['order_id'] = $_POST['order_id'];
            $_SESSION['send_email']['order_data'] = getOrderFullInfo($connection, $_POST['order_id'], 'store');
            
            $restaurant_email = getRestaurant($connection, $order_info['rest_id'])['email'];
        
            ob_start(); //Init the output buffering
            include '../../../email/template/reservation.php'; //Include (and compiles) the given file
            $email_body = ob_get_clean(); //Get the buffer and erase it)
            unset($_SESSION['send_email']);

            sendmail($restaurant_email, $restaurant_name . ' - Reservation ' . $order_info['reservation_id'] . ' Updated', $email_body, '');
        } else {
            //send email to customer
            //get email body
            $_SESSION['send_email'] = array(
                'rest_id' => $order_info['rest_id'],
                'cus_id'  => $_SESSION['login_cus_id'],
                'order_id' => $_POST['order_id'],
                'order_data' => getOrderFullInfo($connection, $_POST['order_id'], 'store'),
                'type' => 'edit',
                'to' => 'customer'
            );

            $restaurant_name = getRestaurant($connection, $order_info['rest_id'])['rest_name'];
            $customer_email = getCustomer($connection, $_SESSION['login_cus_id'])['email'];
        
            ob_start(); //Init the output buffering
            include '../../../email/template/order.php'; //Include (and compiles) the given file
            $email_body = ob_get_clean(); //Get the buffer and erase it)
            unset($_SESSION['send_email']);

            sendmail($customer_email, $restaurant_name . ' - Order ' . $_POST['order_id'] . ' Updated', $email_body, '');

            //send email to restaurant
            //get email body
            $_SESSION['send_email'] = array(
                'rest_id' => $order_info['rest_id'],
                'cus_id'  => $_SESSION['login_cus_id'],
                'order_id' => $_POST['order_id'],
                'order_data' => getOrderFullInfo($connection, $_POST['order_id'], 'store'),
                'type' => 'edit',
                'to' => 'restaurant'
            );

            $restaurant_email = getRestaurant($connection, $order_info['rest_id'])['email'];
        
            ob_start(); //Init the output buffering
            include '../../../email/template/order.php'; //Include (and compiles) the given file
            $email_body = ob_get_clean(); //Get the buffer and erase it)
            unset($_SESSION['send_email']);

            sendmail($restaurant_email, $restaurant_name . ' - Order ' . $_POST['order_id'] . ' Updated', $email_body, '');
        }
        

        $_SESSION['success'] = 'You have successfully cancelled the order';
        exit;
    }

    if (isset($_POST['get_order_rating_review'])){
        include_once '../../../db_connect.php';
        include_once '../../../session.php';
        include_once '../../../admin/php/helpers/rating_review.php';

        updateLastActivity();

        $result = [];
        $rating_review_info = getRatingReviewByOrderID($connection, $_POST['order_id']);

        if (!empty($rating_review_info)){
            $result = $rating_review_info;
        }

        echo json_encode($result);
        exit;
    }

    //rate restaurant or order
    if (isset($_POST['submit_rating_review'])){
        include_once '../../../db_connect.php';
        include_once '../../../session.php';
        include_once '../../../admin/php/helpers/rating_review.php';

        updateLastActivity();

        date_default_timezone_set("Asia/Kuala_Lumpur");

        $rating_review_info = getRatingReviewByOrderID($connection, $_POST['order_id']);

        $review = $connection->real_escape_string($_POST['review']);

        if (!empty($rating_review_info)){
            //edit
            if (!empty($_POST['review']))
                $sql = "UPDATE `rating_review` SET rating = '" . $_POST['rating'] . "', review = '" . $review . "', review_modified_date = '" . date('Y-m-d H:i:s') . "' WHERE order_id = '" . $_POST['order_id'] . "'";
            else
                $sql = "UPDATE `rating_review` SET rating = '" . $_POST['rating'] . "', review = null, review_modified_date = '" . date('Y-m-d H:i:s') . "' WHERE order_id = '" . $_POST['order_id'] . "'";
            
            $_SESSION['success'] = 'You have successfully edited the review';
        } else {
            //add
            if (!empty($_POST['review']))
                $sql = "INSERT INTO `rating_review`(order_id, rating, review) VALUES ('" . $_POST['order_id'] . "','" . $_POST['rating'] . "','" .  $review . "')";
            else
                $sql = "INSERT INTO `rating_review`(order_id, rating) VALUES ('" . $_POST['order_id'] . "','" . $_POST['rating'] . "')";

            $_SESSION['success'] = 'You have successfully added the review';

        }

        $connection->query($sql);
        exit;
    }

?>