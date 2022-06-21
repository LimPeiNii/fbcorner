<?php

    function searchRestaurants($connection, $filter_data = array()){        
        $sql = "SELECT DISTINCT r.rest_id, rest_name, tags, rest_profile, dining_style, r.city, TODAY_OPENING_CLOSING_TIME(r.daily_opening_hours, 'open') AS opening_time, TODAY_OPENING_CLOSING_TIME(r.daily_opening_hours, 'close') AS closing_time, ar.avg_rating, fr.total FROM `restaurant` r LEFT JOIN dining_style ds ON (r.dining_style_id = ds.dining_style_id) LEFT JOIN food_item fi ON (fi.rest_id = r.rest_id) LEFT JOIN category c ON (c.category_id = fi.category_id) LEFT JOIN promotion p ON (p.rest_id = r.rest_id) LEFT JOIN (SELECT AVG(rating) AS avg_rating, o.rest_id FROM rating_review rr LEFT JOIN `order` o ON (rr.order_id = o.order_id) GROUP BY o.rest_id) ar ON (ar.rest_id = r.rest_id) LEFT JOIN (SELECT rest_id, COUNT(*) AS total FROM favourite_restaurant GROUP BY rest_id) fr ON (r.rest_id = fr.rest_id) WHERE 1";

        if (isset($filter_data['keyword'])){
            $sql .= " AND (rest_name LIKE '%" . $filter_data['keyword'] . "%' OR (JSON_VALID(r.tags) AND JSON_SEARCH(LOWER(r.tags), 'all', '%" . $filter_data['keyword'] . "%') IS NOT NULL) OR (ds.dining_style LIKE '%" . $filter_data['keyword'] . "%'";

            if (isset($filter_data['dining_style'])){
                $sql .= " OR ds.dining_style IN ('" . $filter_data['dining_style'][0] . "'";

                foreach ($filter_data['dining_style'] as $key => $dining_style){
                    if ($key != 0)
                        $sql .= ",'" . $dining_style . "'";
                }

                $sql .= ")";
            }
            
            $sql .= "))";
        } elseif (isset($filter_data['dining_style'])){
            $sql .= " AND ds.dining_style IN ('" . $filter_data['dining_style'][0] . "'";

            foreach ($filter_data['dining_style'] as $key => $dining_style){
                if ($key != 0)
                    $sql .= ",'" . $dining_style . "'";
            }

            $sql .= ")";
        }

        if (isset($filter_data['city'])){
            $sql .= " AND city = '" . $filter_data['city'] . "'";
        }

        if (isset($filter_data['promotion'])){
            $sql .= " AND p.status = '1'";
        }

        if (isset($filter_data['category_name'])){
            $sql .= " AND c.category_name IN ('" . $filter_data['category_name'][0] . "'";

            foreach ($filter_data['category_name'] as $key => $category){
                if ($key != 0)
                    $sql .= ",'" . $category . "'";
            }

            $sql .= ")";
        }

        if (isset($filter_data['opening_time'])){
            $sql .= " AND (";

            $counter = 0;
            foreach ($filter_data['opening_time'] as $key => $op){
                $opening_time = substr($key, 13);

                if ($opening_time == 'before-8')
                    $sql .= "TIME(TODAY_OPENING_CLOSING_TIME(r.daily_opening_hours, 'open')) < TIME('08:00')";
                elseif ($opening_time == 'after-20')
                    $sql .= "TIME(TODAY_OPENING_CLOSING_TIME(r.daily_opening_hours, 'open')) > TIME('20:00')";
                else{
                    $start_time = explode('-', $opening_time)[0];
                    $end_time = explode('-', $opening_time)[1];
                    $sql .= "(TIME(TODAY_OPENING_CLOSING_TIME(r.daily_opening_hours, 'open')) >= TIME('" . $start_time . ":00') AND TIME(TODAY_OPENING_CLOSING_TIME(r.daily_opening_hours, 'open')) <= TIME('" . $end_time . ":00'))";
                }

                if (count($filter_data['opening_time']) > 1 && $counter < count($filter_data['opening_time'])-1){
                    $sql .= ' OR ';
                } else {
                    $sql .= ')';
                }

                $counter++;
            }
        }

        if (isset($filter_data['rating'])){
            $sql .= " AND (";

            if (in_array('null', $filter_data['rating'])){
                $sql .= "FLOOR(ar.avg_rating) IS NULL";

                unset($filter_data['rating'][0]);

                if ($filter_data['rating'])
                    $sql .= " OR ";
                else
                    $sql .= ")";
            }

            if ($filter_data['rating']){
                $filter_data['rating'] = array_values($filter_data['rating']);

                $sql .= "FLOOR(ar.avg_rating) IN (" . (double)$filter_data['rating'][0] . "";

                foreach ($filter_data['rating'] as $key => $rating){
                    if ($key != 0)
                        $sql .= "," . (double)$rating;
                }

                $sql .= "))";
            }
        }

        if (isset($filter_data['no_zero_fav_count'])){
            $sql .= " AND total != 0";
        }

        if (isset($filter_data['joined_date'])){
            $sql .= " AND r.join_date > '" .  $filter_data['joined_date'] . "'";
        }

        if (isset($filter_data['sort_by'])){
            $sql .= " ORDER BY " . $filter_data['sort_by'];
        } else {
            $sql .= " ORDER BY rest_name ASC";
        }

        if (isset($filter_data['limit'])){
            $sql .= " LIMIT " . $filter_data['limit'];
        }

        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);
        return $results;
    }

    function searchFoodItems($connection, $filter_data = array()){
        $sql = "SELECT fi.rest_id, fi.item_id, category_name, fi.item_name, fi.image, fi.price FROM `food_item` fi LEFT JOIN category c ON (fi.category_id = c.category_id) LEFT JOIN restaurant r ON (fi.rest_id = r.rest_id) WHERE 1";

        if (isset($filter_data['keyword'])){
            $sql .= " AND (item_name LIKE '%" . $filter_data['keyword'] . "%' OR category_name LIKE '%" . $filter_data['keyword'] . "%')";
        }

        if (isset($filter_data['city'])){
            $sql .= " AND r.city = '" . $filter_data['city'] . "'";
        }

        $sql .= " ORDER BY item_name ASC";

        if (isset($filter_data['limit'])){
            $sql .= " LIMIT " . $filter_data['limit'];
        }

        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    function getRestaurantLoginActivity($connection, $rest_id, $filter_data = array()){
        $sql = "SELECT la.* FROM `login_activity` la LEFT JOIN `user` u ON (la.user_id = u.user_id) WHERE `rest_id` = '" . $rest_id . "'";

        if (isset($filter_data['online'])){
            $sql .= " AND logout_time IS NULL";
        }

        if (isset($filter_data['last_login_activity'])){
            $sql .= " ORDER BY login_time DESC LIMIT 1";
        }

        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    function getFavouriteRestaurants($connection, $cus_id, $filter_data = array()){
        $sql = "SELECT *, TODAY_OPENING_CLOSING_TIME(r.daily_opening_hours, 'open') AS opening_time, TODAY_OPENING_CLOSING_TIME(r.daily_opening_hours, 'close') AS closing_time FROM `favourite_restaurant` fr LEFT JOIN (SELECT AVG(rating) AS avg_rating, o.rest_id FROM rating_review rr LEFT JOIN `order` o ON (rr.order_id = o.order_id) GROUP BY o.rest_id) ar ON (ar.rest_id = fr.rest_id) LEFT JOIN restaurant r ON (fr.rest_id = r.rest_id) WHERE `cus_id` = '" . $cus_id . "'";

        if (isset($filter_data['rest_id'])){
            $sql .= " AND fr.rest_id = '" . $filter_data['rest_id'] . "'";
        }

        $sql .= " ORDER BY added_date DESC";

        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    if (isset($_POST['search_input'])){
        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $filter_data = ['keyword' => $_POST['search_input'], 'limit' => 3];
        if (isset($_POST['location_select']))
            $filter_data['city'] = $_POST['location_select'];

        $data['restaurants'] = searchRestaurants($connection, $filter_data);

        if ($data['restaurants']){
            foreach ($data['restaurants'] as $key => $restaurant_info){
                $data['restaurants'][$key] = array(
                    'id'            => $restaurant_info['rest_id'],
                    'image'         => $restaurant_info['rest_profile'],
                    'rest_name'     => $restaurant_info['rest_name'],
                    'tags'          => json_decode($restaurant_info['tags'], true),
                    'dining_style'  => $restaurant_info['dining_style']
                );
            }
        } else {
            unset($data['restaurants']);
        }

        $data['foods'] = searchFoodItems($connection, $filter_data);
        
        if ($data['foods']){
            foreach ($data['foods'] as $key => $food_info){
                $data['foods'][$key] = array(
                    'rest_id'       => $food_info['rest_id'],
                    'id'            => $food_info['item_id'],
                    'image'         => $food_info['image'],
                    'item_name'     => $food_info['item_name'],
                    'category_name' => $food_info['category_name']
                );
            }
        } else {
            unset($data['foods']);
        }
        echo json_encode($data);
        exit;
    }

    if (isset($_POST['save_favourite_rest'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $sql = "INSERT INTO favourite_restaurant(cus_id, rest_id) VALUES('" . $_SESSION['login_cus_id'] . "', '" . $_POST['rest_id'] . "')";
        $connection->query($sql);

        exit;
    }

    if (isset($_POST['remove_favourite_rest'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $sql = "DELETE FROM favourite_restaurant WHERE cus_id = '" . $_SESSION['login_cus_id'] . "' AND rest_id = '" . $_POST['rest_id'] . "'";
        $connection->query($sql);

        exit;
    }

    if (isset($_POST['update_sharing_count'])){
        include_once '../../../db_connect.php';
        include_once '../../../session.php';
        include_once '../../../admin/php/helpers/restaurant.php';

        updateLastActivity();

        date_default_timezone_set("Asia/Kuala_Lumpur");

        $sharing_info = getSharingCount($connection, $_POST['rest_id'], date('m'), date('Y'));

        if (empty($sharing_info)){
            $sql = "INSERT INTO sharing(rest_id, `count`, `month`, `year`) VALUES ('" . $_POST['rest_id'] . "', 1, " . (int)date('m') . ", " . (int)date('Y') . ")";
        } else {
            $sql = "UPDATE sharing SET `count` = " . ($sharing_info['count'] + 1) . " WHERE rest_id = '" . $sharing_info['rest_id'] . "' AND month = " . (int)$sharing_info['month'] . " AND `year` = " . (int)$sharing_info['year'];
        }

        $connection->query($sql);
        exit;
    }

    if (isset($_POST['update_visitor_count'])){
        session_start();

        if (!isset($_SESSION['visit_pages']) || (isset($_SESSION['visit_pages']) && !in_array($_POST['rest_id'], $_SESSION['visit_pages']))){
            include_once '../../../db_connect.php';
            include_once '../../../admin/php/helpers/restaurant.php';

            date_default_timezone_set("Asia/Kuala_Lumpur");

            $visitor_info = getVisitorCount($connection, $_POST['rest_id'], date('m'), date('Y'));

            if (empty($visitor_info)){
                $sql = "INSERT INTO visitor(rest_id, `count`, `month`, `year`) VALUES ('" . $_POST['rest_id'] . "', 1, " . (int)date('m') . ", " . (int)date('Y') . ")";
            } else {
                $sql = "UPDATE visitor SET `count` = " . ($visitor_info['count'] + 1) . " WHERE rest_id = '" . $visitor_info['rest_id'] . "' AND month = " . (int)$visitor_info['month'] . " AND `year` = " . (int)$visitor_info['year'];
            }

            $connection->query($sql);
            $_SESSION['visit_pages'][] = $_POST['rest_id'];
        }
        exit;
    }

    if (isset($_POST['check_redemption_chance'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../admin/php/helpers/promotion.php';

        $redemption_record = getRedemptionRecord($connection, (int)$_POST['promotion_id'], $_SESSION['login_cus_id']);

        if ($redemption_record){
            $redemption_left = 0;
            foreach ($redemption_record as $redemption_left_info){
                $redemption_left = $redemption_left_info['redemption_left'];
            }
            echo (string)$redemption_left;
        }

        exit;
    }

?>