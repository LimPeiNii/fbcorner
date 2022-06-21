<?php 

    session_start();

    include_once '../../../db_connect.php';
    include_once '../../../session.php';
    updateLastActivity();

    $email       = trim($_POST['email']);
    $password    = $_POST['password'];

    if (isset($_POST['submit_check_sign_in_store'])){

        $error = [];

        // error checking
        // email
        if (empty($email))
            $error['error']['email'] = "Please enter an email!";
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
            $error['error']['email'] = "Email does not appear to be valid!";

        if (empty($password))
            $error['error']['password'] = "Please enter the password!";

        // customer finding (try to login)
        if (empty($error)){
            $sql       = "SELECT * FROM customer WHERE email='" . $email . "'";
            $statement = $connection->query($sql);
            
            if ($statement->num_rows == 0)
                $error['error']['incorrect'] = "Incorrect email or password! Please try again.";
            else
                $result = $statement->fetch_array(MYSQLI_ASSOC);
        }

        if (empty($error)){
            if (password_verify($password, $result['password'])){
                $_SESSION['login_cus_id'] = $result['cus_id'];
                $_SESSION['info'] = 'Welcome, ' . $result['firstname'] . ' ' . $result['lastname'] . " !";
                
                if (isset($_SESSION['visit_pages']))
                    unset($_SESSION['visit_pages']);

                $sql2 = "INSERT INTO login_activity (`user_id`) VALUES ('" . $result['cus_id'] . "')";
                $connection->query($sql2);

                $_SESSION['login_activity_id'] = $connection->query('SELECT LAST_INSERT_ID() AS id')->fetch_array(MYSQLI_ASSOC)['id'];
            } else {
                $error['error']['incorrect'] = "Incorrect email or password! Please try again.";
            }
        }
        
        // after error checking
        echo json_encode($error);

    }

?>