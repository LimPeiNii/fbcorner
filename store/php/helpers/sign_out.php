<?php

    session_start();

    include_once '../../../db_connect.php';

    date_default_timezone_set("Asia/Kuala_Lumpur");

    $sql = "UPDATE login_activity SET `logout_time` = '" . date('Y-m-d H:i:s') . "' WHERE login_activity_id = '" . $_SESSION['login_activity_id'] . "'";
    $connection->query($sql);

    session_unset();
    session_destroy();

    session_start();
    
    $_SESSION['success'] = 'You are signed out of F&B Corner.';

    exit;

?>