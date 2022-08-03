<?php

    function getVouchers($connection, $cus_id, $filter_data = array()){
        $sql = "SELECT *, o.voucher_id AS in_use_voucher_id, v.voucher_id AS the_voucher_id FROM `voucher` v LEFT JOIN `voucher_type` vt ON (v.voucher_type_id = vt.voucher_type_id) LEFT JOIN `order` o ON (o.voucher_id = v.voucher_id) WHERE v.cus_id = '" . $cus_id . "'";

        if (isset($filter_data['applied_order_id'])){
            $sql .= " AND applied_order_id = '" . $filter_data['applied_order_id'] . "'";
        }

        if (isset($filter_data['invalid'])){
            $sql .= " AND DATE(expired_date) < DATE(NOW())";
        }

        if (isset($filter_data['valid'])){
            $sql .= " AND DATE(expired_date) >= DATE(NOW())";
        }

        if (isset($filter_data['invalid_or_used'])){
            $sql .= " AND (applied_order_id != '0' OR DATE(expired_date) < DATE(NOW()))";
        }

        if (isset($filter_data['not_in_use'])){
            $sql .= " AND o.voucher_id IS NULL";
        }

        if (isset($filter_data['not_other_in_use'])){
            $sql .= " AND (o.voucher_id IS NULL OR o.voucher_id = '" . $filter_data['not_other_in_use']['this_voucher_id'] . "')";
        }

        $sql .= " ORDER BY expired_date DESC";

        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    function getVoucher($connection, $voucher_id){
        $sql = "SELECT * FROM `voucher` v LEFT JOIN `voucher_type` vt ON (v.voucher_type_id = vt.voucher_type_id) WHERE v.voucher_id = '" . $voucher_id . "'";

        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        return $results;
    }

    function getVoucherTypes($connection){
        $sql = "SELECT * FROM `voucher_type`";

        $sql .= " ORDER BY points ASC";

        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    function getVoucherType($connection, $voucher_type_id){
        $sql = "SELECT * FROM `voucher_type` WHERE voucher_type_id = '" . $voucher_type_id . "'";

        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        return $results;
    }

    if (isset($_POST['get_voucher_types'])){
        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $voucher_type_infos = getVoucherTypes($connection);

        $voucher_types = [];
        foreach ($voucher_type_infos as $voucher_type_info) {
            $voucher_types[] = array(
                'id' => $voucher_type_info['voucher_type_id'],
                'Name' => $voucher_type_info['name'],
                'Value' => 'RM ' . number_format($voucher_type_info['equal_price'], 2, '.', ''),
                'Points Required' => number_format($voucher_type_info['points'], 0, '.', ','),
                'Validity Period' => '1 year'
            );
        }

        echo json_encode($voucher_types);
        exit;
    }

    if (isset($_POST['redeem_voucher'])){

        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $json = [];

        //check if points is enough
        $voucher_type_info = getVoucherType($connection, $_POST['voucher_type_id']);

        if ($_POST['points'] < $voucher_type_info['points']){
            $json['error'] = 'You do not have enough points to redeem this voucher!';
        } else {
            date_default_timezone_set("Asia/Kuala_Lumpur");

            $sql = "INSERT INTO `voucher`(voucher_type_id, cus_id, applied_order_id, redeemed_date, expired_date) VALUES ('" . $_POST['voucher_type_id'] . "','" . $_SESSION['login_cus_id'] . "','0','" .  date('Y-m-d') . "','" .  date('Y-m-d', strtotime("+1 year", strtotime(date('Y-m-d')))) . "')";

            $connection->query($sql);

            $sql2 = "UPDATE cus_points SET points = '" . ($_POST['points'] - $voucher_type_info['points']) . "' WHERE `cus_id` = '" . $_SESSION['login_cus_id'] . "'";

            $connection->query($sql2);

            $json['success'] = 'You have successfully redeemed the voucher!';
            $json['points_left'] = $_POST['points'] - $voucher_type_info['points'];
        }

        echo json_encode($json);
        exit;
    }


?>