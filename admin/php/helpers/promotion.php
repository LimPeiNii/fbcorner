<?php

    function getPromotions($connection, $rest_id, $filter_data = array()){
        $sql = "SELECT * FROM promotion WHERE `rest_id` = '" . $rest_id . "'";

        if (isset($filter_data['food_item_id'])){
            $sql .= " AND JSON_VALID(food_items) AND JSON_CONTAINS(food_items, '\"" . $filter_data['food_item_id'] . "\"', '$')";
        }

        if (isset($filter_data['type_code'])){
            $sql .= " AND type_code = '" . $filter_data['type_code'] . "'";
        }

        if (isset($filter_data['not_type_code'])){
            $sql .= " AND type_code != '" . $filter_data['not_type_code'] . "'";
        }

        if (isset($filter_data['type_codes'])){
            $sql .= " AND `type_code` IN ('" . $filter_data['type_codes'][0] . "'";

            foreach ($filter_data['type_codes'] as $key => $type_code){
                if ($key != 0)
                    $sql .= ",'" . $type_code . "'";
            }

            $sql .= ")";
        }

        if (isset($filter_data['status'])){
            $sql .= " AND `status` = '" . $filter_data['status'] . "'";
        }

        if (isset($filter_data['not_promotion_id'])){
            $sql .= " AND `promotion_id` != '" . $filter_data['not_promotion_id'] . "'";
        }

        if (isset($filter_data['settings_data'])){
            $sql .= " AND JSON_VALID(settings_data)";

            foreach ($filter_data['settings_data'] as $key => $value){
                $sql .= " AND (JSON_EXTRACT(settings_data, '$." . $key . "')) = '" . $value . "'";
            }
        }

        if (isset($filter_data['order_by']) && isset($filter_data['asc_desc'])){
            $sql .= " ORDER BY " . $filter_data['order_by'] . " " . $filter_data['asc_desc'];
        }

        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    function getPromotion($connection, $promotion_id){
        $sql = "SELECT * FROM promotion WHERE `promotion_id` = '" . $promotion_id . "'";
        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        return $results;
    }

    function getRedemptionRecord($connection, $promotion_id, $cus_id){
        $sql = "SELECT * FROM cus_promo_redemption WHERE `promotion_id` = '" . $promotion_id . "' AND cus_id = '" . $cus_id . "'";
        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    function getPromotionalAds($connection, $rest_id){
        $sql = "SELECT * FROM promotional_ads WHERE `rest_id` = '" . $rest_id . "'";
        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    function getPromotionalAd($connection, $promotional_ads_id){
        $sql = "SELECT * FROM promotional_ads WHERE `promotional_ads_id` = '" . $promotional_ads_id . "'";
        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        return $results;
    }

    //delete promotion
    if (isset($_POST['selected_group_promotion'])){
        
        session_start();

        include_once '../../../db_connect.php';
        include_once '../helpers/order.php';
        include_once '../../../session.php';

        updateLastActivity();

        $selected_groups = $_POST['selected_group_promotion'];
        $counter = 0;
        foreach ($selected_groups as $selected_group){
            $sql = "DELETE FROM `promotion` WHERE promotion_id = '" . $selected_group . "'";
            $connection->query($sql);

            $sql2 = "DELETE FROM `cus_promo_redemption` WHERE promotion_id = '" . $selected_group . "'";
            $connection->query($sql2);

            //if is promo code type promotion then remove record from uncompleted order
            $orders = getOrders($connection, $_SESSION['login_rest_id'], ['promo_code_id' => $selected_group, 'not_statuses' => ['Completed', 'Upcoming', 'Cancelled', 'Disabled']]);

            if ($orders){
                foreach ($orders as $order){
                    $sql3 = "UPDATE `order` SET `promotions` = null WHERE order_id = '" . $order['order_id'] . "'";
                    $connection->query($sql3);
                }
            }

            $counter++;
        }
        
        $_SESSION['success'] = "You have successfully deleted " . (string)$counter . " promotion(s)."; 
        exit;
    }

    //reset redemption chances
    if (isset($_POST['reset_promo_redemption_ids'])){
        
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $promo_ids = $_POST['reset_promo_redemption_ids'];
        $counter = 0;
        foreach ($promo_ids as $promo_id){
            $promo_info = getPromotion($connection, $promo_id);
            $max_redemption = json_decode($promo_info['settings_data'], true)['max_redemption'];

            $sql = "UPDATE `cus_promo_redemption` SET `redemption_left` = '" . $max_redemption . "' WHERE promotion_id = '" . $promo_id . "'";
            $connection->query($sql);

            $counter++;
        }
        
        $_SESSION['success'] = "You have successfully reset the redemption chances of " . (string)$counter . " promotion(s)."; 
        exit;
    }

    //check form
    if (isset($_POST['submit_check_promotion'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $json = [];

        //error checking
        //promotion type
        if ($_POST['promo-type'] == '*')
            $json['error']['promo-type'] = "Please select a promotion type!";
            
        if ($_POST['promo-type'] != '*'){
            //details
            if ($_POST['promo-type'] == 'BOGO'){
                $promo_results = getPromotions($connection, $_SESSION['login_rest_id'], ['type_code' => $_POST['promo-type'], 'status' => '1', 'not_promotion_id' => $_POST['promo_id']]);

                if ($promo_results){
                    $json['warning'] = 'There is an active (Buy 1 Get 1) type promotion, please disable it before adding a new one!';
                }
            } elseif ($_POST['promo-type'] == 'multi_buy'){
                //multi-buys
                if (empty($_POST['multi-buy-amount-1']))
                    $json['error']['multi-buy-amount-1'] = "Please enter an amount!";
                elseif ($_POST['multi-buy-amount-1'] < 2)
                    $json['error']['multi-buy-amount-1'] = "This amount cannot less than two!";
                
                if (empty($_POST['multi-buy-amount-2']))
                    $json['error']['multi-buy-amount-2'] = "Please enter an amount!";
                elseif ($_POST['multi-buy-amount-2'] < 1)
                    $json['error']['multi-buy-amount-2'] = "This amount cannot less than one!";
                elseif (!empty($_POST['multi-buy-amount-1']) && $_POST['multi-buy-amount-1'] >= 2){
                    $settings_data_filter = array(
                        'amount_1' => $_POST['multi-buy-amount-1'],
                        'amount_2' => $_POST['multi-buy-amount-2']
                    );
                    $promo_results = getPromotions($connection, $_SESSION['login_rest_id'], ['type_code' => $_POST['promo-type'], 'status' => '1', 'settings_data' => $settings_data_filter, 'not_promotion_id' => $_POST['promo_id']]);
                    
                    if ($promo_results){
                        $json['warning'] = 'There is an active (Buy ' . $_POST["multi-buy-amount-1"] . ' Get ' . $_POST["multi-buy-amount-2"] . ') type promotion, please disable it before adding a new one!';
                    }
                }

            } elseif ($_POST['promo-type'] == 'percent_off'){
                //percent off
                if (empty($_POST['percent-off-percent']))
                    $json['error']['percent-off-percent'] = "Please enter the discount percentage!";
                elseif ($_POST['percent-off-percent'] < 1)
                    $json['error']['percent-off-percent'] = "Discount percentage cannot less than one!";
                else{
                    $settings_data_filter = array(
                        'percentage' => $_POST['percent-off-percent']
                    );
                    $promo_results = getPromotions($connection, $_SESSION['login_rest_id'], ['type_code' => $_POST['promo-type'], 'status' => '1', 'settings_data' => $settings_data_filter, 'not_promotion_id' => $_POST['promo_id']]);
                    
                    if ($promo_results){
                        $json['warning'] = 'There is an active (' . $_POST["percent-off-percent"] . ' % Offer) type promotion, please disable it before adding a new one!';
                    }
                }

            } elseif ($_POST['promo-type'] == 'dollar_dis'){
                //dollar discount
                if (empty($_POST['dollar-dis-price']))
                    $json['error']['dollar-dis-price'] = "Please enter the discounted price!";
                elseif ($_POST['dollar-dis-price'] < 1)
                    $json['error']['dollar-dis-price'] = "Discounted price cannot less than one!";

            } elseif ($_POST['promo-type'] == 'promo_code'){
                //promotional code
                $promo_code_name = trim($_POST['promo-code-name']);
                $description = trim($_POST['description']);

                if (empty($promo_code_name))
                    $json['error']['promo-code-name'] = "Please enter the promotional code name!";
                else{
                    $settings_data_filter = array(
                        'promo_code_name' => $promo_code_name
                    );
                    $promo_results = getPromotions($connection, $_SESSION['login_rest_id'], ['type_code' => $_POST['promo-type'], 'status' => '1', 'settings_data' => $settings_data_filter, 'not_promotion_id' => $_POST['promo_id']]);
                    
                    if ($promo_results){
                        $json['warning'] = 'There is an active promotion with promotional code named (' . $promo_code_name . '), please disable it before adding a new one!';
                    }
                }
                
                if (empty($_POST['promo-code-min-price']))
                    $json['error']['promo-code-min-price'] = "Please enter the minimum spend!";
                elseif ($_POST['promo-code-min-price'] < 1)
                    $json['error']['promo-code-min-price'] = "Minimum spend cannot less than one!";

                if (empty($_POST['promo-code-dis-price']))
                    $json['error']['promo-code-dis-price'] = "Please enter the actual discount!";
                elseif ($_POST['promo-code-dis-price'] < 1)
                    $json['error']['promo-code-dis-price'] = "Thee actual discount cannot less than one!";

                if (empty($description))
                    $json['error']['description'] = "Please enter the description!";

                if (empty($_POST['promo-code-max-redemption']))
                    $json['error']['promo-code-max-redemption'] = "Please enter the redemption limit!";
                elseif ($_POST['promo-code-max-redemption'] < 1)
                    $json['error']['promo-code-max-redemption'] = "The redemption limit cannot less than one!";
            }

            //food items
            if ($_POST['promo-type'] == 'BOGO' || $_POST['promo-type'] == 'multi_buy' || $_POST['promo-type'] == 'percent_off'){
                if (!isset($_POST['food_items']))
                    $json['error']['food-items'] = "Please select at least one food item!";
            } elseif ($_POST['promo-type'] == 'dollar_dis'){
                if (!isset($_POST['food-item']))
                    $json['error']['food-item'] = "Please select a food item!";
                elseif (!isset($json['error']['dollar-dis-price'])){
                    $promo_results = getPromotions($connection, $_SESSION['login_rest_id'], ['type_code' => $_POST['promo-type'], 'status' => '1', 'food_item_id' => $_POST['food-item'], 'not_promotion_id' => $_POST['promo_id']]);

                    if ($promo_results){
                        $json['warning'] = 'Selected food item already has an active (Dollar Discount) type promotion, please disable it before adding a new one!';
                    }
                }
            }
        }
        
        echo json_encode($json);
        exit;
    }

    //submit form (promotion)
    if (isset($_POST['submit_form_promotion'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../helpers/order.php';
        include_once '../../../session.php';

        updateLastActivity();

        $promo_type = $_POST['promo-type'];
        $status     = $_POST['status'];

        date_default_timezone_set("Asia/Kuala_Lumpur");

        if ($promo_type == 'BOGO'){
            //create or edit promotion
            if (isset($_GET['promotion_id'])){
                $sql = "UPDATE `promotion` SET `type_code` = '" . $promo_type . "', settings_data = NULL, food_items = '" . json_encode($_POST['food_items']) . "', description = null, status = '" . $status . "', modified_date = '" . date('Y-m-d H:i:s') . "' WHERE promotion_id = '" . $_GET['promotion_id'] . "'";
                
                $success_msg = 'You have successfully edited the promotion.';
            } else {
                $sql = "INSERT INTO `promotion`(rest_id, type_code, food_items, `status`) VALUES ('" . $_SESSION['login_rest_id'] . "','" . $promo_type . "','" .  json_encode($_POST['food_items']) . "','" . $status . "')";

                $success_msg = 'You have successfully added a promotion.';
            }

            $connection->query($sql);
        } elseif ($promo_type == 'multi_buy'){
            $details['amount_1'] = $_POST['multi-buy-amount-1'];
            $details['amount_2'] = $_POST['multi-buy-amount-2'];

            //create or edit promotion
            if (isset($_GET['promotion_id'])){
                $sql = "UPDATE `promotion` SET `type_code` = '" . $promo_type . "', settings_data = '" . json_encode($details) . "', food_items = '" . json_encode($_POST['food_items']) . "', description = null, status = '" . $status . "', modified_date = '" . date('Y-m-d H:i:s') . "' WHERE promotion_id = '" . $_GET['promotion_id'] . "'";
                
                $success_msg = 'You have successfully edited the promotion.';
            } else {
                $sql = "INSERT INTO `promotion`(rest_id, type_code, settings_data, food_items, `status`) VALUES ('" . $_SESSION['login_rest_id'] . "','" . $promo_type . "','" .  json_encode($details) . "','" .  json_encode($_POST['food_items']) . "','" . $status . "')";

                $success_msg = 'You have successfully added a promotion.';
            }

            $connection->query($sql);
        } elseif ($_POST['promo-type'] == 'percent_off'){
            $details['percentage'] = $_POST['percent-off-percent'];

            //create or edit promotion
            if (isset($_GET['promotion_id'])){
                $sql = "UPDATE `promotion` SET `type_code` = '" . $promo_type . "', settings_data = '" . json_encode($details) . "', food_items = '" . json_encode($_POST['food_items']) . "', description = null, status = '" . $status . "', modified_date = '" . date('Y-m-d H:i:s') . "' WHERE promotion_id = '" . $_GET['promotion_id'] . "'";
                
                $success_msg = 'You have successfully edited the promotion.';
            } else {
                $sql = "INSERT INTO `promotion`(rest_id, type_code, settings_data, food_items, `status`) VALUES ('" . $_SESSION['login_rest_id'] . "','" . $promo_type . "','" .  json_encode($details) . "','" .  json_encode($_POST['food_items']) . "','" . $status . "')";

                $success_msg = 'You have successfully added a promotion.';
            }

            $connection->query($sql);
        } elseif ($_POST['promo-type'] == 'dollar_dis'){
            $details['discounted_price'] = number_format($_POST['dollar-dis-price'], '2', '.', '');
            $food_item[] = $_POST['food-item'];

            //create or edit promotion
            if (isset($_GET['promotion_id'])){
                $sql = "UPDATE `promotion` SET `type_code` = '" . $promo_type . "', settings_data = '" . json_encode($details) . "', food_items = '" . json_encode($food_item) . "', description = null, status = '" . $status . "', modified_date = '" . date('Y-m-d H:i:s') . "' WHERE promotion_id = '" . $_GET['promotion_id'] . "'";
                
                $success_msg = 'You have successfully edited the promotion.';
            } else {
                $sql = "INSERT INTO `promotion`(rest_id, type_code, settings_data, food_items, `status`) VALUES ('" . $_SESSION['login_rest_id'] . "','" . $promo_type . "','" .  json_encode($details) . "','" .  json_encode($food_item) . "','" . $status . "')";

                $success_msg = 'You have successfully added a promotion.';
            }

            $connection->query($sql);
        } elseif ($_POST['promo-type'] == 'promo_code'){
            //promotional code
            $details['promo_code_name'] = trim($_POST['promo-code-name']);
            $details['min_price'] = number_format($_POST['promo-code-min-price'], '2', '.', '');
            $details['actual_discount'] = number_format($_POST['promo-code-dis-price'], '2', '.', '');
            $details['max_redemption'] = $_POST['promo-code-max-redemption'];
            $description = trim($_POST['description']);

            //create or edit promotion
            if (isset($_GET['promotion_id'])){
                $sql = "UPDATE `promotion` SET `type_code` = '" . $promo_type . "', settings_data = '" . json_encode($details) . "', food_items = NULL, `description` = '" . $description . "', status = '" . $status . "', modified_date = '" . date('Y-m-d H:i:s') . "' WHERE promotion_id = '" . $_GET['promotion_id'] . "'";
                $connection->query($sql);

                $promo_code_id = $_GET['promotion_id'];
                
                $success_msg = 'You have successfully edited the promotion.';
            } else {
                $sql = "INSERT INTO `promotion`(rest_id, type_code, settings_data, `description`, `status`) VALUES ('" . $_SESSION['login_rest_id'] . "','" . $promo_type . "','" .  json_encode($details) . "','" . $description . "','" . $status . "')";
                $connection->query($sql);

                $promo_code_id = $connection->insert_id;

                $success_msg = 'You have successfully added a promotion.';
            }

            if (isset($_POST['reset-redemption'])){
                $sql2 = "UPDATE `cus_promo_redemption` SET `redemption_left` = '" . $_POST['promo-code-max-redemption'] . "' WHERE promotion_id = '" . $_GET['promotion_id'] . "'";
                
                $connection->query($sql2);
            }

            if ($status == '0'){
                $sql3 = "DELETE FROM `cus_promo_redemption` WHERE promotion_id = '" . $_GET['promotion_id'] . "'";
                
                $connection->query($sql3);

                //if is promo code type promotion then remove record from uncompleted order
                $orders = getOrders($connection, $_SESSION['login_rest_id'], ['promo_code_id' => $promo_code_id, 'not_statuses' => ['Completed', 'Upcoming', 'Cancelled', 'Disabled']]);

                if ($orders){
                    foreach ($orders as $order){
                        $sql4 = "UPDATE `order` SET `promotions` = null WHERE order_id = '" . $order['order_id'] . "'";
                        $connection->query($sql4);
                    }
                }
            }
        }

        $_SESSION['success'] = $success_msg;
        header("Location: ../pages/promotion/promotion.php");
        exit;
    }

    //get promotion details for popup modal
    if (isset($_POST['get_promo_food_items'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../helpers/food_menu.php';
        include_once '../../../session.php';

        updateLastActivity();

        $result = getPromotion($connection, $_POST['promo_id']);

        //food items
        if (isset($result['food_items'])){
            $foods = json_decode($result['food_items'], true);
            foreach ($foods as $food){
                $food_item_result = getFoodMenuItem($connection, $food);
                $promotion[$food_item_result['item_id']] = $food_item_result['item_name'];
            }
        } else {
            $result['description'] = str_replace("\n", '<br>', $result['description']);
            $promotion['description'] = $result['description'];
        }
        
        echo json_encode($promotion);
        exit;
    }

    if (isset($_POST['get_unavailable_foods'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $promo_results = getPromotions($connection, $_SESSION['login_rest_id'], ['type_codes' => $_POST['promotion_type'], 'status' => '1']);

        $unavailable_foods = [];

        if ($promo_results){
            foreach ($promo_results as $promo_result){
                $food_items = json_decode($promo_result['food_items'], true);
                foreach ($food_items as $food_item){
                    if (!in_array($food_item, $unavailable_foods)){
                        $unavailable_foods[] = $food_item;
                    }
                }
            }
        }

        echo json_encode($unavailable_foods);
        exit;
    }

    //submit form (promotional ads)
    if (isset($_POST['submit_form_ads'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $title   = trim($_POST['title']);
        $content = trim($_POST['content']);

        date_default_timezone_set("Asia/Kuala_Lumpur");

        $json = [];

        //error checking
        //title
        if (empty($title))
            $json['error']['title'] = "Please enter a title!";
        
        //content
        if (empty($content))
            $json['error']['content'] = "Please enter the content!";

        if (!isset($json['error'])){
            //promo ads id
            if (isset($_POST['promotional_ads_id']))
                $ads_id = $_POST['promotional_ads_id'];
            else{
                $sql = "SELECT `auto_increment` FROM INFORMATION_SCHEMA.TABLES WHERE table_name = 'promotional_ads'";
                $statement = $connection->query($sql);
                $ads_id = ($statement->fetch_assoc())['auto_increment'];
            }

            //upload images
            $uploaded_img_names = array();

            foreach ($_FILES as $img_data){
                if (isset($img_data['size']) && $img_data['size'] > 0){
                    $img_name      = $img_data['name'];
                    $img_tmp_name  = $img_data['tmp_name'];
                    $img_error     = $img_data['error'];
                }

                if ($img_error === 0){
                    //get image extension
                    $img_ext = pathinfo($img_name, PATHINFO_EXTENSION);
                    
                    //convert image extension to lower case
                    $img_ext_lower = strtolower($img_ext);

                    //allowed image extensions
                    $allowed_ext = array("jpg", "jpeg", "png");

                    if (in_array($img_ext_lower, $allowed_ext)) {
                        //rename image name
                        $new_img_name = $_SESSION['login_rest_id'] . '-' . $ads_id . '-1.' . $img_ext_lower;

                        //image upload path 
                        $upload_path = '../../uploads/promo_ads_photo/' . $new_img_name;

                        //check if the image name is already used
                        $counter = 1;
                        while (file_exists($upload_path)) {
                            $counter++;
                            $new_img_name = $_SESSION['login_rest_id'] . '-' . $ads_id . '-' . (string)$counter . '.' . $img_ext_lower;
                            $upload_path = '../../uploads/promo_ads_photo/' . $new_img_name;
                        }

                        //move uploaded image to folder
                        move_uploaded_file($img_tmp_name, $upload_path);

                        $uploaded_img_names[] = $new_img_name;
                    }
                } else {
                    //remove uploaded images
                    foreach ($uploaded_img_names as $name) {
                        unlink('../../uploads/promo_ads_photo/' . $name);
                    }

                    $json['error']['upload-imgs'] = 'Unknown error occured! Please try again.';
                    break;
                }
            }
        }

        if (!isset($json['error'])){
            //remove some old images (update)
            if (isset($_POST['promotional_ads_id'])){
                $old_imgs = json_decode($_POST['old_imgs'], true);
                $current_old_imgs = json_decode($_POST['current_old_imgs'], true);
                foreach ($old_imgs as $old_img){
                    if (!in_array($old_img, $current_old_imgs)){
                        //remove file
                        unlink('../../uploads/promo_ads_photo/' . $old_img);
                    }
                }
                
                $latest_imgs = array_merge($current_old_imgs, $uploaded_img_names);
                $sql = "UPDATE promotional_ads SET title = '" . $title . "', content = '" . $content . "', photos = '" . json_encode($latest_imgs) . "', status = " . (int)$_POST['status'] . ", modified_date = '" . date('Y-m-d H:i:s') . "' WHERE promotional_ads_id = " . (int)$_POST['promotional_ads_id'];

                $success_msg = 'You have successfully edited the promotional ads.';
            } else {
                //insert promo ads record
                $sql = "INSERT INTO `promotional_ads`(rest_id, title, content, `photos`, `status`) VALUES ('" . $_SESSION['login_rest_id'] . "','" . $title . "','" . $content . "','" . json_encode($uploaded_img_names) . "'," . (int)$_POST['status'] . ")";

                $success_msg = 'You have successfully added a promotional ads.';
            }

            $connection->query($sql);

            $json['success'] = $success_msg;
            $_SESSION['success'] = $success_msg;
        }

        echo json_encode($json);
        exit;
    }

    //delete promotional ads
    if (isset($_POST['delete_promotional_ads'])){
        
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $selected_promotional_ads = $_POST['selected_promotional_ads'];
        $counter = 0;
        foreach ($selected_promotional_ads as $selected_promotional_ad){
            //remove image
            $images = glob('../../uploads/promo_ads_photo/' . $_SESSION['login_rest_id']. '-' . $selected_promotional_ad . '-*');
            foreach ($images as $image) {
                unlink($image);
            }

            $sql = "DELETE FROM `promotional_ads` WHERE promotional_ads_id = '" . $selected_promotional_ad . "'";
            $connection->query($sql);
            $counter++;
        }
        
        $_SESSION['success'] = "You have successfully deleted " . (string)$counter . " promotional ad(s)."; 
        exit;
    }

?>