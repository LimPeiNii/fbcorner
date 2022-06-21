<?php

    function getInventories($connection, $rest_id){
        $sql = "SELECT * FROM `inventory` WHERE `rest_id` = '" . $rest_id . "' ORDER BY item_code ASC";
        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    function getInventory($connection, $inventory_id){
        $sql = "SELECT * FROM `inventory` WHERE `inventory_id` = '" . $inventory_id . "'";
        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        return $results;
    }

    function getACategoryLastInventoryItemCode($connection, $category_id, $rest_id){
        $sql = "SELECT item_code FROM inventory WHERE category_id = '" . $category_id . "' AND rest_id = '" . $rest_id . "' ORDER BY inventory_id DESC LIMIT 1";

        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        return $results;
    }

    //reset stocks
    if (isset($_POST['all_item_ids'])){
        
        session_start();

        include_once '../../../db_connect.php';
        include_once '../helpers/food_menu.php';
        include_once '../../../session.php';

        updateLastActivity();

        $all_item_ids = $_POST['all_item_ids'];
        $counter = 0;
        foreach ($all_item_ids as $all_item_id){
            $sql = "UPDATE `inventory` SET `current_stock` = `total_stock`, `current_stock_2` = '0' WHERE inventory_id = '" . $all_item_id . "'";
            $connection->query($sql);

            //enable food status that has this as ingredient
            //get food items that need this ingredient
            $food_contains_ingredients = getFoodMenuItems($connection, $_SESSION['login_rest_id'], ['ingredient_id' => $all_item_id]);
            if ($food_contains_ingredients){
                foreach ($food_contains_ingredients as $food){
                    //if the food ingredient is lack, then cannot enable this food item until reset stock
                    $unavailable_condition = json_decode($food['unavailable_condition'], true);

                    if (array_key_exists('lack_ingredient', $unavailable_condition) && in_array($all_item_id, $unavailable_condition['lack_ingredient'])){
                        $index = array_search($all_item_id, $unavailable_condition['lack_ingredient']);
                        unset($unavailable_condition['lack_ingredient'][$index]);

                        if ($unavailable_condition['lack_ingredient']){
                            $sql2 = "UPDATE `food_item` SET `unavailable_condition` = '" . json_encode($unavailable_condition) . "' WHERE item_id = '" . $food['item_id'] . "'";
                            $connection->query($sql2);
                        } else {
                            unset($unavailable_condition['lack_ingredient']);

                            $sql2 = "UPDATE `food_item` SET `unavailable_condition` = '" . json_encode($unavailable_condition) . "' WHERE item_id = '" . $food['item_id'] . "'";
                            $connection->query($sql2);

                            if (!array_key_exists('disabled', $unavailable_condition) && !array_key_exists('disabled_ingredient', $unavailable_condition)){
                                $sql3 = "UPDATE `food_item` SET `status` = '1' WHERE item_id = '" . $food['item_id'] . "'";
                                $connection->query($sql3);
                            }
                        }
                    }
                }
            }
            
            $counter++;
        }
        
        $_SESSION['success'] = "You have successfully reset the stock for " . (string)$counter . " item(s)."; 
        exit;
    }

    //check form
    if (isset($_POST['submit_check_inventory'])){
        
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $item_name         = trim($_POST['item_name']);
        $category          = $_POST['category'];
        $current_stock     = $_POST['current_stock'];
        $current_stock_2   = $_POST['current_stock_2'];
        $reorder_level     = $_POST['reorder_level'];
        $reorder_level_2   = $_POST['reorder_level_2'];
        $total_stock       = $_POST['total_stock'];
        $unit              = trim($_POST['unit']);
        $unit_convert      = $_POST['unit_conversion'];

        $json = [];

        //error checking
        //item name
        if (empty($item_name))
            $json['error']['item-name'] = "Please enter the item name!";
        elseif (strlen($item_name) > 100)
            $json['error']['item-name'] = "Item name must be between 1 and 100 characters!";

        //category
        if ($category == 0)
            $json['error']['category'] = "Please select a category!";

        //current stock
        if ($current_stock == '')
            $json['error']['current-stock'] = "Please enter the current stock for this item!";
        elseif ($current_stock < 0)
            $json['error']['current-stock'] = "Stock amount must be greater than or equal to zero!";

        //current stock 2
        if ($current_stock_2 < 0)
            $json['error']['current-stock-2'] = "Stock amount must be greater than or equal to zero!";

        //reorder level
        if ($reorder_level < 0)
            $json['error']['reorder-level'] = "Reorder level amount must be greater than or equal to zero!";

        //reorder level
        if ($reorder_level_2 < 0)
            $json['error']['reorder-level-2'] = "Reorder level amount must be greater than or equal to zero!";

        //total stock
        if ($total_stock == '')
            $json['error']['total-stock'] = "Please enter the current stock for this item!";
        elseif ($total_stock < 0)
            $json['error']['total-stock'] = "Total stock amount must be greater than or equal to zero!";
        elseif ($current_stock != '' && $current_stock >= 0 && ($current_stock_2 >= 0 || $current_stock_2 == '') && ($total_stock < $current_stock || ($current_stock == $total_stock && $current_stock_2 > 0))){
            $json['error']['total-stock'] = "Total stock amount must be greater than or equal to the current stock!";
        }
        
        //unit
        if (empty($unit))
            $json['error']['unit'] = "Please enter the unit!";
        elseif (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $unit) || preg_match('~[0-9]+~', $unit))
            $json['error']['unit'] = "This unit does not appear to be valid!";
        elseif (strlen($unit) > 10)
            $json['error']['unit'] = "Unit must be between 1 and 10 characters!";

        //unit conversion
        if (!empty($unit_convert[0]) || !empty($unit_convert[1]) || !empty($unit_convert[2])){
            //quantity 1
            if (empty($unit_convert[0]))
                $json['error']['qty1'] = "Please enter the quantity!";
            else{
                $unit_convert[0] = str_replace(',', '', $unit_convert[0]);
                if (!is_numeric($unit_convert[0]))
                    $json['error']['qty1'] = "Quantity must be numeric!";
                elseif ($unit_convert[0] <= 0)
                    $json['error']['qty1'] = "Quantity cannot less than or equal to zero!";
            }
            
            //quantity 2
            if (empty($unit_convert[1]))
                $json['error']['qty2'] = "Please enter the quantity!";
            else{
                $unit_convert[1] = str_replace(',', '', $unit_convert[1]);
                if (!is_numeric($unit_convert[1]))
                    $json['error']['qty2'] = "Quantity must be numeric!";
                elseif ($unit_convert[1] <= 0)
                    $json['error']['qty2'] = "Quantity cannot less than or equal to zero!";
            }

            //unit 2
            if (empty($unit_convert[2]))
                $json['error']['unit2'] = "Please enter the unit!";
            elseif (preg_match('/[\'^£$%&*()};{@#~?><>,|=_+¬-]/', $unit_convert[2]) || preg_match('~[0-9]+~', $unit_convert[2]))
                $json['error']['unit2'] = "This unit does not appear to be valid!";
            elseif (strlen($unit_convert[2]) > 10)
                $json['error']['unit2'] = "Unit must be between 1 and 10 characters!";
        }
        
        echo json_encode($json);
        exit;
    }

    //submit form
    if (isset($_POST['submit_form_inventory'])){
        
        session_start();

        include_once '../../../db_connect.php';
        include_once '../helpers/food_menu.php';
        include_once '../../../session.php';

        updateLastActivity();

        $item_code       = $_POST['item-code'];
        $item_name       = trim($_POST['item-name']);
        $category        = $_POST['category'];
        $current_stock   = $_POST['current-stock'];
        $current_stock_2 = $_POST['current-stock-2'];
        $reorder_level   = $_POST['reorder-level'];
        $reorder_level_2 = $_POST['reorder-level-2'];
        $total_stock     = $_POST['total-stock'];
        $unit            = trim($_POST['unit']);
        $status          = $_POST['status'];
        $unit_convert    = $_POST['unit_convert'];

        //unit conversion data
        if (empty($unit_convert['qty1']) && empty($unit_convert['qty2']) && empty($unit_convert['unit2'])){
            $unit_convert['qty1'] = (double)1;
            $unit_convert['qty2'] = (double)1;
            $unit_convert['unit2'] = $unit;
        }

        //stock data
        if (empty($current_stock_2)){
            $current_stock_2 = (double)0;
        }

        if (empty($reorder_level_2)){
            $reorder_level_2 = (double)0;
        }

        //create or edit inventory
        if (isset($_GET['inventory_id'])){
            $sql = "UPDATE `inventory` SET `item_code` = '" . $item_code . "', item_name = '" . $item_name . "', status = '" . $status . "', category_id = '" . $category . "', current_stock = '" . $current_stock . "', current_stock_2 = '" . $current_stock_2 . "', reorder_level = '" . $reorder_level . "', reorder_level_2 = '" . $reorder_level_2 . "', total_stock = '" . $total_stock . "', unit = '" . $unit . "', unit_2 = '" . $unit_convert['unit2'] . "', unit_convert_qty1 = '" . $unit_convert['qty1'] . "', unit_convert_qty2 = '" . $unit_convert['qty2'] . "' WHERE inventory_id = '" . $_GET['inventory_id'] . "'";
            $connection->query($sql);
            
            $inventory_id = $_GET['inventory_id'];

            //if disable inventory, need to disable food item, order, preoder
            //get food items that need this ingredient
            $food_contains_ingredients = getFoodMenuItems($connection, $_SESSION['login_rest_id'], ['ingredient_id' => $inventory_id]);
            if ($food_contains_ingredients){
                if ($status == 0){
                    foreach ($food_contains_ingredients as $food){
                        $unavailable_condition = json_decode($food['unavailable_condition'], true);
                        if (!in_array($inventory_id, $unavailable_condition['disabled_ingredient']))
                            $unavailable_condition['disabled_ingredient'][] = $inventory_id;

                        $sql2 = "UPDATE `food_item` SET `status` = '0', unavailable_condition = '" . json_encode($unavailable_condition) . "' WHERE item_id = '" . $food['item_id'] . "'";

                        $connection->query($sql2);

                        disableOrdersDueToFoodItem($connection, $_SESSION['login_rest_id'], $food['item_id'], $food['item_name']);
                    }
                } else {
                    //update unavailable_condition
                    foreach ($food_contains_ingredients as $food){
                        $unavailable_condition = json_decode($food['unavailable_condition'], true);

                        if (array_key_exists('disabled_ingredient', $unavailable_condition) && in_array($inventory_id, $unavailable_condition['disabled_ingredient'])){
                            $index = array_search($inventory_id, $unavailable_condition['disabled_ingredient']);
                            unset($unavailable_condition['disabled_ingredient'][$index]);
    
                            if ($unavailable_condition['disabled_ingredient']){
                                $sql2 = "UPDATE `food_item` SET `unavailable_condition` = '" . json_encode($unavailable_condition) . "' WHERE item_id = '" . $food['item_id'] . "'";
                                $connection->query($sql2);
                            } else {
                                unset($unavailable_condition['disabled_ingredient']);
    
                                $sql2 = "UPDATE `food_item` SET `unavailable_condition` = '" . json_encode($unavailable_condition) . "' WHERE item_id = '" . $food['item_id'] . "'";
                                $connection->query($sql2);
    
                                if (!array_key_exists('disabled', $unavailable_condition) && !array_key_exists('lack_ingredient', $unavailable_condition)){
                                    $sql3 = "UPDATE `food_item` SET `status` = '1' WHERE item_id = '" . $food['item_id'] . "'";
                                    $connection->query($sql3);
                                }
                            }
                        }
                    }
                }
            }

            $success_msg = 'You have successfully edited item (' . $item_code . ')';
        } else {
            $sql = "INSERT INTO `inventory`(rest_id, item_code, item_name, category_id, current_stock, current_stock_2, reorder_level, reorder_level_2, total_stock, `status`, unit, unit_2, unit_convert_qty1, unit_convert_qty2) VALUES ('" . $_SESSION['login_rest_id'] . "','" . $item_code . "','" .  $item_name . "','" .  $category . "','" .  $current_stock . "','" .  $current_stock_2 . "','" .  $reorder_level . "','" .  $reorder_level_2 . "','" .  $total_stock . "','" . $status . "','" . $unit . "','" . $unit_convert['unit2'] . "','" . $unit_convert['qty1'] . "','" . $unit_convert['qty2'] . "')";
            $success_msg = 'You have successfully added item (' . $item_code . ')';
            $connection->query($sql);
            
            $inventory_id = $connection->insert_id;
        }     
        
        //get food items that need this ingredient
        $food_contains_ingredients = getFoodMenuItems($connection, $_SESSION['login_rest_id'], ['ingredient_id' => $inventory_id]);
        if ($food_contains_ingredients){
            //disable food item if this ingredient need to restock
            if ($current_stock < $reorder_level || ($current_stock == $reorder_level && $current_stock_2 <= $reorder_level_2)){
                foreach ($food_contains_ingredients as $food){
                    $unavailable_condition = json_decode($food['unavailable_condition'], true);
                    if (!in_array($inventory_id, $unavailable_condition['lack_ingredient']))
                        $unavailable_condition['lack_ingredient'][] = $inventory_id;

                    $sql2 = "UPDATE `food_item` SET `status` = '0', unavailable_condition = '" . json_encode($unavailable_condition) . "' WHERE item_id = '" . $food['item_id'] . "'";
                    $connection->query($sql2);

                    if ($current_stock == 0 && $current_stock_2 == 0)
                        disableOrdersDueToFoodItem($connection, $_SESSION['login_rest_id'], $food['item_id'], $food['item_name'], 'order');
                }
            } else {
                //enable food item if this ingredient has restock (manually)
                foreach ($food_contains_ingredients as $food){
                    //if the food ingredient is lack, then cannot enable this food item until reset stock
                    $unavailable_condition = json_decode($food['unavailable_condition'], true);

                    if (array_key_exists('lack_ingredient', $unavailable_condition) && in_array($inventory_id, $unavailable_condition['lack_ingredient'])){
                        $index = array_search($inventory_id, $unavailable_condition['lack_ingredient']);
                        unset($unavailable_condition['lack_ingredient'][$index]);

                        if ($unavailable_condition['lack_ingredient']){
                            $sql2 = "UPDATE `food_item` SET `unavailable_condition` = '" . json_encode($unavailable_condition) . "' WHERE item_id = '" . $food['item_id'] . "'";
                            $connection->query($sql2);
                        } else {
                            unset($unavailable_condition['lack_ingredient']);

                            $sql2 = "UPDATE `food_item` SET `unavailable_condition` = '" . json_encode($unavailable_condition) . "' WHERE item_id = '" . $food['item_id'] . "'";
                            $connection->query($sql2);

                            if (!array_key_exists('disabled', $unavailable_condition) && !array_key_exists('disabled_ingredient', $unavailable_condition)){
                                $sql3 = "UPDATE `food_item` SET `status` = '1' WHERE item_id = '" . $food['item_id'] . "'";
                                $connection->query($sql3);
                            }
                        }
                    }
                }
            }
        }

        $_SESSION['success'] = $success_msg;
        header("Location: ../pages/inventory/inventory.php");
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
        $last_item = getACategoryLastInventoryItemCode($connection, $_POST['category_id'], $_SESSION['login_rest_id']);
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

?>