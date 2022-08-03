<?php

    function getFoodMenuItems($connection, $rest_id, $filter_data = array()){
        $sql = "SELECT * FROM `food_item` WHERE `rest_id` = '" . $rest_id . "'";

        if (isset($filter_data['status'])){
            $sql .= " AND `status` = '" . $filter_data['status'] .  "'";
        }

        if (isset($filter_data['ingredient_id'])){
            $sql .= " AND JSON_VALID(ingredients) AND JSON_CONTAINS_PATH(ingredients, 'all', '$.\"" . $filter_data['ingredient_id'] . "\"')";
        }

        if (isset($filter_data['order_by']) && isset($filter_data['asc_desc'])){
            $sql .= " ORDER BY " . $filter_data['order_by'] . " " . $filter_data['asc_desc'];
        }

        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    function getFoodMenuItem($connection, $item_id){
        $sql = "SELECT * FROM `food_item` WHERE `item_id` = '" . $item_id . "'";
        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        return $results;
    }

    function getACategoryLastFoodItemCode($connection, $category_id, $rest_id){
        $sql = "SELECT item_code FROM food_item WHERE category_id = '" . $category_id . "' AND rest_id = '" . $rest_id . "' ORDER BY item_id DESC LIMIT 1";

        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        return $results;
    }

    function disableOrdersDueToFoodItem($connection, $rest_id, $food_id, $item_name, $order_type = 'all'){
        include_once '../helpers/order.php';
        include_once '../helpers/restaurant.php';
        include_once '../helpers/reservation.php';
        include_once '../../../email/send_email.php';
        include_once '../../../store/php/helpers/customer.php';

        date_default_timezone_set("Asia/Kuala_Lumpur");

        if ($order_type == 'all')
            $orders = getOrders($connection, $rest_id, ['food_item_id' => $food_id, 'statuses' => ['Pending', 'Upcoming']]);
        elseif ($order_type == 'order')
            $orders = getOrders($connection, $rest_id, ['food_item_id' => $food_id, 'status' => 'Pending']);

        if ($orders){
            foreach ($orders as $order){
                $sql2 = "UPDATE `order` SET `status` = 'Disabled', remarks = '" . $item_name . " is unavailable', voucher_id = 0, promotions = null, modified_date = '" . date('Y-m-d H:i:s') . "' WHERE order_id = '" . $order['order_id'] . "'";
                
                $connection->query($sql2);

                if ($order['reservation_id'] == 0){
                    //send email to customer
                    //get email body
                    $_SESSION['send_email'] = array(
                        'rest_id' => $rest_id,
                        'cus_id'  => $order['cus_id'],
                        'order_id' => $order['order_id'],
                        'order_data' => getOrderFullInfo($connection, $order['order_id'], 'admin'),
                        'type' => 'edit',
                        'to' => 'customer'
                    );

                    $restaurant_name = getRestaurant($connection, $rest_id)['rest_name'];
                    $customer_email = getCustomer($connection, $order['cus_id'])['email'];
                
                    ob_start(); //Init the output buffering
                    include '../../../email/template/order.php'; //Include (and compiles) the given file
                    $email_body = ob_get_clean(); //Get the buffer and erase it)
                    unset($_SESSION['send_email']);

                    sendmail($customer_email, $restaurant_name . ' - Order ' . $order['order_id'] . ' Update', $email_body, '');

                    //send email to restaurant
                    //get email body
                    $_SESSION['send_email'] = array(
                        'rest_id' => $rest_id,
                        'cus_id'  => $order['cus_id'],
                        'order_id' => $order['order_id'],
                        'order_data' => getOrderFullInfo($connection, $order['order_id'], 'admin'),
                        'type' => 'edit',
                        'to' => 'restaurant'
                    );

                    $restaurant_email = getRestaurant($connection, $rest_id)['email'];
                
                    ob_start(); //Init the output buffering
                    include '../../../email/template/order.php'; //Include (and compiles) the given file
                    $email_body = ob_get_clean(); //Get the buffer and erase it)
                    unset($_SESSION['send_email']);
                    
                    if (!in_array($restaurant_email, ['veganFood@gmail.com', 'thewestern@gmail.com', 'hotchicken@gmail.com', 'thejapanese@gmail.com'])){
                        sendmail($restaurant_email, $restaurant_name . ' - Order ' . $order['order_id'] . ' Update', $email_body, '');
                    }
                } else {
                    //send email to customer
                    //get email body
                    $_SESSION['send_email'] = array(
                        'rest_id' => $rest_id,
                        'cus_id'  => $order['cus_id'],
                        'reservation_id' => $order['reservation_id'],
                        'reservation_data' => getReservationFullInfo($connection, $order['reservation_id']),
                        'type' => 'edit',
                        'reservation_or_preorder' => 'preorder',
                        'to' => 'customer'
                    );

                    //add preorder data
                    $_SESSION['send_email']['order_id'] = $order['order_id'];
                    $_SESSION['send_email']['order_data'] = getOrderFullInfo($connection, $order['order_id'], 'admin');

                    $restaurant_name = getRestaurant($connection, $rest_id)['rest_name'];
                    $customer_email = getCustomer($connection, $order['cus_id'])['email'];

                    ob_start(); //Init the output buffering
                    include '../../../email/template/reservation.php'; //Include (and compiles) the given file
                    $email_body = ob_get_clean(); //Get the buffer and erase it)
                    unset($_SESSION['send_email']);

                    sendmail($customer_email, $restaurant_name . ' - Reservation ' . $order['reservation_id'] . ' Updated', $email_body, '');

                    //send email to restaurant
                    //get email body
                    $_SESSION['send_email'] = array(
                        'rest_id' => $rest_id,
                        'cus_id'  => $order['cus_id'],
                        'reservation_id' => $order['reservation_id'],
                        'reservation_data' => getReservationFullInfo($connection, $order['reservation_id']),
                        'type' => 'edit',
                        'reservation_or_preorder' => 'preorder',
                        'to' => 'restaurant'
                    );

                    //add preorder data
                    $_SESSION['send_email']['order_id'] = $order['order_id'];
                    $_SESSION['send_email']['order_data'] = getOrderFullInfo($connection, $order['order_id'], 'admin');

                    $restaurant_email = getRestaurant($connection, $rest_id)['email'];
                
                    ob_start(); //Init the output buffering
                    include '../../../email/template/reservation.php'; //Include (and compiles) the given file
                    $email_body = ob_get_clean(); //Get the buffer and erase it)
                    unset($_SESSION['send_email']);

                    if (!in_array($restaurant_email, ['veganFood@gmail.com', 'thewestern@gmail.com', 'hotchicken@gmail.com', 'thejapanese@gmail.com'])){
                        sendmail($restaurant_email, $restaurant_name . ' - Reservation ' . $order['reservation_id'] . ' Updated', $email_body, '');
                    }
                }
            }
        }
    }

    //to get data for details modal
    if (isset($_POST['id'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../helpers/category.php';
        include_once '../helpers/inventory.php';
        include_once '../helpers/promotion.php';
        include_once '../../../session.php';

        updateLastActivity();

        $food_menu_item = getFoodMenuItem($connection, $_POST['id']);

        $category = getCategory($connection, $food_menu_item['category_id']);

        //ingredients
        $ingredients = [];

        if (!empty($food_menu_item['ingredients'])){
            $ingredients_results = json_decode($food_menu_item['ingredients'], true);

            foreach ($ingredients_results as $key => $ingredients_result){
                $ingredient_name = getInventory($connection, $key)['item_name'];
                $ingredients[$ingredient_name]['qty'] = $ingredients_result;
                $ingredients[$ingredient_name]['unit'] = getInventory($connection, $key)['unit_2'];
            }
        }

        //promotions
        $promotions = [];

        $promotion_results = getPromotions($connection, $_SESSION['login_rest_id'], ['food_item_id' => $_POST['id']]);
        if ($promotion_results){
            foreach ($promotion_results as $promotion_result){
                if ($promotion_result['status'] == '1'){
                    if ($promotion_result['settings_data'])
                        $details = json_decode($promotion_result['settings_data'], true);
                    if ($promotion_result['type_code'] == 'BOGO'){
                        $promotions[] = 'Buy 1 Get 1 Free';
                    } elseif ($promotion_result['type_code'] == 'multi_buy'){
                        $promotions[] = 'Buy ' . $details['amount_1'] . ' Get ' . $details['amount_2'] . ' Free';
                    } elseif ($promotion_result['type_code'] == 'percent_off'){
                        $promotions[] = $details['percentage'] . ' % Offer';
                    } elseif ($promotion_result['type_code'] == 'dollar_dis'){
                        $food_item_info = getFoodMenuItem($connection, json_decode($promotion_result['food_items'], true)[0]);
                        $promotions[] = 'Original Price: RM ' . number_format($food_item_info['price'], 2, '.', '') . '<br>Discounted Price: RM ' . number_format($details['discounted_price'], 2, '.', '');
                    }
                }
            }
        }

        $final_result = [];

        $final_result['Image'] = $food_menu_item['image'];
        $final_result['Item Code'] = $food_menu_item['item_code'];
        $final_result['Item Name'] = $food_menu_item['item_name'];
        $final_result['Category'] = $category['category_name'];
        $final_result['Description'] = $food_menu_item['description'];
        $final_result['Price'] = "RM " . number_format($food_menu_item['price'], 2, '.', '');
        $final_result['Status'] = $food_menu_item['status'] == 1 ? '<span class="badge bg-success">Available</span>' : '<span class="badge bg-secondary">Unavailable</span>';
        $final_result['Ingredient'] = $ingredients;

        if ($promotions){
            $final_result['Promotions'] = $promotions;
        }

        echo json_encode($final_result);
        exit;
    }

    //generate new item code based on category selected
    if (isset($_POST['category_id'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../helpers/category.php';
        include_once '../../../session.php';

        updateLastActivity();

        $category_code = getCategory($connection, $_POST['category_id'])['code'];
        $last_item = getACategoryLastFoodItemCode($connection, $_POST['category_id'], $_SESSION['login_rest_id']);
        if ($last_item){
            $last_item_code = $last_item['item_code'];
            $last_item_code_num = (int)substr($last_item_code, strpos($last_item_code, '-') + 1);
        } else {
            $last_item_code_num = 0;
        }
        $new_item_code = $category_code . '-' . (string)($last_item_code_num + 1);

        echo $new_item_code;
        exit;
    }

    //check form
    if (isset($_POST['submit_check_food_menu'])){
        
        session_start();

        include_once '../../../db_connect.php';
        include_once '../helpers/category.php';
        include_once '../helpers/inventory.php';
        include_once '../../../session.php';

        updateLastActivity();

        $item_name     = trim($_POST['item-name']);
        $category      = $_POST['category'];
        $image         = $_POST['image'];
        $description   = trim($_POST['description']);
        $price         = trim($_POST['price']);

        if (isset($_POST['ingredient']))
            $ingredients = $_POST['ingredient'];
        else
            $ingredients = array();

        $json = [];

        //if the food ingredient is lack, then cannot enable this food item until refresh stock
        if (!empty($_POST['food_menu_item_id']) && $_POST['status'] == '1'){
            $food_info = getFoodMenuItem($connection, $_POST['food_menu_item_id']);
            $unavailable_condition = json_decode($food_info['unavailable_condition'], true);

            if (array_key_exists('lack_ingredient', $unavailable_condition)){
                $json['enable_error'] = "Unable to enable the food item. Ingredient(s) stock for this food item is currently lower than restock level.";
                echo json_encode($json);
                exit;
            } elseif (array_key_exists('disabled_ingredient', $unavailable_condition)){
                $json['enable_error'] = "Unable to enable the food item. Ingredient(s) stock is disabled.";
                echo json_encode($json);
                exit;
            }
        }

        //error checking
        //item name
        if (empty($item_name))
            $json['error']['item-name'] = "Please enter the item name!";
        elseif (strlen($item_name) > 100)
            $json['error']['item-name'] = "Item name must be between 1 and 100 characters!";

        //category
        if ($category['category'] == '0')
            $json['error']['category'] = "Please select a category!";
        elseif ($category['category'] == 'others'){
            $category_others_name = trim($category['category_name']);
            $category_others_code = trim($category['category_code']);
            $current_categories = getCategories($connection, 'food_menu');
            $current_category_names = array_column($current_categories, 'category_name');
            $current_category_codes = array_column($current_categories, 'code');

            if (empty($category_others_name))
                $json['error']['category_others_name'] = "Please enter the category name!";
            elseif (strlen($category_others_name) > 300)
                $json['error']['category_others_name'] = "Category name must be between 1 and 300 characters!";
            elseif (in_array($category_others_name, $current_category_names))
                $json['error']['category_others_name'] = "This name has already been used!";

            if (empty($category_others_code))
                $json['error']['category_others_code'] = "Please enter the category code!";
            elseif (strlen($category_others_code) > 5)
                $json['error']['category_others_code'] = "Category code must be between 1 and 5 characters!";
            elseif (in_array(strtoupper($category_others_code), $current_category_codes))
                $json['error']['category_others_code'] = "This code has already been used! Please try another code";
        }

        // image
        if (empty($_POST['food_menu_item_id']) && empty($image))
            $json['error']['image'] = "Please select an image!";

        // description
        if (empty($description))
            $json['error']['description'] = "Please enter the description!";

        //price
        if (empty($price))
            $json['error']['price'] = "Please enter the price!";
        else{
            $price = str_replace(',', '', $price);
            if (!is_numeric($price))
                $json['error']['price'] = "Price must be numeric!";
            elseif ($price < 0)
                $json['error']['price'] = "Price cannot less than zero!";
        }

        //ingredient
        foreach ($ingredients as $key => $ingredient){
            if ($ingredient['inventory_id'] == '0')
                $json['error']['ingredient_item_' . (string)$key] = "Please select an item!";
            
            if (empty($ingredient['quantity']))
                $json['error']['ingredient_qty_' . (string)$key] = "Please enter the quantity!";
            else{
                $ingredient['quantity'] = str_replace(',', '', $ingredient['quantity']);
                if (!is_numeric($ingredient['quantity']))
                    $json['error']['ingredient_qty_' . (string)$key] = "Quantity must be numeric!";
                elseif ($ingredient['quantity'] <= 0)
                    $json['error']['ingredient_qty_' . (string)$key] = "Quantity cannot less than or equal to zero!";
                elseif ($ingredient['inventory_id'] != '0') {
                    $selected_ingredient = getInventory($connection, $ingredient['inventory_id']);
                    if ($selected_ingredient['unit_convert_qty1'] == 1 && $selected_ingredient['unit_convert_qty2'] == 1 && $ingredient['quantity'] > $selected_ingredient['total_stock'])
                        $json['error']['ingredient_qty_' . (string)$key] = "This quantity has exceeded the total stock!";
                }
            }           
        }

        //check repeat ingredient
        $ingredient_selected = [];
        foreach ($ingredients as $key => $ingredient){
            if ($ingredient['inventory_id'] != '0' && in_array($ingredient['inventory_id'], $ingredient_selected)){
                $json['error']['ingredient_item_' . (string)$key] = "This ingredient is already selected!";
            } elseif ($ingredient['inventory_id'] != '0') {
                $ingredient_selected[] = $ingredient['inventory_id'];
            }
        }

        echo json_encode($json);
        exit;
    }

    //submit form
    if (isset($_POST['submit_form_food_menu'])){
        
        session_start();

        include_once '../../../db_connect.php';
        include_once '../helpers/inventory.php';
        include_once '../../../session.php';

        updateLastActivity();

        $item_code     = $_POST['item-code'];
        $item_name     = trim($_POST['item-name']);
        $category      = $_POST['category'];
        $description   = trim($_POST['description']);
        $price         = trim($_POST['price']);
        $status        = $_POST['status'];

        if (isset($_POST['ingredient']))
            $ingredients = $_POST['ingredient'];
        else
            $ingredients = array();
        
        //if category is Others
        if ($category['category'] == 'others'){
            $sql = "INSERT INTO `category`(section, category_name, code) VALUES ('food_menu','" . $category['category_name'] . "','" .  strtoupper($category['category_code']) . "')";
        
            $connection->query($sql);
            $category_id = $connection->insert_id;
            $item_code = strtoupper($category['category_code']) . "-1";

        } else {
            $category_id = $category['category'];
        }

        //price
        $price = number_format((float)$price, 2, '.', '');

        //ingredient
        if (count($ingredients) > 0)
            $ingredients = array_column($ingredients, 'quantity', 'inventory_id');

        // image
        if (isset($_FILES['image']['size']) && $_FILES['image']['size'] > 0){
            $name      = $_FILES['image']['name'];
            $tmp_name  = $_FILES['image']['tmp_name'];
            $img_error = $_FILES['image']['error'];

            if ($img_error === 0){
                //get image extension
                $img_ext = pathinfo($name, PATHINFO_EXTENSION);
                
                //convert image extension to lower case
                $img_ext_lower = strtolower($img_ext);

                //allowed image extensions
                $allowed_ext = array("jpg", "jpeg", "png");

                if (in_array($img_ext_lower, $allowed_ext)) {
                    //delete file for existing food item
                    if (isset($_GET['item_id'])){
                        $current_item = getFoodMenuItem($connection,  $_GET['item_id']);
                        $current_file_name = $current_item['image'];
                        unlink('../../uploads/food_menu_photo/' . $current_file_name);
                    }
                   
                    //rename image name
                    $new_file_name = $_SESSION['login_rest_id']. '(' . $item_code . ').' . $img_ext_lower;

                    //image upload path 
                    $upload_path = '../../uploads/food_menu_photo/' . $new_file_name;

                    //move uploaded img to folder
                    move_uploaded_file($tmp_name, $upload_path);
                }
            } else {
                $_SESSION['error'] = "Unknown error occured! Please reload and try again.";
                header("Location: ../pages/food_menu/food_menu.php");
                exit;
            }
        }

        //create or edit food item
        if (isset($_GET['item_id'])){
            $sql = "UPDATE `food_item` SET `item_code` = '" . $item_code . "', item_name = '" . $item_name . "', status = '" . $status . "', category_id = '" . $category_id . "', description = '" . $description . "', price = '" . $price . "', ingredients = '" . json_encode($ingredients) . "' WHERE item_id = '" . $_GET['item_id'] . "'";

            $connection->query($sql);

            $item_id = $_GET['item_id'];

            //if disable the food then need to disable orders and preorders
            if ($status == 0){
                disableOrdersDueToFoodItem($connection, $_SESSION['login_rest_id'], $item_id, $item_name);
            }

            $success_msg = 'You have successfully edited the item (' . $item_code . ')';
        } else {
            //check if ingredients lack
            $lack_ingredient = [];
            $disabled_ingredient = [];
            foreach($ingredients as $ingredient_id => $value){
                $ingredient_info = getInventory($connection, $ingredient_id);

                if ($ingredient_info['current_stock'] < $ingredient_info['reorder_level'] || ($ingredient_info['current_stock'] == $ingredient_info['reorder_level'] && $ingredient_info['current_stock_2'] <= $ingredient_info['reorder_level_2'])){
                    $lack_ingredient[] = $ingredient_id;
                }

                if ($ingredient_info['status'] == 0)
                    $disabled_ingredient[] = $ingredient_id;
            }

            if ($status == '0')
                $final_status = 0;
            elseif ($lack_ingredient)
                $final_status = 0;
            elseif ($disabled_ingredient)
                $final_status = 0;
            else
                $final_status = 1;

            if ($lack_ingredient || $disabled_ingredient){
                if ($lack_ingredient)
                    $unavailable_condition['lack_ingredient'] = $lack_ingredient;
                if ($disabled_ingredient)
                    $unavailable_condition['disabled_ingredient'] = $disabled_ingredient;
            } else
                $unavailable_condition = [];
                

            $sql = "INSERT INTO `food_item`(rest_id, `image`, item_code, item_name, `description`, category_id, price, ingredients, `status`, unavailable_condition) VALUES ('" . $_SESSION['login_rest_id'] . "','','" . $item_code . "','" .  $item_name . "','" .  $description . "','" .  $category_id . "','" .  $price . "','" .  json_encode($ingredients) . "','" . $final_status . "', '" . json_encode($unavailable_condition) . "')";

            $success_msg = 'You have successfully added item (' . $item_code . ')';

            $connection->query($sql);

            $item_id = $connection->insert_id;
        }
        
        if (isset($new_file_name) && !isset($_SESSION['error'])){
            $sql2 = "UPDATE `food_item` SET `image` = '" . $new_file_name . "' WHERE item_id = '" . $item_id . "'";

            $connection->query($sql2);
        }

        //update unavailable condition
        $food_info = getFoodMenuItem($connection, $item_id);
        $unavailable_condition = json_decode($food_info['unavailable_condition'], true);
        if ($status == '0'){
            $unavailable_condition['disabled'] = 'true';

            $sql3= "UPDATE `food_item` SET `unavailable_condition` = '" . json_encode($unavailable_condition) . "' WHERE item_id = '" . $item_id . "'";

            $connection->query($sql3);
        } elseif (array_key_exists('disabled', $unavailable_condition)){
            unset($unavailable_condition['disabled']);

            $sql3= "UPDATE `food_item` SET `unavailable_condition` = '" . json_encode($unavailable_condition) . "' WHERE item_id = '" . $item_id . "'";

            $connection->query($sql3);
        }

        $_SESSION['success'] = $success_msg;
        header("Location: ../pages/food_menu/food_menu.php");
        exit;
    }
    
    if (isset($_POST['an_inventory_id'])){
        include_once '../../../db_connect.php';
        include_once '../helpers/inventory.php';
        include_once '../../../session.php';

        updateLastActivity();

        $inventory = getInventory($connection, $_POST['an_inventory_id']);

        echo $inventory['unit_2'];
    }

?>