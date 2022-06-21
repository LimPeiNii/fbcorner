<?php

    function getPermissionFiles($connection) {
        $sql = "SELECT * FROM permission ORDER BY `file_name` ASC";
        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    function getUserGroups($connection, $rest_id, $filter_data = array()){
        $sql = "SELECT * FROM user_group WHERE rest_id = '" . $rest_id . "'";

        if (isset($filter_data['order_by']) && isset($filter_data['asc_desc'])){
            $sql .= " ORDER BY " . $filter_data['order_by'] . " " . $filter_data['asc_desc'];
        }

        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    function getUserGroup($connection, $user_group_id){
        $sql = "SELECT * FROM user_group WHERE user_group_id = '" . $user_group_id . "'";
        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        return $results;
    }

    if (isset($_POST['submit_form_user_group'])){
        
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $user_group_name = trim($_POST['user-group-name']);
        $access_files    = [];
        $modify_files    = [];

        if (isset($_POST['access']))
            $access_files = $_POST['access'];
        if (isset($_POST['modify']))
            $modify_files = $_POST['modify'];

        //create or edit user group
        if (isset($_GET['user_group_id'])){
            $sql = "UPDATE user_group SET user_group_name = '" . $user_group_name . "', access_permission = '" . json_encode($access_files) . "', modify_permission = '" . json_encode($modify_files) . "' WHERE user_group_id = '" . $_GET['user_group_id'] . "'";
            $success_msg = 'You have successfully edited "' . $user_group_name . '" user group.';
        } else {
            $sql = "INSERT INTO user_group(rest_id, user_group_name, access_permission, modify_permission) VALUES ('" . $_SESSION['login_rest_id'] . "','" . $user_group_name . "','" .  json_encode($access_files) . "','" . json_encode($modify_files) . "')";
            $success_msg = 'You have successfully added "' . $user_group_name . '" user group.';
        }
        $connection->query($sql);

        $_SESSION['success'] = $success_msg;
        header("Location: ../pages/user/user_group.php");

    }

    //delete user group
    if (isset($_POST['selected_group'])){
        
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $selected_groups = $_POST['selected_group'];
        $counter = 0;
        foreach ($selected_groups as $selected_group){
            $sql = "DELETE FROM user_group WHERE user_group_id = '" . $selected_group . "'";
            $connection->query($sql);

            $sql2 = "DELETE FROM user WHERE user_group_id = '" . $selected_group . "'";
            $connection->query($sql2);

            $counter++;
        }
        
        $_SESSION['success'] = "You have successfully deleted " . (string)$counter . " user group(s)."; 
        exit;
    }

?>