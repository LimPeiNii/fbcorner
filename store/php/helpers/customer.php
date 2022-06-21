<?php

    function getCustomer($connection, $cus_id){
        $sql = "SELECT * FROM customer WHERE `cus_id` = '" . $cus_id . "'";
        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        return $results;
    }

    function getCustomerRestaurantHistory($connection, $cus_id, $rest_id){
        $sql = "SELECT * FROM cus_rest_history WHERE `cus_id` = '" . $cus_id . "' AND rest_id = '" . $rest_id . "'";
        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);
        
        return $results;
    }

    function getCustomers($connection, $filter_data = array()){
        $sql = "SELECT * FROM customer";

        if (isset($filter_data['order_by']) && isset($filter_data['asc_desc'])){
            $sql .= " ORDER BY " . $filter_data['order_by'] . " " . $filter_data['asc_desc'];
        }

        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    function getCustomerPoints($connection, $cus_id){
        $sql = "SELECT * FROM cus_points WHERE `cus_id` = '" . $cus_id . "'";
        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        date_default_timezone_set("Asia/Kuala_Lumpur");

        //check if the points are expired
        if (date('Y-m-d', strtotime($results['expired_date'])) < date('Y-m-d')){
            $results['points'] = 0;
            $results['expired_date'] = date('Y-m-d', strtotime("+3 months", strtotime($results['expired_date'])));

            $sql2 = "UPDATE cus_points SET points = 0, expired_date = '" . $results['expired_date'] . "' WHERE `cus_id` = '" . $cus_id . "'";
            $connection->query($sql2);
        }
        
        return $results;
    }

    //admin site reservation list page (customer details modal)
    if (isset($_POST['id'])){
        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $result = getCustomer($connection, $_POST['id']);

        $customer['Name'] = $result['firstname'] . " " . $result['lastname'];
        $customer['Email'] = $result['email'];
        $customer['Contact Number'] = $result['contact_num'];
        $customer['pp'] = $result['profile_pic'];
        
        echo json_encode($customer);
        exit;
    }

    if (isset($_POST['submit_check_cus'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $firstname    = trim($_POST['first-name']);
        $lastname     = trim($_POST['last-name']);
        $email        = trim($_POST['email']);
        $contact_num  = trim($_POST['contact-num']);
        $password     = trim($_POST['pwd']);
        $con_password = trim($_POST['c-pwd']);      

        //change contact number format
        if (strlen($contact_num) > 3 && $contact_num[3] != '-'){
            $contact_num = substr($contact_num, 0, 3) . '-' . substr($contact_num, 3);
        }

        $json = [];

        // error checking
        // first name
        if (empty($firstname))
            $json['error']['first-name'] = "Please enter the first name!";
        elseif (strlen($firstname) > 32)
            $json['error']['first-name'] = "First name must be between 1 and 32 characters!";
        
        // last name
        if (strlen($lastname) > 32)
            $json['error']['last-name'] = "Last name must be between 1 and 32 characters!";
        
        // email
        if (empty($email))
            $json['error']['email'] = "Please enter an email!";
        elseif (strlen($email) > 96 || !filter_var($email, FILTER_VALIDATE_EMAIL))
            $json['error']['email'] = "Email does not appear to be valid!";
        else {
            $sql       = "SELECT email FROM customer WHERE email = '" . $email . "' AND cus_id != '" . $_SESSION['login_cus_id'] . "'";
            $statement = $connection->query($sql);

            if ($statement->num_rows > 0)
                $json['error']['email'] = "The email ($email) has been used by another account!";
        }

        // contact number
        $tel_pattern = "/^(01[0-9])-([0-9]{7}|[0-9]{8})$/";
        $tel_pattern_2 = "/^(01[0-9]{8}|01[0-9]{9})$/";
        if (empty($contact_num))
            $json['error']['contact-num'] = "Please enter the contact number!";
        elseif (!preg_match($tel_pattern, $contact_num) && !preg_match($tel_pattern_2, $contact_num))
            $json['error']['contact-num'] = "Contact number does not appear to be valid!";
        else {
            $sql       = "SELECT contact_num FROM customer WHERE contact_num = '" . $contact_num . "' AND cus_id != '" . $_SESSION['login_cus_id'] . "'";
            $statement = $connection->query($sql);

            if ($statement->num_rows > 0)
                $json['error']['contact-num'] = "The contact number ($contact_num) has been used by another account!";
        }

        //password
        if ($password != '' && (strlen($password) < 4 || strlen($password) > 20))
            $json['error']['pwd'] = "Password must be between 4 and 20 characters!";

        //confirm password
        if ($password != '' && empty($con_password))
            $json['error']['c-pwd'] = "Please re-enter the password!";
        elseif ($password != '' && ($con_password != $password))
            $json['error']['c-pwd'] = "Passwords entered are not same!";

        echo json_encode($json);
        exit;
    }

    //submit form
    if (isset($_POST['submit_form_cus'])){
        
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $firstname    = trim($_POST['first-name']);
        $lastname     = trim($_POST['last-name']);
        $email        = trim($_POST['email']);
        $contact_num  = trim($_POST['contact-num']);
        $password     = trim($_POST['pwd']);
        $con_password = trim($_POST['c-pwd']);
        $reset_image   = $_POST['reset_profile'];

        //change contact number format
        if ($contact_num[3] != '-'){
            $contact_num = substr($contact_num, 0, 3) . '-' . substr($contact_num, 3);
        }

        if ($password != '')
            $hashed_pwd = password_hash($password, PASSWORD_DEFAULT);

        //profile picture
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
                    $files = glob('../../uploads/profile_pic/' . $_SESSION['login_cus_id']. '.*');
                    foreach ($files as $file) {
                        unlink($file);
                    }

                    //rename image name
                    $new_name = $_SESSION['login_cus_id']. '.' . $img_ext_lower;

                    //image upload path 
                    $upload_path = '../../uploads/profile_pic/' . $new_name;

                    //move uploaded img to folder
                    move_uploaded_file($tmp_name, $upload_path);
                }
            } else {
                $_SESSION['error'] = "Unknown error occured! Please try again.";
                header("Location: ../pages/customer/profile_manage.php");
                exit;
            }
        } elseif ($reset_image == 'remove'){
            $new_name = 'default_pp.png';
            $files = glob('../../uploads/profile_pic/' . $_SESSION['login_cus_id'] . '.*');
            foreach ($files as $file) {
                unlink($file);
            }
        } else {
            $new_name = getCustomer($connection,  $_SESSION['login_cus_id'])['profile_pic'];
        }

        //edit customer
        $sql = "UPDATE customer SET `firstname` = '" . $firstname . "', lastname = '" . $lastname . "', email = '" . $email . "', contact_num = '" . $contact_num . "', profile_pic = '" . $new_name . "'";

        if (isset($hashed_pwd))
            $sql .= ", password = '" . $hashed_pwd . "'";

        $sql .= " WHERE cus_id = '" . $_SESSION['login_cus_id'] . "'";
        $connection->query($sql);
        $_SESSION['success'] = 'You have successfully edited your profile.';

        header("Location: ../pages/customer/profile_manage.php");
        exit;
    }

?>