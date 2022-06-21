<?php

    function updateLastActivity(){
        if (session_status() === PHP_SESSION_NONE)
            session_start();

        date_default_timezone_set("Asia/Kuala_Lumpur");

        $_SESSION['LAST_ACTIVITY'] = time();
    }

    if (isset($_POST['check_session_expired'])){
        session_start();

        date_default_timezone_set("Asia/Kuala_Lumpur");

        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > (20 * 60)) && (isset($_SESSION['login_rest_id']) || isset($_SESSION['login_cus_id']))) {
            include_once 'db_connect.php';

            $sql = "UPDATE login_activity SET `logout_time` = '" . date('Y-m-d H:i:s') . "' WHERE login_activity_id = '" . $_SESSION['login_activity_id'] . "'";
            $connection->query($sql);

            session_unset();
            session_destroy();

            echo 'Your session is expired, please log in again.';
        } else {
            echo '';
        }
    }

?>