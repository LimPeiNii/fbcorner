<?php

    function getUsers($connection, $rest_id){
        $sql = "SELECT * FROM user WHERE rest_id = '" . $rest_id . "'";
        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    function getUser($connection, $user_id){
        $sql = "SELECT * FROM user WHERE `user_id` = '" . $user_id . "'";
        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        return $results;
    }

    function checkUserPermission($connection, $user_id, $file_name, $type){
        $sql = "SELECT * FROM permission p WHERE `file_name` = '" . $file_name . "'";
        $statement = $connection->query($sql);
        $result = $statement->fetch_array(MYSQLI_ASSOC);

        $sql2 = "SELECT JSON_CONTAINS(ug." . $type . ", '\"" . $result['permission_id'] . "\"', '$') AS has_permission FROM user u LEFT JOIN user_group ug ON (u.user_group_id = ug.user_group_id) WHERE u.user_id = " . (int)$user_id;

        $statement = $connection->query($sql2);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        return $results;
    }

    //check form
    if (isset($_POST['submit_check_user'])){
        
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $username     = trim($_POST['user-name']);
        $user_group   = $_POST['user-group'];
        
        if (isset($_POST['staff']))
            $staff_id     = $_POST['staff'];
        
        $password     = trim($_POST['pwd']);
        $con_password = trim($_POST['c-pwd']);
        

        $json = [];

        //error checking
        //username
        if (empty($username))
            $json['error']['user-name'] = "Please enter a username!";
        elseif (strlen($username) > 32)
            $json['error']['user-name'] = "Username must be between 1 and 32 characters!";
        elseif (strlen($_POST['edit_user']) == 0){
            $all_users = getUsers($connection, $_SESSION['login_rest_id']);
            $all_usernames = array_column($all_users, 'username');
            if (in_array($username, $all_usernames)){
                $json['error']['user-name'] = "This username has already been used!";
            }
        }
        elseif (strlen($_POST['edit_user']) > 0){
            $all_users = getUsers($connection, $_SESSION['login_rest_id']);
            $all_usernames = array_column($all_users, 'username');
            unset($all_usernames[array_search($_POST['edit_user'], $all_usernames)]);
            if (in_array($username, $all_usernames)){
                $json['error']['user-name'] = "This username has already been used!";
            }
        }

        // staff id
        if (isset($staff_id) && empty($staff_id))
            $json['error']['staff-id'] = "Please select a staff!";
        elseif (isset($staff_id)) {
            if (strlen($_POST['user_id']) > 0)
                $sql = "SELECT staff_id FROM user WHERE staff_id = '" . $staff_id . "' AND user_id != " . (int)$_POST['user_id'];
            else
                $sql = "SELECT staff_id FROM user WHERE staff_id = '" . $staff_id . "'";

            $statement = $connection->query($sql);

            if ($statement->num_rows > 0)
                $json['error']['staff-id'] = "This staff already has an account!";
        }

        //user group
        if (empty($user_group))
            $json['error']['user-group'] = "Please select a user group!";

        //password
        if (strlen($_POST['edit_user']) == 0){
            if (empty($password))
                $json['error']['pwd'] = "Please enter a password!";
            elseif (strlen($password) < 4 || strlen($password) > 20)
                $json['error']['pwd'] = "Password must be between 4 and 20 characters!";
        } else {
            if ($password != '' && (strlen($password) < 4 || strlen($password) > 20))
                $json['error']['pwd'] = "Password must be between 4 and 20 characters!";
        }

        //confirm password
        if (strlen($_POST['edit_user']) == 0){
            if (empty($con_password))
                $json['error']['c-pwd'] = "Please enter the password!";
            elseif ($con_password != $password)
                $json['error']['c-pwd'] = "Passwords entered are not same!";
        } else {
            if ($password != '' && empty($con_password))
                $json['error']['c-pwd'] = "Please re-enter the password!";
            elseif ($password != '' && ($con_password != $password))
                $json['error']['c-pwd'] = "Passwords entered are not same!";
        }

        
        echo json_encode($json);
        
    }

    //submit form
    if (isset($_POST['submit_form_user'])){
        
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $username     = trim($_POST['user-name']);
        $user_group   = $_POST['user-group'];

        if (isset($_POST['staff-id']))
            $staff_id     = $_POST['staff-id'];

        $password     = $_POST['pwd'];
        $con_password = $_POST['c-pwd'];

        if ($password != '')
            $hashed_pwd = password_hash($password, PASSWORD_DEFAULT);

        //create or edit user
        if (isset($_GET['user_id'])){
            $sql = "UPDATE user SET `username` = '" . $username . "', user_group_id = '" . $user_group . "'";

            if (isset($hashed_pwd))
                $sql .= ", password = '" . $hashed_pwd . "'";
            
            if (isset($staff_id))
                $sql .= ", staff_id = " . $staff_id;

            $sql .= " WHERE user_id = '" . $_GET['user_id'] . "'";

            $success_msg = 'You have successfully edited "' . $username . '" user.';
        } else {
            $sql = "INSERT INTO user(rest_id, username, `password`, user_group_id, staff_id) VALUES ('" . $_SESSION['login_rest_id'] . "','" . $username . "','" .  $hashed_pwd . "','" . $user_group . "','" . $staff_id . "')";
            $success_msg = 'You have successfully added "' . $username . '" user.';
        }
            $connection->query($sql);

        $_SESSION['success'] = $success_msg;
        header("Location: ../pages/user/user.php");
        
    }

    //delete user
    if (isset($_POST['selected_group'])){
        
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $selected_groups = $_POST['selected_group'];
        $counter = 0;
        foreach ($selected_groups as $selected_group){
            $sql = "DELETE FROM user WHERE user_id = '" . $selected_group . "'";
            $connection->query($sql);
            $counter++;
        }
        
        $_SESSION['success'] = "You have successfully deleted " . (string)$counter . " user(s)."; 
        exit;
    }

?>