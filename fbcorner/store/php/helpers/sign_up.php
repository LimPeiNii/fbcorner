<?php

    session_start();

    include_once '../../../db_connect.php';

    $firstname   = trim($_POST['firstname']);
    $lastname    = trim($_POST['lastname']);
    $email       = trim($_POST['email']);
    $tel         = trim($_POST['tel']);
    $password    = $_POST['password'];
    $c_password  = $_POST['c_password'];
    
    if (isset($_POST['submit_check_sign_up_store'])){
        $profile_pic = $_POST['profile_pic'];

        $error = [];

        // error checking
        // firstname
        if (empty($firstname))
            $error['error']['firstname'] = "Please enter you first name!";
        elseif (strlen($firstname) > 32)
            $error['error']['firstname'] = "First name must be between 1 and 32 characters!";

        // lastname
        if (strlen($lastname) > 32)
            $error['error']['lastname'] = "Last name must be between 1 and 32 characters!";

        // email
        if (empty($email))
            $error['error']['email'] = "Please enter an email!";
        elseif (strlen($email) > 96 || !filter_var($email, FILTER_VALIDATE_EMAIL))
            $error['error']['email'] = "Email does not appear to be valid!";
        else {
            $sql       = "SELECT email FROM customer WHERE email='" . $email . "'";
            $statement = $connection->query($sql);

            if ($statement->num_rows > 0)
                $error['error']['email'] = "The email ($email) is already registered!";
        }

        // contact number
        $tel_pattern = "/^(01[0-9])-([0-9]{7}|[0-9]{8})$/";
        $tel_pattern_2 = "/^(01[0-9]{8}|01[0-9]{9})$/";
        if (empty($tel))
            $error['error']['tel'] = "Please enter the contact number!";
        elseif (!preg_match($tel_pattern, $tel) && !preg_match($tel_pattern_2, $tel))
            $error['error']['tel'] = "Contact number does not appear to be valid!";
        else{
            if ($tel[3] != '-'){
                $tel = substr($tel, 0, 3) . '-' . substr($tel, 3);
            }

            $sql       = "SELECT contact_num FROM customer WHERE contact_num='" . $tel . "'";
            $statement = $connection->query($sql);

            if ($statement->num_rows > 0)
                $error['error']['tel'] = "The contact number ($tel) is already registered!";
        }

        //password
        if (empty($password))
            $error['error']['password'] = "Please enter a password!";
        elseif (strlen($password) < 4 || strlen($password) > 20)
            $error['error']['password'] = "Password must be between 4 and 20 characters!";

        //confirm password
        if (empty($c_password))
            $error['error']['c_password'] = "Please enter the password!";
        elseif ($c_password != $password)
            $error['error']['c_password'] = "Passwords entered are not same!";
        
        // profile picture (extension type)
        if (!empty($profile_pic)){
            $allowed_ext = array("jpg", "jpeg", "png");
            if (!in_array(strtolower(pathinfo($profile_pic, PATHINFO_EXTENSION)), $allowed_ext))
                $error['error']['profile_pic'] = 'You can only upload files with type "jpg", "jpeg", "png"!';
        }

        // after error checking
        echo json_encode($error);
        exit;
    }

    if (isset($_POST['submit_form_sign_up_store'])){

        //change contact_num format
        if ($tel[3] != '-'){
            $tel = substr($tel, 0, 3) . '-' . substr($tel, 3);
        }

        //create new id for new customer
        $sql = "SELECT cus_id FROM customer ORDER BY cus_id DESC LIMIT 1";
        $statement = $connection->query($sql);
        if ($statement->num_rows > 0){
            $last_id = ($statement->fetch_assoc())['cus_id'];
            $new_id = 'C' . (string)((int)substr($last_id, 1) + 1);
        } else {
            $new_id = 'C1';
        }

        //upload profile picture
        if (isset($_FILES['profile_pic']['size']) && $_FILES['profile_pic']['size'] > 0){
            $name      = $_FILES['profile_pic']['name'];
            $tmp_name  = $_FILES['profile_pic']['tmp_name'];
            $img_error = $_FILES['profile_pic']['error'];

            if ($img_error === 0){
                //get image extension
                $img_ext = pathinfo($name, PATHINFO_EXTENSION);
                
                //convert image extension to lower case
                $img_ext_lower = strtolower($img_ext);

                //allowed image extensions
                $allowed_ext = array("jpg", "jpeg", "png");

                if (in_array($img_ext_lower, $allowed_ext)) {
                    //rename image name
                    $new_name = $new_id . '.' . $img_ext_lower;

                    //image upload path 
                    $upload_path = '../../uploads/profile_pic/' . $new_name;

                    //move uploaded img to folder
                    move_uploaded_file($tmp_name, $upload_path);
                }
            } else {
                $_SESSION['error'] = "Unknown error occured! Please try again.";
                header("Location: ../pages/homepage/index.php");
                exit;
            }
        }

        //password
        $hashed_pwd = password_hash($password, PASSWORD_DEFAULT);

        //if user upload profile picture
        if (isset($new_name)){
            $sql2 = "INSERT INTO customer(cus_id, firstname, lastname, email, contact_num, `password`, profile_pic) VALUES ('" . $new_id . "','" .  $firstname . "','" . $lastname . "','" . $email . "','" . $tel . "','" . $hashed_pwd . "','" . $new_name . "')";
        }
        else {       
            $sql2 = "INSERT INTO customer(cus_id, firstname, lastname, email, contact_num, `password`) VALUES ('" . $new_id . "','" .  $firstname . "','" . $lastname . "','" . $email . "','" . $tel . "','" . $hashed_pwd . "')";
        }

        //add cus_points record
        date_default_timezone_set("Asia/Kuala_Lumpur");
        $point_expired_date = date('Y-m-d', strtotime("+3 months", strtotime(date('Y-m-d'))));
        $sql3 = "INSERT INTO cus_points(cus_id, expired_date) VALUES ('" . $new_id . "', '" . $point_expired_date . "')";
        $connection->query($sql3);

        $result = $connection->query($sql2);
        $_SESSION['success'] = 'Thanks for your registration. You have successfully created an account.';

        if (!isset($_SESSION['success'])){
            $_SESSION['error'] = 'There was an error! Please try again.';
        }

        header("Location: ../pages/homepage/index.php");
        exit;
    }
?>