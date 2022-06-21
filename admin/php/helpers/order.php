<?php

    function getOrders($connection, $rest_id, $filter_data = array()){
        $sql = "SELECT o.* , ts.start_time AS startTime, vt.equal_price, CASE WHEN (o.reservation_id != '0') THEN (CASE WHEN CONCAT(r.date, ' ', ts.start_time) IS NULL THEN CONCAT(r.date, ' ', SUBSTRING(JSON_EXTRACT(r.deleted_data, '$.startTime'), 2, 8)) ELSE CONCAT(r.date, ' ', ts.start_time) END) ELSE (CASE WHEN o.table_id = 0 THEN o.pickup_time ELSE o.created_date END) END AS `datetime` FROM `order` o LEFT JOIN reservation r ON (r.reservation_id = o.reservation_id) LEFT JOIN time_slot ts ON (CAST(ts.time_slot_id AS CHAR) = (JSON_EXTRACT(r.time_slot_id, '$[0]'))) LEFT JOIN voucher v ON (o.voucher_id = v.voucher_id) LEFT JOIN voucher_type vt ON (v.voucher_type_id = vt.voucher_type_id) WHERE o.rest_id = '" . $rest_id . "'";
        
        if (isset($filter_data['table_id'])){
            $sql .= " AND o.table_id = '" . $filter_data['table_id'] . "'";
        }

        if (isset($filter_data['reservation_id'])){
            $sql .= " AND o.reservation_id = '" . $filter_data['reservation_id'] . "'";
        }

        if (isset($filter_data['not_reservation_id'])){
            $sql .= " AND o.reservation_id != '" . $filter_data['reservation_id'] . "'";
        }

        if (isset($filter_data['pre_order'])){
            $sql .= " AND o.reservation_id != '0' AND (o.status = 'Upcoming' OR o.status = 'Cancelled' OR o.status = 'Disabled')";
        }

        if (isset($filter_data['status'])){
            $sql .= " AND o.status = '" . $filter_data['status'] . "'";
        }

        if (isset($filter_data['statuses'])){
            $sql .= " AND o.status IN ('" . $filter_data['statuses'][0] . "'";

            foreach ($filter_data['statuses'] as $key => $is_status){
                if ($key != 0)
                    $sql .= ",'" . $is_status . "'";
            }

            $sql .= ")";
        }

        if (isset($filter_data['not_status'])){
            $sql .= " AND o.status != '" . $filter_data['not_status'] . "'";
        }

        if (isset($filter_data['not_statuses'])){
            $sql .= " AND o.status NOT IN ('" . $filter_data['not_statuses'][0] . "'";

            foreach ($filter_data['not_statuses'] as $key => $not_status){
                if ($key != 0)
                    $sql .= ",'" . $not_status . "'";
            }

            $sql .= ")";
        }

        if (isset($filter_data['pickup_time'])){
            $sql .= " AND o.pickup_time = '" . $filter_data['pickup_time'] . "'";
        }

        if (isset($filter_data['created_date'])){
            $sql .= " AND o.created_date LIKE '" . $filter_data['created_date'] . "%'";
        }

        if (isset($filter_data['promo_code_id'])){
            $sql .= " AND JSON_VALID(o.promotions) AND JSON_CONTAINS_PATH(o.promotions, 'all', '$.\"promo_code\"') AND JSON_EXTRACT(o.promotions, '$.promo_code.id') = '" . $filter_data['promo_code_id'] . "'";
        }

        if (isset($filter_data['food_item_id'])){
            $sql .= " AND JSON_VALID(o.item_quantity) AND JSON_CONTAINS_PATH(o.item_quantity, 'all', '$.\"" . $filter_data['food_item_id'] . "\"')";
        }

        if (isset($filter_data['exclude_cancelled_disabled_preorders'])){
            $sql .= " AND NOT(o.status IN ('Cancelled', 'Disabled') AND o.reservation_id != 0)";
        }

        if (isset($filter_data['group_by'])){
            $sql .= " GROUP BY o." . $filter_data['group_by'];
        }

        if (isset($filter_data['order_by']) && isset($filter_data['asc_desc'])){
            $sql .= " ORDER BY " . $filter_data['order_by'] . " " . $filter_data['asc_desc'];
        } else {
            $sql .= " ORDER BY 
            CASE 
                WHEN o.status = 'Pending' THEN 1 
                WHEN o.status = 'Processing' THEN 2
                WHEN o.status = 'Served' THEN 3
                ELSE 4 
            END ASC, `datetime` DESC";
        }

        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    function getOrder($connection, $order_id){
        $sql = "SELECT o.*, t.table_num_name, CONCAT(c.firstname, ' ', c.lastname) AS customer_name FROM `order` o LEFT JOIN customer c ON (c.cus_id = o.cus_id) LEFT JOIN `table` t ON (t.table_id = o.table_id) WHERE order_id = '" . $order_id . "'";

        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        return $results;
    }

    function getOrdersByReservationID($connection, $reservation_id, $filter_data = array()){
        $sql = "SELECT * FROM `order` WHERE reservation_id = '" . $reservation_id . "'";
        
        if (isset($filter_data['not_status'])){
            $sql .= " AND `status` != '" . $filter_data['not_status'] . "'";
        }

        if (isset($filter_data['not_statuses'])){
            $sql .= " AND `status` NOT IN ('" . $filter_data['not_statuses'][0] . "'";

            foreach ($filter_data['not_statuses'] as $key => $not_status){
                if ($key != 0)
                    $sql .= ",'" . $not_status . "'";
            }

            $sql .= ")";
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

    function getCustomerOrders($connection, $cus_id, $filter_data = array()){
        $sql = "SELECT o.*, rr.*, o.order_id AS order_id, CASE WHEN (o.reservation_id != '0') THEN (CASE WHEN CONCAT(r.date, ' ', ts.start_time) IS NULL THEN r.date ELSE CONCAT(r.date, ' ', ts.start_time) END) ELSE o.created_date END AS `datetime` FROM `order` o LEFT JOIN reservation r ON (r.reservation_id = o.reservation_id) LEFT JOIN time_slot ts ON (CAST(ts.time_slot_id AS CHAR) = (JSON_EXTRACT(r.time_slot_id, '$[0]'))) LEFT JOIN rating_review rr ON (rr.order_id = o.order_id) WHERE o.cus_id = '" . $cus_id . "'";

        if (isset($filter_data['rest_id'])){
            $sql .= " AND o.rest_id = '" . $filter_data['rest_id'] . "'";
        }

        if (isset($filter_data['not_order_id'])){
            $sql .= " AND o.order_id != " . $filter_data['not_order_id'];
        }

        if (isset($filter_data['not_preorder'])){
            $sql .= " AND o.reservation_id = '0'";
        }

        if (isset($filter_data['preorder'])){
            $sql .= " AND o.reservation_id != '0'";
        }

        if (isset($filter_data['status'])){
            $sql .= " AND o.status = '" . $filter_data['status'] . "'";
        }

        if (isset($filter_data['statuses'])){
            $sql .= " AND o.status IN ('" . $filter_data['statuses'][0] . "'";

            foreach ($filter_data['statuses'] as $key => $status){
                if ($key != 0)
                    $sql .= ",'" . $status . "'";
            }

            $sql .= ")";
        }

        if (isset($filter_data['not_status'])){
            $sql .= " AND o.status != '" . $filter_data['not_status'] . "'";
        }

        if (isset($filter_data['not_statuses'])){
            $sql .= " AND o.status NOT IN ('" . $filter_data['not_statuses'][0] . "'";

            foreach ($filter_data['not_statuses'] as $key => $not_status){
                if ($key != 0)
                    $sql .= ",'" . $not_status . "'";
            }

            $sql .= ")";
        }

        if (isset($filter_data['group_by'])){
            $sql .= " GROUP BY o." . $filter_data['group_by'];
        }

        if (isset($filter_data['order_by']) && isset($filter_data['asc_desc'])){
            $sql .= " ORDER BY o." . $filter_data['order_by'] . " " . $filter_data['asc_desc'];
        } elseif (isset($filter_data['asc_desc'])) {
            $sql .= " ORDER BY `datetime` DESC";
        } else {
            $sql .= " ORDER BY `datetime` ASC";
        }

        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    function getOrderFullInfo($connection, $order_id, $admin_or_store){
        if ($admin_or_store == 'admin'){
            include_once '../helpers/food_menu.php';
            include_once '../helpers/promotion.php';
            include_once '../helpers/rating_review.php';
            include_once '../../../store/php/helpers/voucher.php';
        } else {
            include_once '../helpers/voucher.php';
            include_once '../../../admin/php/helpers/food_menu.php';
            include_once '../../../admin/php/helpers/promotion.php';
            include_once '../../../admin/php/helpers/rating_review.php';
        }

        $result = getOrder($connection, $order_id);
        $order['Name'] = $result['customer_name'];
        $order['Table Number (Name)'] = $result['table_id'] == '0' ? '-' : (empty($result['table_num_name']) ? json_decode($result['deleted_data'], true)['table_num_name'] : $result['table_num_name']);
        $order['Self Pickup'] = $result['table_id'] == 0 ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>';
        $order['Self Pickup Time'] = $result['table_id'] == 0 ? date('d/m/Y h:i a', strtotime($result['pickup_time'])) : '-';
        
        //order's promotions
        $arranged_promotions_info = [];
        if (!empty($result['promotions']) && $result['status'] == 'Completed'){
            $promos = json_decode($result['promotions'], true);
            foreach ($promos as $key => $promo){
                if ($key !== 'promo_code'){
                    foreach ($promo['food_items'] as $id){
                        $arranged_promotions_info[$id][] = $promo;

                    }
                }
            }
        }
        //food items
        $food_qty = json_decode($result['item_quantity'], true);
        $total = 0.0;
        foreach ($food_qty as $id => $qty){
            $food_item_result = getFoodMenuItem($connection, $id);

            $promotions = [];
            if ($result['status'] != 'Completed' && $result['status'] != 'Cancelled' && $result['status'] != 'Disabled' && $result['status'] != 'Upcoming'){
                //get promotion data from promotion table
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
            } else if ($result['status'] == 'Completed'){
                if (isset($arranged_promotions_info[$id])){
                    foreach ($arranged_promotions_info[$id] as $promotion_info){
                        if (isset($promotion_info['settings_data'])){
                            $settings_data = $promotion_info['settings_data'];
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

            $order['Order'][] = array(
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
        $order['Order']['Total'] = 'RM ' . number_format($total, 2, '.', '');
        
        if (!empty($result['promotions'])){
            $order_promos = json_decode($result['promotions'], true);
            if (isset($order_promos['promo_code'])){
                $order['Order']['promo_code']['name'] = $order_promos['promo_code']['promo_code_name'];
                $order['Order']['promo_code']['discount'] = '- RM ' . number_format($order_promos['promo_code']['actual_discount'], 2, '.', '');
            }
        }

        if ($result['voucher_id'] != '0'){
            $voucher_info = getVoucher($connection, $result['voucher_id']);
            $order['Order']['voucher'] = array(
                'price' => '- RM ' . number_format($voucher_info['equal_price'], 2, '.', '')
            );
        }

        $order['Special Request'] = $result['additional_notes'] ? $result['additional_notes'] : "-";
        $order['Created Date'] = date('d/m/Y h:i a', strtotime($result['created_date']));
        $order['Modified Date'] = $result['modified_date'] === $result['created_date'] ? "-" : date('d/m/Y h:i a', strtotime($result['modified_date']));
        $order['Status'] = $result['status'] == 'Cancelled' ? '<span class="badge bg-danger">Cancelled</span>' : ($result['status'] == 'Served' ? '<span class="badge bg-success">Served</span>' : ($result['status'] == 'Disabled' ? '<span class="badge bg-secondary">Disabled</span>' : ($result['status'] == 'Completed' ? '<span class="badge bg-primary">Completed</span>' : ($result['status'] == 'Pending' ? '<span class="badge bg-warning text-dark bg-opacity-75">Pending</span>' : ($result['status'] == 'Upcoming' ? '<span class="badge bg-success bg-opacity-75">Upcoming</span>' : '<span class="badge bg-info bg-opacity-50" style="color: #055160;">Processing</span>')))));
        $order['Remarks'] = $result['remarks'] ? $result['remarks'] : '-';

        //ratings
        $rating_review_info = getRatingReviewByOrderID($connection, $order_id);
        if (!empty($rating_review_info) && $result['status'] == 'Completed'){
            $order['Rating'] = '';
            for ($i=0; $i<$rating_review_info['rating']; $i++) {
                $order['Rating'] .= '<i class="fas fa-star text-danger"></i>';
            }
            for ($i=0; $i<(5-$rating_review_info['rating']); $i++) {
                $order['Rating'] .= '<i class="fas fa-star text-secondary"></i>';
            }

            $order['Review'] = empty($rating_review_info['review']) ? '-' : $rating_review_info['review'];
            $order['Reply'] = empty($rating_review_info['reply']) ? '-' : $rating_review_info['reply'];
        }
        
        return json_encode($order);
    }

    //delete order and preorder
    if (isset($_POST['selected_group'])){
        
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $selected_groups = $_POST['selected_group'];
        $counter = 0;
        foreach ($selected_groups as $selected_group){
            $sql = "DELETE FROM `order` WHERE order_id = '" . $selected_group . "'";
            $connection->query($sql);

            if (!isset($_POST['pre_order'])){
                $sql2 = "DELETE FROM `rating_review` WHERE order_id = '" . $selected_group . "'";
                $connection->query($sql2);
            }

            $counter++;
        }
        
        if (isset($_POST['pre_order']))
            $_SESSION['success'] = "You have successfully deleted " . (string)$counter . " preorder(s).";
        else
            $_SESSION['success'] = "You have successfully deleted " . (string)$counter . " order(s).";
        
        exit;
    }

    //get order details for popup modal
    if (isset($_POST['modal_order_id'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        echo getOrderFullInfo($connection, $_POST['modal_order_id'], 'admin');
        
        exit;
    }

    //order form offer description
    if (isset($_POST['get_offer_description'])){
        include_once '../../../db_connect.php';
        include_once '../helpers/promotion.php';
        include_once '../../../session.php';

        updateLastActivity();

        $promo_info = getPromotion($connection, $_POST['promo_id']);

        $promo_info['description'] = str_replace("\n", '<br>', $promo_info['description']);
        
        echo $promo_info['description'];
        exit;
    }

    //order form apply offer checking
    if (isset($_POST['apply_offer_checking']) || isset($_POST['apply_offer_checking_store'])){
        include_once '../../../db_connect.php';
        include_once '../helpers/promotion.php';
        include_once '../../../session.php';

        updateLastActivity();

        $json = [];

        //check min spend
        $promo_info = getPromotion($connection, $_POST['promo_id']);
        $settings_data = json_decode($promo_info['settings_data'], true);
        if ($_POST['current_total'] < (float)$settings_data['min_price']){
            $json['error'] = "This order has not reached the minimum spend, add more to enjoy the offer!";
        }

        //check redemption limit
        if (empty($json)){
            $redemption_record = getRedemptionRecord($connection, $_POST['promo_id'], $_POST['cus_id']);
            if ($redemption_record){
                if ($redemption_record[0]['redemption_left'] == 0){
                    if (isset($_POST['apply_offer_checking_store']))
                        $json['error'] = "You have reached the redemption limit of this promotional code!";
                    else
                        $json['error'] = "This customer has reached the redemption limit of this promotional code!";
                }
            }
        }

        if (empty($json)){
            $json['success']['value'] = (float)$settings_data['actual_discount'];
            $json['success']['name'] = $settings_data['promo_code_name'];
        }
        
        echo json_encode($json);
        exit;
    }

    //update voucher list add order form
    if (isset($_POST['update_voucher_list'])){
        include_once '../../../db_connect.php';
        include_once '../../../session.php';
        include_once '../../../store/php/helpers/voucher.php';

        updateLastActivity();

        $voucher_infos = getVouchers($connection, $_POST['cus_id'], ['valid' => true, 'applied_order_id' => '0', 'not_in_use' => true]);
        $vouchers = [];

        foreach ($voucher_infos as $key => $voucher_info){
            $vouchers[] = array(
                'the_voucher_id' => $voucher_info['the_voucher_id'],
                'name'           => $voucher_info['name'],
                'equal_price'    => $voucher_info['equal_price'],
                'expired_date'   => date('d/m/Y', strtotime($voucher_info['expired_date']))
            );
        }

        echo json_encode($vouchers);
        exit;
    }

    //check order form
    if (isset($_POST['submit_check_order'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $cus_id     = $_POST['cus-id'];
        $cus_name   = trim($_POST['cus_name']);
        $order_type = $_POST['order_type'];
        $table_id   = $_POST['table-num-name'];

        $json = [];

        //error checking
        //customer name
        if (empty($cus_name))
            $json['error']['cus-name'] = "Please enter a customer name!";
        
        //customer id
        if (empty($cus_id) && !empty($cus_name))
            $json['error']['cus-name'] = "This customer is not registered!";
        elseif (!isset($_POST['order_id'])){
            //check if this customer has uncompleted order if is not edit order
            $result = getCustomerOrders($connection, $cus_id, ['not_statuses' => ['Completed', 'Cancelled', 'Disabled', 'Upcoming'], 'rest_id' => $_SESSION['login_rest_id']]);
            if ($result){
                $json['warning'] = "This customer has uncompleted order!";
            }
        }

        //table id
        if ($table_id == '*' && $order_type == 'dine_in')
            $json['error']['table-num-name'] = "Please select a table!";

        //pickup time
        if ($order_type == 'self_pickup'){
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

                if (!isset($json['error']['pickup_time'])){
                    if (isset($_POST['order_id'])){
                        $current_order_info = getOrder($connection, $_POST['order_id']);
                    }

                    if ((isset($current_order_info) && $current_order_info['pickup_time'] != (date('Y-m-d') . ' ' . $_POST['pickup_time'] . ':00')) || !isset($current_order_info)) {
                        $pickup_orders = getOrders($connection, $_SESSION['login_rest_id'], ['pickup_time' => (date('Y-m-d') . ' ' . $_POST['pickup_time'] . ':00'), 'not_statuses' => ['Cancelled', 'Disabled']]);

                        if (count($pickup_orders) >= 5){
                            $json['error']['pickup_time'] = 'The pickup slots are full at this time, please select another time.';
                        }
                    }
                }
            }
        }
        
        //check food order items (at least 1)
        if (!isset($_POST['food_qty']))
            $json['error']['order-food-table'] = "Please select at least one food item!";

        echo json_encode($json);
        exit;
    }

    //submit order form
    if (isset($_POST['submit_form_order'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../helpers/food_menu.php';
        include_once '../helpers/inventory.php';
        include_once '../helpers/promotion.php';
        include_once '../helpers/restaurant.php';
        include_once '../../../email/send_email.php';
        include_once '../../../session.php';
        include_once '../../../store/php/helpers/customer.php';

        updateLastActivity();

        $cus_id           = $_POST['cus-id'];
        $order_type       = $_POST['order_type'];
        $table_id         = isset($_POST['table-num-name']) ? $_POST['table-num-name'] : '0';
        $additional_notes = trim($_POST['special-request']);
        $food_qty         = $_POST['food_qty'];
        $status           = $_POST['status'];
        $remarks          = trim($_POST['remarks']);

        //promotions
        $promotions = null;
        //promo code
        if (isset($_POST['promo_code_id']) && (in_array($status, ['Pending', 'Processing', 'Served', 'Completed']))){
            $promotion_info = getPromotion($connection, $_POST['promo_code_id']);
            $settings_data = json_decode($promotion_info['settings_data'], true);
            $promotions['promo_code']['id'] = $promotion_info['promotion_id'];
            $promotions['promo_code']['promo_code_name'] = $settings_data['promo_code_name'];
            $promotions['promo_code']['actual_discount'] = $settings_data['actual_discount'];
            $promotions = json_encode($promotions);
        }

        //voucher
        if (isset($_POST['voucher_id']) && $status != 'Cancelled')
            $voucher_id = $_POST['voucher_id'];
        else
            $voucher_id = 0;

        if ($status == 'Completed'){
            //promotions
            if (isset($_POST['promo_code_id'])){
                //edit redemption record
                $redemption_record = getRedemptionRecord($connection, $_POST['promo_code_id'], $cus_id);
                if ($redemption_record){
                    $sql = "UPDATE `cus_promo_redemption` SET redemption_left = '" . (((int)$redemption_record[0]['redemption_left']) - 1) . "' WHERE cus_promo_redemption_id = '" . $redemption_record[0]['cus_promo_redemption_id'] . "'";
                } else {
                    $sql = "INSERT INTO `cus_promo_redemption`(cus_id, `promotion_id`, redemption_left) VALUES ('" . $cus_id . "','" . $_POST['promo_code_id'] . "'," . (((int)$settings_data['max_redemption']) - 1) . ")";
                }
                $connection->query($sql);
            }

            //add the rest promotions into promotions array
            $enabled_promotions = getPromotions($connection, $_SESSION['login_rest_id'], ['not_type_code' => 'promo_code', 'status' => '1']);

            if ($enabled_promotions){
                if (!empty($promotions))
                    $promotions = json_decode($promotions, true);
                else
                    $promotions = [];

                foreach ($enabled_promotions as $enabled_promotion){
                    $temp_food_ids = [];
                    foreach (json_decode($enabled_promotion['food_items'], true) as $food_id){
                        if (array_key_exists($food_id, $food_qty)){
                            $temp_food_ids[] = $food_id;
                        }
                    }

                    if (!empty($temp_food_ids)){
                        $promotions[] = array(
                            'type_code'     => $enabled_promotion['type_code'],
                            'settings_data' => json_decode($enabled_promotion['settings_data'], true),
                            'food_items'    => $temp_food_ids
                        );
                    }
                }

                if (!empty($promotions))
                    $promotions = json_encode($promotions);
                else
                    $promotions = null;
            }
        }

        //table id
        if ($order_type == 'self_pickup')
            $table_id = '0';

        //get food ingredients
        $food_ingredients = [];
        foreach ($food_qty as $key => $value){
            $food_promo = getPromotions($connection, $_SESSION['login_rest_id'], ['type_codes' => ['BOGO', 'multi_buy'], 'status' => '1', 'food_item_id' => $key]);

            if ($food_promo){
                //update quantity (include free item value)
                if ($food_promo[0]['type_code'] == 'BOGO')
                    $value += $value;
                elseif ($food_promo[0]['type_code'] == 'multi_buy'){
                    $settings_data = json_decode($food_promo[0]['settings_data'], true);
                    $value += floor($value / (int)$settings_data['amount_1']) * (int)$settings_data['amount_2'];
                }
                    
            }

            $food_item_info = getFoodMenuItem($connection, $key);
            $ingredients = json_decode($food_item_info['ingredients'], true);
            foreach ($ingredients as $key2 => $ingredient){
                $ingredients[$key2] = (int)$ingredient * (int)$value;
            }
            $food_ingredients[$key] = $ingredients;
        }

        $update_stock = false;

        date_default_timezone_set("Asia/Kuala_Lumpur");
        
        //pickup time
        if (empty($_POST['pickup_time']))
            $pickup_time = 'null';
        else
            $pickup_time = "'" . date('Y-m-d') . ' ' . $_POST['pickup_time'] . "'" ;

        //create or edit order
        if (isset($_GET['order_id'])){
            //get original order status
            $original_order_status = getOrder($connection, $_GET['order_id'])['status'];

            $sql = "UPDATE `order` SET `table_id` = '" . $table_id . "', voucher_id = " . (int)$voucher_id . ", item_quantity = '" . json_encode($food_qty) . "', additional_notes = '" . $additional_notes . "', modified_date = '" . date('Y-m-d H:i:s') . "', status = '" . $status . "', pickup_time = " . $pickup_time . ", remarks = '" . $remarks;

            if (!empty($promotions)){
                $sql .= "', promotions = '" . $promotions . "' WHERE order_id = '" . $_GET['order_id'] . "'";
            } else
                $sql .= "', promotions = null WHERE order_id = '" . $_GET['order_id'] . "'";

            $connection->query($sql);

            //update inventory stock
            if ($original_order_status != 'Served' && ($status == 'Served' || $status == 'Completed')){
                $update_stock = true;
            }

            //update voucher's applied order id
            if ($status == 'Completed' && isset($_POST['voucher_id'])){
                $sql2 = "UPDATE voucher SET applied_order_id = '" . $_GET['order_id'] . "' WHERE voucher_id = '" . $_POST['voucher_id'] . "'";

                $connection->query($sql2);
            }

            $success_msg = 'You have successfully edited the order.';

            //send email to customer
            //get email body
            $_SESSION['send_email'] = array(
                'rest_id' => $_SESSION['login_rest_id'],
                'cus_id'  => $cus_id,
                'order_id' => $_GET['order_id'],
                'order_data' => getOrderFullInfo($connection, $_GET['order_id'], 'admin'),
                'type' => 'edit',
                'to' => 'customer'
            );

            $restaurant_name = getRestaurant($connection, $_SESSION['login_rest_id'])['rest_name'];
            $customer_email = getCustomer($connection, $cus_id)['email'];
        
            ob_start(); //Init the output buffering
            include '../../../email/template/order.php'; //Include (and compiles) the given file
            $email_body = ob_get_clean(); //Get the buffer and erase it)
            unset($_SESSION['send_email']);

            sendmail($customer_email, $restaurant_name . ' - Order ' . $_GET['order_id'] . ' Update', $email_body, $success_msg);

            //send email to restaurant
            //get email body
            $_SESSION['send_email'] = array(
                'rest_id' => $_SESSION['login_rest_id'],
                'cus_id'  => $cus_id,
                'order_id' => $_GET['order_id'],
                'order_data' => getOrderFullInfo($connection, $_GET['order_id'], 'admin'),
                'type' => 'edit',
                'to' => 'restaurant'
            );

            $restaurant_email = getRestaurant($connection, $_SESSION['login_rest_id'])['email'];
        
            ob_start(); //Init the output buffering
            include '../../../email/template/order.php'; //Include (and compiles) the given file
            $email_body = ob_get_clean(); //Get the buffer and erase it)
            unset($_SESSION['send_email']);

            $success_msg = sendmail($restaurant_email, $restaurant_name . ' - Order ' . $_GET['order_id'] . ' Update', $email_body, $success_msg);
        } else {
            if (!empty($promotions)){
                $sql = "INSERT INTO `order`(rest_id, cus_id, `table_id`, pickup_time, item_quantity, voucher_id, reservation_id, `status`, additional_notes, remarks, promotions) VALUES ('" . $_SESSION['login_rest_id'] . "','" . $cus_id . "','" . $table_id . "'," . $pickup_time . ",'" .  json_encode($food_qty) . "','0','0','" . $status . "','" .  $additional_notes . "','" .  $remarks . "','" .  $promotions . "')";
            } else {
                $sql = "INSERT INTO `order`(rest_id, cus_id, `table_id`, pickup_time, item_quantity, voucher_id, reservation_id, `status`, additional_notes, remarks) VALUES ('" . $_SESSION['login_rest_id'] . "','" . $cus_id . "','" . $table_id . "'," . $pickup_time . ",'" .  json_encode($food_qty) . "','0','0','" . $status . "','" .  $additional_notes . "','" .  $remarks . "')";
            }

            $connection->query($sql);

            //update inventory stock
            if ($status == 'Served' || $status == 'Completed'){
                $update_stock = true;
            }

            $inserted_id = $connection->query('SELECT LAST_INSERT_ID() AS id')->fetch_array(MYSQLI_ASSOC)['id'];

            //update voucher's applied order id
            if ($status == 'Completed' && isset($_POST['voucher_id'])){
                $sql2 = "UPDATE voucher SET applied_order_id = '" . $inserted_id . "' WHERE voucher_id = '" . $_POST['voucher_id'] . "'";

                $connection->query($sql2);
            }

            $cus_rest_history_result = getCustomerRestaurantHistory($connection, $cus_id, $_SESSION['login_rest_id']);
            if (count($cus_rest_history_result) == 0){
                $sql2 = "INSERT INTO cus_rest_history(cus_id, rest_id) VALUES ('" . $cus_id . "','" .  $_SESSION['login_rest_id'] . "')";
                $connection->query($sql2);
            }
            
            $success_msg = 'You have successfully added the order.';

            //send email to customer
            //get email body
            $_SESSION['send_email'] = array(
                'rest_id' => $_SESSION['login_rest_id'],
                'cus_id'  => $cus_id,
                'order_id' => $inserted_id,
                'order_data' => getOrderFullInfo($connection, $inserted_id, 'admin'),
                'type' => 'add',
                'to' => 'customer'
            );

            $restaurant_name = getRestaurant($connection, $_SESSION['login_rest_id'])['rest_name'];
            $customer_email = getCustomer($connection, $cus_id)['email'];
        
            ob_start(); //Init the output buffering
            include '../../../email/template/order.php'; //Include (and compiles) the given file
            $email_body = ob_get_clean(); //Get the buffer and erase it)
            unset($_SESSION['send_email']);

            sendmail($customer_email, $restaurant_name . ' - Order ' . $inserted_id, $email_body, $success_msg);

            //send email to restaurant
            //get email body
            $_SESSION['send_email'] = array(
                'rest_id' => $_SESSION['login_rest_id'],
                'cus_id'  => $cus_id,
                'order_id' => $inserted_id,
                'order_data' => getOrderFullInfo($connection, $inserted_id, 'admin'),
                'type' => 'add',
                'to' => 'restaurant'
            );

            $restaurant_email = getRestaurant($connection, $_SESSION['login_rest_id'])['email'];
        
            ob_start(); //Init the output buffering
            include '../../../email/template/order.php'; //Include (and compiles) the given file
            $email_body = ob_get_clean(); //Get the buffer and erase it)
            unset($_SESSION['send_email']);

            $success_msg = sendmail($restaurant_email, $restaurant_name . ' - Order ' . $inserted_id, $email_body, $success_msg);
        }

        //update inventory stock
        if ($update_stock && $food_ingredients){
            foreach ($food_ingredients as $food_id => $ingredients){
                foreach ($ingredients as $id => $qty){
                    //get ingredient info
                    $ingredient_info = getInventory($connection, $id);
                    if ($ingredient_info['current_stock_2'] == '0'){
                        $to_reduce = ceil($qty / $ingredient_info['unit_convert_qty2']);
                        $current_stock = $ingredient_info['current_stock'] - $to_reduce;
                        $current_stock_2 = ($ingredient_info['unit_convert_qty2'] * $to_reduce) - $qty;
                    } elseif ($ingredient_info['current_stock_2'] < $qty){
                        $qty_left = $qty - $ingredient_info['current_stock_2'];
                        $to_reduce = ceil($qty_left / $ingredient_info['unit_convert_qty2']);
                        $current_stock = $ingredient_info['current_stock'] - $to_reduce;
                        $current_stock_2 = ($ingredient_info['unit_convert_qty2'] * $to_reduce) - $qty_left;
                    } else {
                        $current_stock = $ingredient_info['current_stock'];
                        $current_stock_2 = $ingredient_info['current_stock_2'] - $qty;
                    }
                    if ($current_stock < 0) $current_stock = 0;
                    if ($current_stock_2 < 0) $current_stock_2 = 0;
                    
                    $sql3 = "UPDATE `inventory` SET `current_stock` = '" . $current_stock . "', current_stock_2 = '" . $current_stock_2 . "' WHERE inventory_id = '" . $id . "'";
                    $connection->query($sql3);

                    //disable food item if this ingredient need to restock
                    if ($current_stock < $ingredient_info['reorder_level'] || ($current_stock == $ingredient_info['reorder_level'] && $current_stock_2 <= $ingredient_info['reorder_level_2'])){
                        //get food items that need this ingredient
                        $food_contains_ingredients = getFoodMenuItems($connection, $_SESSION['login_rest_id'], ['ingredient_id' => $id]);
                        if ($food_contains_ingredients){
                            foreach ($food_contains_ingredients as $food){
                                $unavailable_condition = json_decode($food['unavailable_condition'], true);
                                if (!in_array($id, $unavailable_condition['lack_ingredient']))
                                    $unavailable_condition['lack_ingredient'][] = $id;

                                $sql4 = "UPDATE `food_item` SET `status` = '0', unavailable_condition = '" . json_encode($unavailable_condition) . "' WHERE item_id = '" . $food['item_id'] . "'";
                                $connection->query($sql4);

                                if ($current_stock == 0 && $current_stock_2 == 0)
                                    disableOrdersDueToFoodItem($connection, $_SESSION['login_rest_id'], $food['item_id'], $food['item_name'], 'order');
                            }
                        }
                    }
                }
            }
        }

        //update customer points
        if ($status == 'Completed'){
            if (isset($_POST['grand_total']))
                $final_total = $_POST['grand_total'];
            elseif (isset($_POST['total']))
                $final_total = $_POST['total'];

            $points = floor($final_total);

            $sql5 = "UPDATE `cus_points` SET points = points + " . $points . " WHERE cus_id = '" . $cus_id . "'";
            $connection->query($sql5);
        }

        $_SESSION['success'] = $success_msg;
        header("Location: ../pages/order/order.php");
        
        exit;
    }

    //check preorder form
    if (isset($_POST['submit_check_preorder'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../helpers/reservation.php';
        include_once '../../../session.php';

        updateLastActivity();

        $cus_id = $_POST['cus-id'];

        $json = [];

        //error checking
        //customer id
        if (isset($_POST['reservation_id'])){
            //check if this reservation already has a preorder if is not edit order
            $result = getOrdersByReservationID($connection, $_POST['reservation_id'], ['not_statuses' => ['Cancelled', 'Disabled']]);
            if ($result){
                $json['warning'] = "This reservation already has a pre-order!";
            } elseif ($_POST['status'] == 'Pending') {
                $result2 = getCustomerOrders($connection, $cus_id, ['not_statuses' => ['Completed', 'Cancelled', 'Disabled', 'Upcoming'], 'rest_id' => $_SESSION['login_rest_id']]);
                if ($result2){
                    $json['warning'] = "This customer has uncompleted order!";
                }
            }
        } else { //when edit preorder
            //to change preorder status, check if there is uncompleted order
            if (!in_array($_POST['status'], ['Upcoming', 'Cancelled'])){
                $result = getCustomerOrders($connection, $cus_id, ['not_statuses' => ['Completed', 'Cancelled', 'Disabled', 'Upcoming'], 'rest_id' => $_SESSION['login_rest_id'], 'not_order_id' => $_POST['order_id']]);
                if ($result){
                    $json['warning'] = "This customer has uncompleted order!";
                } else {
                    //check if there is other ongoing reservation (not belongs to this preorder)
                    $order_infor = getOrder($connection, $_POST['order_id']);
                    $ongoing_reservations = getReservations($connection, $_SESSION['login_rest_id'], ['cus_id' => $cus_id, 'remarks' => 'Ongoing', 'not_reservation_id' => $order_infor['reservation_id']]);
                    if ($ongoing_reservations)
                        $json['warning'] = "This customer currently has other ongoing reservation!";
                }
            }
        }

        //check food order items (at least 1)
        if (!isset($_POST['food_qty']))
            $json['error']['order-food-table'] = "Please select at least one food item!";

        echo json_encode($json);
        exit;
    }

    //submit preorder form
    if (isset($_POST['submit_form_preorder'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../helpers/food_menu.php';
        include_once '../helpers/inventory.php';
        include_once '../helpers/reservation.php';
        include_once '../helpers/promotion.php';
        include_once '../helpers/restaurant.php';
        include_once '../../../email/send_email.php';
        include_once '../../../session.php';
        include_once '../../../store/php/helpers/customer.php';

        updateLastActivity();

        $cus_id            = $_POST['cus-id'];
        $food_qty          = $_POST['food_qty'];
        $status            = $_POST['status'];
        $remarks           = trim($_POST['remarks']);
        $additional_notes  = trim($_POST['special-request']);

        //promotions
        $promotions = null;
        //promo code
        if (isset($_POST['promo_code_id']) && (in_array($status, ['Pending', 'Processing', 'Served', 'Completed']))){
            $promotion_info = getPromotion($connection, $_POST['promo_code_id']);
            $settings_data = json_decode($promotion_info['settings_data'], true);
            $promotions['promo_code']['id'] = $promotion_info['promotion_id'];
            $promotions['promo_code']['promo_code_name'] = $settings_data['promo_code_name'];
            $promotions['promo_code']['actual_discount'] = $settings_data['actual_discount'];
            $promotions = json_encode($promotions);
        }

        //voucher
        if (isset($_POST['voucher_id']) && $status != 'Cancelled')
            $voucher_id = $_POST['voucher_id'];
        else
            $voucher_id = 0;

        if ($status == 'Completed'){
            //promotions
            if (isset($_POST['promo_code_id'])){
                //edit redemption record
                $redemption_record = getRedemptionRecord($connection, $_POST['promo_code_id'], $cus_id);
                if ($redemption_record){
                    $sql = "UPDATE `cus_promo_redemption` SET redemption_left = '" . (((int)$redemption_record[0]['redemption_left']) - 1) . "' WHERE cus_promo_redemption_id = '" . $redemption_record[0]['cus_promo_redemption_id'] . "'";
                } else {
                    $sql = "INSERT INTO `cus_promo_redemption`(cus_id, `promotion_id`, redemption_left) VALUES ('" . $cus_id . "','" . $_POST['promo_code_id'] . "'," . (((int)$settings_data['max_redemption']) - 1) . ")";
                }
                $connection->query($sql);
            }

            //add the rest promotions into promotions array
            $enabled_promotions = getPromotions($connection, $_SESSION['login_rest_id'], ['not_type_code' => 'promo_code', 'status' => '1']);

            if ($enabled_promotions){
                if (!empty($promotions))
                    $promotions = json_decode($promotions, true);
                else
                    $promotions = [];

                foreach ($enabled_promotions as $enabled_promotion){
                    $temp_food_ids = [];
                    foreach (json_decode($enabled_promotion['food_items'], true) as $food_id){
                        if (array_key_exists($food_id, $food_qty)){
                            $temp_food_ids[] = $food_id;
                        }
                    }

                    if (!empty($temp_food_ids)){
                        $promotions[] = array(
                            'type_code'     => $enabled_promotion['type_code'],
                            'settings_data' => json_decode($enabled_promotion['settings_data'], true),
                            'food_items'    => $temp_food_ids
                        );
                    }
                }

                if (!empty($promotions))
                    $promotions = json_encode($promotions);
                else
                    $promotions = null;
            }
        }
        

        //get food ingredients
        $food_ingredients = [];
        foreach ($food_qty as $key => $value){
            $food_promo = getPromotions($connection, $_SESSION['login_rest_id'], ['type_codes' => ['BOGO', 'multi_buy'], 'status' => '1', 'food_item_id' => $key]);

            if ($food_promo){
                //update quantity (include free item value)
                if ($food_promo[0]['type_code'] == 'BOGO')
                    $value += $value;
                elseif ($food_promo[0]['type_code'] == 'multi_buy'){
                    $settings_data = json_decode($food_promo[0]['settings_data'], true);
                    $value += floor($value / (int)$settings_data['amount_1']) * (int)$settings_data['amount_2'];
                }
                    
            }

            $food_item_info = getFoodMenuItem($connection, $key);
            $ingredients = json_decode($food_item_info['ingredients'], true);
            foreach ($ingredients as $key2 => $ingredient){
                $ingredients[$key2] = (int)$ingredient * (int)$value;
            }
            $food_ingredients[$key] = $ingredients;
        }

        $update_stock = false;

        date_default_timezone_set("Asia/Kuala_Lumpur");

        //create or edit preorder
        if (isset($_GET['order_id'])){
            //get original order status
            $original_order_status = getOrder($connection, $_GET['order_id'])['status'];

            $sql = "UPDATE `order` SET item_quantity = '" . json_encode($food_qty) . "', voucher_id = " . (int)$voucher_id . ", modified_date = '" . date('Y-m-d H:i:s') . "', status = '" . $status . "', additional_notes = '" . $additional_notes . "', remarks = '" . $remarks;

            if (!empty($promotions)){
                $sql .= "', promotions = '" . $promotions . "' WHERE order_id = '" . $_GET['order_id'] . "'";
            } else
                $sql .= "', promotions = null WHERE order_id = '" . $_GET['order_id'] . "'";
            
            $connection->query($sql);

            //update inventory stock
            if ($original_order_status != 'Served' && ($status == 'Served' || $status == 'Completed')){
                $update_stock = true;
            }

            //update voucher's applied order id
            if ($status == 'Completed' && isset($_POST['voucher_id'])){
                $sql2 = "UPDATE voucher SET applied_order_id = '" . $_GET['order_id'] . "' WHERE voucher_id = '" . $_POST['voucher_id'] . "'";

                $connection->query($sql2);
            }

            $reservation_id = getOrder($connection, $_GET['order_id'])['reservation_id'];

            //update reservation's status and modified date
            if ($status == 'Completed'){
                $sql2 = "UPDATE `reservation` SET modified_date = '" . date('Y-m-d H:i:s') . "', status = '3', remarks = '' WHERE reservation_id = '" . $reservation_id . "'";

                $connection->query($sql2);
            } elseif ($status != 'Cancelled' && $status != 'Upcoming'){
                $sql2 = "UPDATE `reservation` SET modified_date = '" . date('Y-m-d H:i:s') . "', remarks = 'Ongoing' WHERE reservation_id = '" . $reservation_id . "'";

                $connection->query($sql2);
            } elseif ($status == 'Upcoming'){
                $sql2 = "UPDATE `reservation` SET modified_date = '" . date('Y-m-d H:i:s') . "', remarks = '' WHERE reservation_id = '" . $reservation_id . "'";

                $connection->query($sql2);
            }

            if ($original_order_status == 'Upcoming'){
                $location = "Location: ../pages/order/preorder.php";
                $success_msg = 'You have successfully edited the pre-order.';
            } else {
                $location = "Location: ../pages/order/order.php";
                $success_msg = 'You have successfully edited the order.';
            }

            //send email to customer
            //get email body
            $_SESSION['send_email'] = array(
                'rest_id' => $_SESSION['login_rest_id'],
                'cus_id'  => $cus_id,
                'reservation_id' => $reservation_id,
                'reservation_data' => getReservationFullInfo($connection, $reservation_id),
                'type' => 'edit',
                'reservation_or_preorder' => 'preorder',
                'to' => 'customer'
            );

            //add preorder data
            $_SESSION['send_email']['order_id'] = $_GET['order_id'];
            $_SESSION['send_email']['order_data'] = getOrderFullInfo($connection, $_GET['order_id'], 'admin');

            $restaurant_name = getRestaurant($connection, $_SESSION['login_rest_id'])['rest_name'];
            $customer_email = getCustomer($connection, $cus_id)['email'];

            ob_start(); //Init the output buffering
            include '../../../email/template/reservation.php'; //Include (and compiles) the given file
            $email_body = ob_get_clean(); //Get the buffer and erase it)
            unset($_SESSION['send_email']);

            sendmail($customer_email, $restaurant_name . ' - Reservation ' . $reservation_id . ' Updated', $email_body, $success_msg);

            //send email to restaurant
            //get email body
            $_SESSION['send_email'] = array(
                'rest_id' => $_SESSION['login_rest_id'],
                'cus_id'  => $cus_id,
                'reservation_id' => $reservation_id,
                'reservation_data' => getReservationFullInfo($connection, $reservation_id),
                'type' => 'edit',
                'reservation_or_preorder' => 'preorder',
                'to' => 'restaurant'
            );

            //add preorder data
            $_SESSION['send_email']['order_id'] = $_GET['order_id'];
            $_SESSION['send_email']['order_data'] = getOrderFullInfo($connection, $_GET['order_id'], 'admin');

            $restaurant_email = getRestaurant($connection, $_SESSION['login_rest_id'])['email'];
        
            ob_start(); //Init the output buffering
            include '../../../email/template/reservation.php'; //Include (and compiles) the given file
            $email_body = ob_get_clean(); //Get the buffer and erase it)
            unset($_SESSION['send_email']);

            $success_msg = sendmail($restaurant_email, $restaurant_name . ' - Reservation ' . $reservation_id . ' Updated', $email_body, $success_msg);
        } else {
            $reservation_info = getReservation($connection, $_GET['reservation_id']);

            if ($reservation_info['remarks'] == 'Ongoing')
                $status = 'Pending';

            $sql = "INSERT INTO `order`(rest_id, cus_id, `table_id`, item_quantity, voucher_id, reservation_id, `status`, additional_notes, remarks) VALUES ('" . $_SESSION['login_rest_id'] . "','" . $cus_id . "','" . $reservation_info['table_id'] . "','" .  json_encode($food_qty) . "','" . $voucher_id . "','" . $reservation_info['reservation_id'] . "','" . $status . "','" . $additional_notes . "','" .  $remarks . "')";
            $connection->query($sql);

            $inserted_id = $connection->query('SELECT LAST_INSERT_ID() AS id')->fetch_array(MYSQLI_ASSOC)['id'];

            $cus_rest_history_result = getCustomerRestaurantHistory($connection, $cus_id, $_SESSION['login_rest_id']);
            if (count($cus_rest_history_result) == 0){
                $sql2 = "INSERT INTO cus_rest_history(cus_id, rest_id) VALUES ('" . $cus_id . "','" .  $_SESSION['login_rest_id'] . "')";
                $connection->query($sql2);
            }
            
            if ($status == 'Upcoming'){
                $success_msg = 'You have successfully added the pre-order.';
                $location = "Location: ../pages/order/preorder.php";
            } else {
                $success_msg = 'You have successfully added the order.';
                $location = "Location: ../pages/order/order.php";
            }

            //send email to customer
            //get email body
            $_SESSION['send_email'] = array(
                'rest_id' => $_SESSION['login_rest_id'],
                'cus_id'  => $cus_id,
                'reservation_id' => $reservation_info['reservation_id'],
                'reservation_data' => getReservationFullInfo($connection, $reservation_info['reservation_id']),
                'type' => 'add',
                'reservation_or_preorder' => 'preorder',
                'to' => 'customer'
            );

            //add preorder data
            $_SESSION['send_email']['order_id'] = $inserted_id;
            $_SESSION['send_email']['order_data'] = getOrderFullInfo($connection, $inserted_id, 'admin');

            $restaurant_name = getRestaurant($connection, $_SESSION['login_rest_id'])['rest_name'];
            $customer_email = getCustomer($connection, $cus_id)['email'];

            ob_start(); //Init the output buffering
            include '../../../email/template/reservation.php'; //Include (and compiles) the given file
            $email_body = ob_get_clean(); //Get the buffer and erase it)
            unset($_SESSION['send_email']);

            sendmail($customer_email, $restaurant_name . ' - Reservation ' . $reservation_info['reservation_id'] . ' Updated', $email_body, $success_msg);

            //send email to restaurant
            //get email body
            $_SESSION['send_email'] = array(
                'rest_id' => $_SESSION['login_rest_id'],
                'cus_id'  => $cus_id,
                'reservation_id' => $reservation_info['reservation_id'],
                'reservation_data' => getReservationFullInfo($connection, $reservation_info['reservation_id']),
                'type' => 'add',
                'reservation_or_preorder' => 'preorder',
                'to' => 'restaurant'
            );

            //add preorder data
            $_SESSION['send_email']['order_id'] = $inserted_id;
            $_SESSION['send_email']['order_data'] = getOrderFullInfo($connection, $inserted_id, 'admin');

            $restaurant_email = getRestaurant($connection, $_SESSION['login_rest_id'])['email'];
        
            ob_start(); //Init the output buffering
            include '../../../email/template/reservation.php'; //Include (and compiles) the given file
            $email_body = ob_get_clean(); //Get the buffer and erase it)
            unset($_SESSION['send_email']);

            $success_msg = sendmail($restaurant_email, $restaurant_name . ' - Reservation ' . $reservation_info['reservation_id'] . ' Updated', $email_body, $success_msg);
        }

        //update inventory stock
        if ($update_stock && $food_ingredients){
            foreach ($food_ingredients as $food_id => $ingredients){
                foreach ($ingredients as $id => $qty){
                    //get ingredient info
                    $ingredient_info = getInventory($connection, $id);
                    if ($ingredient_info['current_stock_2'] == '0'){
                        $to_reduce = ceil($qty / $ingredient_info['unit_convert_qty2']);
                        $current_stock = $ingredient_info['current_stock'] - $to_reduce;
                        $current_stock_2 = ($ingredient_info['unit_convert_qty2'] * $to_reduce) - $qty;
                    } elseif ($ingredient_info['current_stock_2'] < $qty){
                        $qty_left = $qty - $ingredient_info['current_stock_2'];
                        $to_reduce = ceil($qty_left / $ingredient_info['unit_convert_qty2']);
                        $current_stock = $ingredient_info['current_stock'] - $to_reduce;
                        $current_stock_2 = ($ingredient_info['unit_convert_qty2'] * $to_reduce) - $qty_left;
                    } else {
                        $current_stock = $ingredient_info['current_stock'];
                        $current_stock_2 = $ingredient_info['current_stock_2'] - $qty;
                    }
                    if ($current_stock < 0) $current_stock = 0;
                    if ($current_stock_2 < 0) $current_stock_2 = 0;

                    $sql3 = "UPDATE `inventory` SET `current_stock` = '" . $current_stock . "', current_stock_2 = '" . $current_stock_2 . "' WHERE inventory_id = '" . $id . "'";
                    $connection->query($sql3);

                    //disable food item if this ingredient need to restock
                    if ($current_stock < $ingredient_info['reorder_level'] || ($current_stock == $ingredient_info['reorder_level'] && $current_stock_2 <= $ingredient_info['reorder_level_2'])){
                        //get food items that need this ingredient
                        $food_contains_ingredients = getFoodMenuItems($connection, $_SESSION['login_rest_id'], ['ingredient_id' => $id]);
                        if ($food_contains_ingredients){
                            foreach ($food_contains_ingredients as $food){
                                $unavailable_condition = json_decode($food['unavailable_condition'], true);
                                if (!in_array($id, $unavailable_condition['lack_ingredient']))
                                    $unavailable_condition['lack_ingredient'][] = $id;

                                $sql4 = "UPDATE `food_item` SET `status` = '0', unavailable_condition = '" . json_encode($unavailable_condition) . "' WHERE item_id = '" . $food['item_id'] . "'";
                                $connection->query($sql4);

                                if ($current_stock == 0 && $current_stock_2 == 0)
                                    disableOrdersDueToFoodItem($connection, $_SESSION['login_rest_id'], $food['item_id'], $food['item_name'], 'order');
                            }
                        }
                    }
                }
            }
        }

        //update customer points
        if ($status == 'Completed'){
            if (isset($_POST['grand_total']))
                $final_total = $_POST['grand_total'];
            elseif (isset($_POST['total']))
                $final_total = $_POST['total'];

            $points = floor($final_total);

            $sql5 = "UPDATE `cus_points` SET points = points + " . $points . " WHERE cus_id = '" . $cus_id . "'";
            $connection->query($sql5);
        }

        $_SESSION['success'] = $success_msg;
        header($location);
        
        exit;
    }

    if (isset($_POST['update_order_list'])){
        session_start();

        include_once '../../../db_connect.php';

        $orders = getOrders($connection, $_SESSION['login_rest_id'], ['not_status' => 'Upcoming', 'exclude_cancelled_disabled_preorders' => true]);

        if (count($orders) > $_POST['total_count'])
            echo true;
        else
            echo false;

        exit;
    }

    if (isset($_POST['update_preorder_list'])){
        session_start();

        include_once '../../../db_connect.php';

        $orders = getOrders($connection, $_SESSION['login_rest_id'], ['pre_order' => true]);

        if (count($orders) > $_POST['total_count'])
            echo true;
        else
            echo false;

        exit;
    }

?>