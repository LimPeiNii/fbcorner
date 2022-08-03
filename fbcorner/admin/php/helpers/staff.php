<?php

    function getStaffs($connection, $rest_id){
        $sql = "SELECT * FROM `staff` WHERE `rest_id` = '" . $rest_id . "' ORDER BY entry_date DESC";
        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    function getStaff($connection, $staff_id){
        $sql = "SELECT * FROM `staff` WHERE `staff_id` = '" . $staff_id . "'";
        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        return $results;
    }

    if (isset($_POST['get_staff_data'])){
        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $result = getStaff($connection, $_POST['id']);

        $staff['Name'] = $result['firstname'] . " " . $result['lastname'];
        $staff['Email'] = $result['email'];
        $staff['Contact Number'] = $result['contact_num'];
        $staff['Address'] = $result['address'];
        $staff['Date Of Birth'] = date('d/m/Y', strtotime($result['date_of_birth']));
        $staff['Entry Date'] = date('d/m/Y h:i a', strtotime($result['entry_date']));
        $staff['image'] = $result['image'];
        
        echo json_encode($staff);
        exit;
    }

    //delete staff
    if (isset($_POST['selected_group'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $selected_staffs = $_POST['selected_group'];

        $counter = 0;
        foreach ($selected_staffs as $selected_staff){
            //remove image
            $images = glob('../../uploads/staff_img/' . $_SESSION['login_rest_id']. '-' . $selected_staff . '*');
            foreach ($images as $image) {
                unlink($image);
            }

            $sql = "DELETE FROM `staff` WHERE staff_id = '" . $selected_staff . "'";
            $connection->query($sql);

            $sql2 = "DELETE FROM `user` WHERE staff_id = '" . $selected_staff . "'";
            $connection->query($sql2);

            $counter++;
        }
        
        $_SESSION['success'] = "You have successfully deleted " . (string)$counter . " staff(s)."; 
        exit;
    }

    if (isset($_POST['submit_check_staff'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $firstname    = trim($_POST['first-name']);
        $lastname     = trim($_POST['last-name']);
        $email        = trim($_POST['email']);
        $contact_num  = trim($_POST['contact-num']);
        $address      = trim($_POST['address']);
        $dob          = $_POST['dob'];

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
            if (isset($_POST['staff_id']))
                $sql       = "SELECT email FROM staff WHERE email = '" . $email . "' AND staff_id != " . $_POST['staff_id'] . " AND rest_id = '" . $_SESSION['login_rest_id'] . "'";
            else
                $sql       = "SELECT email FROM staff WHERE email = '" . $email . "' AND rest_id = '" . $_SESSION['login_rest_id'] . "'";

            $statement = $connection->query($sql);

            if ($statement->num_rows > 0)
                $json['error']['email'] = "The email ($email) has been used by another staff!";
        }

        // contact number
        $tel_pattern = "/^(01[0-9])-([0-9]{7}|[0-9]{8})$/";
        $tel_pattern_2 = "/^(01[0-9]{8}|01[0-9]{9})$/";
        if (empty($contact_num))
            $json['error']['contact-num'] = "Please enter the contact number!";
        elseif (!preg_match($tel_pattern, $contact_num) && !preg_match($tel_pattern_2, $contact_num))
            $json['error']['contact-num'] = "Contact number does not appear to be valid!";
        else {
            if (isset($_POST['staff_id']))
                $sql       = "SELECT contact_num FROM staff WHERE contact_num = '" . $contact_num . "' AND staff_id != " . $_POST['staff_id'] . " AND rest_id = '" . $_SESSION['login_rest_id'] . "'";
            else
                $sql       = "SELECT contact_num FROM staff WHERE contact_num = '" . $contact_num . "' AND rest_id = '" . $_SESSION['login_rest_id'] . "'";

            $statement = $connection->query($sql);

            if ($statement->num_rows > 0)
                $json['error']['contact-num'] = "The contact number ($contact_num) has been used by another staff!";
        }

        // address
        if (empty($address))
            $json['error']['address'] = "Please enter an address!";
        elseif (strlen($address) > 400)
            $json['error']['address'] = "Address must be between 1 and 400 characters!";

        // date of birth
        if (empty($dob))
            $json['error']['dob'] = "Please enter the date of birth!";

        echo json_encode($json);
        exit;
    }

    if (isset($_POST['submit_form_staff'])){

        session_start();
        
        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $firstname     = trim($_POST['first-name']);
        $lastname      = trim($_POST['last-name']);
        $email         = trim($_POST['email']);
        $contact_num   = trim($_POST['contact-num']);
        $address       = trim($_POST['address']);
        $dob           = $_POST['dob'];
        $reset_image   = $_POST['reset_staff_image'];

        //change contact number format
        if ($contact_num[3] != '-'){
            $contact_num = substr($contact_num, 0, 3) . '-' . substr($contact_num, 3);
        }

        //staff id
        if (isset($_GET['staff_id']))
            $staff_id = $_GET['staff_id'];
        else{
            $sql = "SELECT `auto_increment` FROM INFORMATION_SCHEMA.TABLES WHERE table_name = 'staff'";
            $statement = $connection->query($sql);
            $staff_id = ($statement->fetch_assoc())['auto_increment'];
        }
        
        //staff image
        //upload staff image
        if (isset($_FILES['image']['size']) && $_FILES['image']['size'] > 0){
            $name      = $_FILES['image']['name'];
            $tmp_name  = $_FILES['image']['tmp_name'];
            $img_error = $_FILES['image']['error'];
            
            if ($img_error === 0){
                //get image extension
                $img_ext = pathinfo($name, PATHINFO_EXTENSION);
                
                //convert image extension to lower case
                $img_ext_lower = strtolower($img_ext);

                //allowed image extensions
                $allowed_ext = array("jpg", "jpeg", "png");

                if (in_array($img_ext_lower, $allowed_ext)) {
                    $files = glob('../../uploads/staff_img/' . $_SESSION['login_rest_id']. '-' . $staff_id . '.*');
                    foreach ($files as $file) {
                        unlink($file);
                    }

                    //rename image name
                    $new_name = $_SESSION['login_rest_id']. '-' . $staff_id . '.' . $img_ext_lower;

                    //image upload path 
                    $upload_path = '../../uploads/staff_img/' . $new_name;

                    //move uploaded img to folder
                    move_uploaded_file($tmp_name, $upload_path);
                }
            } else {
                $_SESSION['error'] = "Unknown error occured! Please reload and try again.";
                header("Location: ../pages/staff/staff.php");
                exit;
            }
        } elseif ($reset_image == 'remove'){
            $new_name = 'default_staff_img.png';
            $files = glob('../../uploads/staff_img/' . $_SESSION['login_rest_id']. '-' . $staff_id . '.*');
            foreach ($files as $file) {
                unlink($file);
            }
        } else {
            if (isset($_GET['staff_id']))
                $new_name = getStaff($connection, $_GET['staff_id'])['image'];
            else
                $new_name = 'default_staff_img.png';
        }
        
        //create or edit staff
        if (isset($_GET['staff_id'])){
            $sql = "UPDATE staff SET `firstname` = '" . $firstname . "', lastname = '" . $lastname . "', email = '" . $email . "', contact_num = '" . $contact_num . "', address = '" . $address . "', date_of_birth = '" . $dob . "', image = '" . $new_name . "' WHERE staff_id = '" . $_GET['staff_id'] . "'";
            $success_msg = 'You have successfully edited the staff.';
        } else {
            $sql = "INSERT INTO staff(rest_id, firstname, lastname, email, contact_num, `address`, date_of_birth, `image`) VALUES ('" . $_SESSION['login_rest_id'] . "','" . $firstname . "','" .  $lastname . "','" .  $email . "','" .  $contact_num . "','" .  $address . "','" .  $dob . "','" . $new_name . "')";
            $success_msg = 'You have successfully added the staff.';
        }
        
        $connection->query($sql);

        $_SESSION['success'] = $success_msg;
        header("Location: ../pages/staff/staff.php");
        exit;
    }

?>