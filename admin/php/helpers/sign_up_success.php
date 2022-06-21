<?php

    function generateRandomPassword() {
        $password = '';
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        for ($i=0; $i<6; $i++){
            $index = rand(0, (strlen($characters) - 1));
            $password .= $characters[$index];
        }
        
        return $password;
    }

    session_start();

    include_once '../../../db_connect.php';
    include_once '../../../email/send_email.php';

    $rest_name    = trim($_POST['rest-name']);
    $owner_name   = trim($_POST['owner-name']);
    $contact_num  = trim($_POST['tel']);
    $email        = trim($_POST['email']);
    $address      = trim($_POST['address']);

    //change contact_num format
    if ($contact_num[3] != '-'){
        $contact_num = substr($contact_num, 0, 3) . '-' . substr($contact_num, 3);
    }

    //create new id for new restaurant
    $sql = "SELECT rest_id FROM restaurant ORDER BY rest_id DESC LIMIT 1";
    $statement = $connection->query($sql);
    if ($statement->num_rows > 0){
        $last_id = ($statement->fetch_assoc())['rest_id'];
        $new_id = 'R' . (string)((int)substr($last_id, 1) + 1);
    } else {
        $new_id = 'R1';
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
                $new_name = $new_id. '.' . $img_ext_lower;

                //image upload path 
                $upload_path = '../../uploads/profile_pic/' . $new_name;

                //check if the image name is already used
                $counter = 0;
                while (file_exists($upload_path)) {
                    $counter++;
                    $new_name = $new_id . '(' . $counter . ').' . $img_ext_lower;
                    $upload_path = '../../uploads/profile_pic/' . $new_name;
                }

                //move uploaded img to folder
                move_uploaded_file($tmp_name, $upload_path);
            }
        } else {
            $_SESSION['error'] = "Unknown error occured! Please reload and try again.";
            header("Location: ../pages/signin_signup/sign_up.php");
            exit;
        }
    }

    //upload restaurant documents
    if (isset($_FILES['documents']['size']) && $_FILES['documents']['size'] > 0){
        $doc_names      = $_FILES['documents']['name'];
        $doc_tmp_names  = $_FILES['documents']['tmp_name'];
        $doc_errors     = $_FILES['documents']['error'];

        if ((array_count_values($doc_errors))[0] == count($doc_errors)){
            //get documents extension
            foreach ($doc_names as $name)
                $docs_ext[] = pathinfo($name, PATHINFO_EXTENSION);
            
            //allowed image extensions
            $allowed_ext = array("pdf", "doc", "docx", "xls", "xlsx", "txt");

            $document_names = array();

            foreach ($docs_ext as $key => $ext){
                //convert doc extension to lower case
                $docs_ext_lower = strtolower($ext);

                if (in_array($docs_ext_lower, $allowed_ext)) {
                    //rename doc name
                    $new_doc_name = $new_id. '.' . $docs_ext_lower;

                    //doc upload path 
                    $upload_path = '../../uploads/rest_docs/' . $new_doc_name;

                    //check if the doc name is already used
                    $counter = 0;
                    while (file_exists($upload_path)) {
                        $counter++;
                        $new_doc_name = $new_id . '(' . $counter . ').' . $docs_ext_lower;
                        $upload_path = '../../uploads/rest_docs/' . $new_doc_name;
                    }

                    //move uploaded documents to folder
                    move_uploaded_file($doc_tmp_names[$key], $upload_path);

                    $document_names[] = $new_doc_name;
                }
            }
        } else {
            $_SESSION['error'] = "Unknown error occured! Please reload and try again.";
            header("Location: ../pages/signin_signup/sign_up.php");
            exit;
        }
    }

    $documents = json_encode($document_names);

    //if user upload profile picture
    if (isset($new_name)){
        $sql2 = "INSERT INTO restaurant(rest_id, rest_name, owner_name, owner_contact_num, email, `address`, rest_profile, documents) VALUES ('" . $new_id . "','" .  $rest_name . "','" . $owner_name . "','" . $contact_num . "','" . $email . "','" . $address . "','" . $new_name . "','" . $documents . "')";
        $result = $connection->query($sql2);
    }
    else {       
        $sql2 = "INSERT INTO restaurant(rest_id, rest_name, owner_name, owner_contact_num, email, `address`, documents) VALUES ('" . $new_id . "','" .  $rest_name . "','" . $owner_name . "','" . $contact_num . "','" . $email . "','" . $address . "','" . $documents . "')";
        $result = $connection->query($sql2);
    }

    //create restaurant's administrator usergroup and user account
    if ($result){
        $sql3 = "SELECT permission_id FROM permission";
        $statement = $connection->query($sql3);
        $results = $statement->fetch_all(MYSQLI_ASSOC);
        $access_permission_ids = array_column($results, 'permission_id');

        $sql3 = "SELECT permission_id FROM permission WHERE `file_name` NOT IN ('customer/customer', 'dashboard/dashboard')";
        $statement = $connection->query($sql3);
        $results = $statement->fetch_all(MYSQLI_ASSOC);
        $modify_permission_ids = array_column($results, 'permission_id');

        $random_pwd = generateRandomPassword();

        $sql4 = "INSERT INTO user_group(user_group_name, rest_id, access_permission, modify_permission) VALUES ('Administrator','" . $new_id . "','" .  json_encode($access_permission_ids) . "','" . json_encode($modify_permission_ids) . "')";
        if ($connection->query($sql4)){
            $sql5 = "INSERT INTO user(rest_id, username, `password`, user_group_id) VALUES ('" . $new_id . "', 'admin','" .  password_hash($random_pwd, PASSWORD_DEFAULT) . "', (SELECT user_group_id FROM user_group WHERE rest_id = '" . $new_id . "' AND user_group_name = 'Administrator'))";
            if ($connection->query($sql5)){
                $_SESSION['success'] = 'Thanks for signing up, you have successfully joined F&B Corner. We have sent you an email, please check your inbox to get your account information.';
            }
        }
    }


    if (!isset($_SESSION['success'])){
        $_SESSION['error'] = 'There was an error! Please reload and try again.';
    } else {
        //send confirmation email to restaurant
        $email_body = '
        <html>
        <head>
        </head>
        <body>';

        $email_body .= 'Thanks for joining F&amp;B Corner. We have created a main account for the administrator of your restaurant. You can add more accounts for your staff under "User" section after logging in. To sign in as the administrator, please use the following information:<br>
        Restaurant Email: ' . $email . '<br>
        Username: admin<br>
        Password: ' . $random_pwd . '<br><br>
        It is advised to change the default password to protect your account.';

        $email_body .= 
        '</body>
        </html>';

        sendmail($email, "Welcome to F&B Corner", $email_body, '');
    }

    header("Location: ../pages/signin_signup/sign_up.php");

?>