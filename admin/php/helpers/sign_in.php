<?php

    session_start();

    include_once '../../../db_connect.php';
    include_once '../../../session.php';
    updateLastActivity();
    
    if (isset($_POST['submit_check_sign_in'])){
        $email    = trim($_POST['email']);
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        $error = [];

        // error checking (format/empty)
        // email
        if (empty($email))
            $error['error']['email'] = "Please enter an email!";
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
            $error['error']['email'] = "Email does not appear to be valid!";

        if (empty($username))
            $error['error']['username'] = "Please enter the username!";

        if (empty($password))
            $error['error']['password'] = "Please enter the password!";

        // user finding (try to login)
        if (empty($error)){
            $sql       = "SELECT rest_id FROM restaurant WHERE email='" . $email . "'";
            $statement = $connection->query($sql);
            
            if ($statement->num_rows == 0)
                $error['error']['incorrect'] = "Incorrect email, username or password! Please try again.";
            else
                $result = $statement->fetch_array(MYSQLI_ASSOC);
        }
        
        if (empty($error)){
            $sql       = "SELECT * FROM user WHERE username='" . $username . "' AND rest_id='" . $result['rest_id'] . "'";
            $statement = $connection->query($sql);

            if ($statement->num_rows == 0)
                $error['error']['incorrect'] = "Incorrect email, username or password! Please try again.";
            else
                $result = $statement->fetch_array(MYSQLI_ASSOC);
        }

        if (empty($error)){
            if (password_verify($password, $result['password'])){
                $_SESSION['login_rest_id'] = $result['rest_id'];
                $_SESSION['login_user_id'] = $result['user_id'];

                $sql2 = "INSERT INTO login_activity (`user_id`) VALUES ('" . $result['user_id'] . "')";
                $connection->query($sql2);

                $_SESSION['login_activity_id'] = $connection->query('SELECT LAST_INSERT_ID() AS id')->fetch_array(MYSQLI_ASSOC)['id'];
            } else {
                $error['error']['incorrect'] = "Incorrect email, username or password! Please try again.";
            }
        }

        // after error checking
        echo json_encode($error);

    }

?>