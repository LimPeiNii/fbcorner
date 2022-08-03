<?php

    function getRatingsReviews($connection, $rest_id, $filter_data = array()){
        $sql = "SELECT rr.*, c.profile_pic, o.cus_id, o.modified_date AS dine_date, o.reservation_id, CONCAT(c.firstname, ' ', c.lastname) AS cus_name FROM rating_review rr LEFT JOIN `order` o ON (rr.order_id = o.order_id) LEFT JOIN customer c ON (o.cus_id = c.cus_id) WHERE o.rest_id = '" . $rest_id . "'";

        if (isset($filter_data['read'])){
            $sql .= " AND rr.read_date IS NOT NULL";
        }

        if (isset($filter_data['unread'])){
            $sql .= " AND rr.read_date IS NULL";
        }

        if (isset($filter_data['order_by']) && isset($filter_data['asc_desc'])){
            $sql .= " ORDER BY " . $filter_data['order_by'] . " " . $filter_data['asc_desc'];
        }

        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    function getRatingReviewByOrderID($connection, $order_id){
        $sql = "SELECT * FROM `rating_review` WHERE `order_id` = '" . $order_id . "'";

        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        return $results;
    }

    function getRatingReviewSetting($connection, $rest_id){
        $sql = "SELECT * FROM `settings` WHERE `rest_id` = '" . $rest_id . "' AND `key` = 'rating_review'";
        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    //mark reviews as unread or read
    if (isset($_POST['mark_reviews_read']) || isset($_POST['mark_reviews_unread'])){
        
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        date_default_timezone_set("Asia/Kuala_Lumpur");

        $selected_groups = $_POST['selected_group'];
        $counter = 0;
        foreach ($selected_groups as $selected_group){
            if (isset($_POST['mark_reviews_unread']))
                $sql = "UPDATE rating_review SET read_date = null WHERE order_id = '" . $selected_group . "'";
            else
                $sql = "UPDATE rating_review SET read_date = '" . date('Y-m-d H:i:s') . "' WHERE order_id = '" . $selected_group . "'";

            $connection->query($sql);
            $counter++;
        }
        
        if (isset($_POST['mark_reviews_unread']))
            $_SESSION['success'] = "You have successfully mark " . (string)$counter . " review(s) as unread.";
        else
            $_SESSION['success'] = "You have successfully mark " . (string)$counter . " review(s) as read.";

        exit;
    }

    if (isset($_POST['get_rating_review_setting'])){
        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $rating_review_setting = getRatingReviewSetting($connection, $_POST['rest_id']);

        if ($rating_review_setting){
            echo $rating_review_setting[0]['value'];
        } else {
            echo json_encode([]);
        }

        exit;
    }

    if (isset($_POST['save_rating_review_setting'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $result = getRatingReviewSetting($connection, $_SESSION['login_rest_id']);

        $settings_data = array(
            'reply_options' => $_POST['reply_options'],
            'auto_reply_index' => $_POST['auto_reply_index']
        );

        $settings_data = $connection->real_escape_string(json_encode($settings_data));

        if (count($result) > 0){
            if (empty($_POST['reply_options'])){
                //delete settings data
                $sql .= "DELETE FROM settings WHERE rest_id = '" . $_SESSION['login_rest_id'] . "' AND `key` = 'rating_review'";

                $_SESSION['success'] = 'You have successfully updated the settings.';
            } else {
                //save settings data
                $sql = "UPDATE `settings` SET `value` = '" . $settings_data . "' WHERE rest_id = '" . $_SESSION['login_rest_id'] . "' AND `key` = 'rating_review'";

                $_SESSION['success'] = 'You have successfully updated the settings.';
            }
        } else {
            //save settings data
            if (!empty($_POST['reply_options'])){
                $sql = "INSERT INTO `settings`(rest_id, `key`, `value`) VALUES ('" . $_SESSION['login_rest_id'] . "','rating_review','" . $settings_data . "')";

                $_SESSION['success'] = 'You have successfully updated the settings.';
            }
        }

        $connection->query($sql);
        

        exit;
    }

    if (isset($_POST['check_auto_reply_exist'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $result = getRatingReviewSetting($connection, $_SESSION['login_rest_id']);

        $auto_reply_msg = '';

        if (count($result) > 0){
            $settings_data = json_decode($result[0]['value'], true);

            if ($settings_data['auto_reply_index'] != '-1'){
                $auto_reply_msg = $settings_data['reply_options'][(int)$settings_data['auto_reply_index']];
            }
        }

        echo $auto_reply_msg;
        exit;
    }

    if (isset($_POST['auto_reply_reviews'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        date_default_timezone_set("Asia/Kuala_Lumpur");

        $selected_groups = $_POST['selected_group'];
        $counter = 0;
        foreach ($selected_groups as $selected_group){
            $sql = "UPDATE rating_review SET reply = '" . $_POST['auto_reply_message'] . "', reply_modified_date = '" . date('Y-m-d H:i:s') . "', read_date = '" . date('Y-m-d H:i:s') . "' WHERE order_id = '" . $selected_group . "'";

            $connection->query($sql);
            $counter++;
        }
        
        $_SESSION['success'] = "You have successfully auto reply " . (string)$counter . " review(s).";
        exit;
    }

    if (isset($_POST['get_review_data'])){
        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $rating_review_result = getRatingReviewByOrderID($connection, $_POST['order_id']);

        echo json_encode($rating_review_result);
        exit;
    }

    if (isset($_POST['submit_reply_review'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        date_default_timezone_set("Asia/Kuala_Lumpur");

        $sql = "UPDATE rating_review SET reply = '" . $_POST['reply_review'] . "', reply_modified_date = '" . date('Y-m-d H:i:s') . "', read_date = '" . date('Y-m-d H:i:s') . "' WHERE order_id = '" . $_POST['order_id'] . "'";

        $connection->query($sql);

        $_SESSION['success'] = "You have successfully reply to the customer's review";
        header('Location: ../pages/reviews/reviews.php');
        exit;
    }

    if (isset($_POST['mark_rating_review_read'])){
        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        date_default_timezone_set("Asia/Kuala_Lumpur");

        $rating_review_info = getRatingReviewByOrderID($connection, $_POST['order_id']);

        if (empty($rating_review_info['read_date'])){
            $sql = "UPDATE rating_review SET read_date = '" . date('Y-m-d H:i:s') . "' WHERE order_id = '" . $_POST['order_id'] . "'";

            $connection->query($sql);
        }
        exit;
    }

?>