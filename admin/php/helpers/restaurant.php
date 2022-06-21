<?php 

    function getRestaurant($connection, $rest_id){
        $sql = "SELECT *, TODAY_OPENING_CLOSING_TIME(daily_opening_hours, 'open') AS opening_time, TODAY_OPENING_CLOSING_TIME(daily_opening_hours, 'close') AS closing_time FROM restaurant WHERE `rest_id` = '" . $rest_id . "'";

        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        return $results;
    }

    function getDiningStyles($connection){
        $sql = "SELECT * FROM dining_style";
        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    function getDiningStyle($connection, $dining_style_id){
        $sql = "SELECT * FROM dining_style WHERE dining_style_id = '" . $dining_style_id . "'";
        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        return $results;
    }

    function getSharingPostSettings($connection, $rest_id){
        $sql = "SELECT * FROM `settings` WHERE `rest_id` = '" . $rest_id . "' AND `key` = 'sharing_post'";
        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        return $results;
    }

    function getSharingCount($connection, $rest_id, $month, $year){
        $sql = "SELECT * FROM `sharing` WHERE `rest_id` = '" . $rest_id . "' AND `month` = " . (int)$month . " AND `year` = " . (int)$year;

        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        return $results;
    }

    function getVisitorCount($connection, $rest_id, $month, $year){
        $sql = "SELECT * FROM `visitor` WHERE `rest_id` = '" . $rest_id . "' AND `month` = " . (int)$month . " AND `year` = " . (int)$year;


        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        return $results;
    }

    if (isset($_POST['submit_check_restaurant'])){
        
        session_start();
        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $rest_name             = trim($_POST['rest_name']);
        $contact_num           = trim($_POST['contact_num']);
        $email                 = trim($_POST['email']);
        $address               = trim($_POST['address']);
        $opening_hours         = $_POST['opening_hours'];
        $dining_style          = $_POST['dining_style'];
        $owner_name            = trim($_POST['owner_name']);
        $owner_contact_num     = trim($_POST['owner_contact_num']);
        $owner_email           = trim($_POST['owner_email']);

        $error = [];

        // error checking
        // restaurant name
        if (empty($rest_name))
            $error['error']['rest_name'] = "Please enter the restaurant name!";
        elseif (strlen($rest_name) > 100)
            $error['error']['rest_name'] = "Restaurant name must be between 1 and 100 characters!";

        // contact number
        $tel_pattern = "/^(03)-([0-9]{8})$/";
        $tel_pattern2 = "/^(03)([0-9]{8})$/";
        if (!empty($contact_num) && !preg_match($tel_pattern, $contact_num) && !preg_match($tel_pattern2, $contact_num))
            $error['error']['contact_num'] = "Contact number does not appear to be valid!";

        // email
        if (empty($email))
            $error['error']['email'] = "Please enter an email!";
        elseif (strlen($email) > 96 || !filter_var($email, FILTER_VALIDATE_EMAIL))
            $error['error']['email'] = "Email does not appear to be valid!";
        else {
            $sql       = "SELECT email FROM restaurant WHERE email='" . $email . "' AND rest_id != '" . $_SESSION['login_rest_id'] . "'";
            $statement = $connection->query($sql);

            if ($statement->num_rows > 0)
                $error['error']['email'] = "The email ($email) is already used!";
        }

        // address
        if (empty($address))
            $error['error']['address'] = "Please enter an address!";
        elseif (strlen($address) > 400)
            $error['error']['address'] = "Address must be between 1 and 400 characters!";
        else {
            $sql       = "SELECT `address` FROM restaurant WHERE `address`='" . $address . "' AND rest_id != '" . $_SESSION['login_rest_id'] . "'";
            $statement = $connection->query($sql);

            if ($statement->num_rows > 0)
                $error['error']['address'] = "This address is already registered!";
        }

        // owner name
        if (empty($owner_name))
            $error['error']['owner_name'] = "Please enter the owner name!";
        elseif (strlen($owner_name) > 64)
            $error['error']['owner_name'] = "Owner name must be between 1 and 64 characters!";

        // owner contact number
        $tel_pattern = "/^(01[0-9])-([0-9]{7}|[0-9]{8})$/";
        $tel_pattern_2 = "/^(01[0-9]{8}|01[0-9]{9})$/";
        if (empty($owner_contact_num))
            $error['error']['owner_contact_num'] = "Please enter the contact number!";
        elseif (!preg_match($tel_pattern, $owner_contact_num) && !preg_match($tel_pattern_2, $owner_contact_num))
            $error['error']['owner_contact_num'] = "Contact number does not appear to be valid!";

        // owner email
        if (!empty($owner_email) && (strlen($owner_email) > 96 || !filter_var($owner_email, FILTER_VALIDATE_EMAIL)))
            $error['error']['owner_email'] = "Email does not appear to be valid!";
        
        // opening hours
        $opening_hours = json_decode($opening_hours, true);
        foreach ($opening_hours as $key => $opening_hour){
            if ($opening_hour[0] == '0')
                $error['opening_hours']['from-'.(string)$key] = 'Opening hours "From", "Open" and "Close" fields cannot be empty!';
            if ($opening_hour[2] == '')
                $error['opening_hours']['opening_hours_open_'.(string)$key] = 'Opening hours "From", "Open" and "Close" fields cannot be empty!';
            if ($opening_hour[3] == '')
                $error['opening_hours']['opening_hours_close_'.(string)$key] = 'Opening hours "From", "Open" and "Close" fields cannot be empty!';
        }

        //dining style
        if ($dining_style[0] == '0')
            $error['error']['dining_style'] = "Please select a dining style!";
        elseif ($dining_style[0] == 'others' && $dining_style[1] == '')
            $error['error']['dining_style_others'] = "Please enter the dining style!";
        elseif ($dining_style[0] == 'others' && is_numeric($dining_style[1]))
            $error['error']['dining_style_others'] = "Dining style does not appear to be valid!";    
        
        echo json_encode($error);
    }

    if (isset($_POST['submit_form_restaurant'])){

        session_start();
        
        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $rest_name              = trim($_POST['rest_name']);
        $description            = trim($_POST['description']);
        $contact_num            = trim($_POST['contact_num']);
        $email                  = trim($_POST['email']);
        $address                = trim($_POST['address']);
        $city                   = $_POST['city'];
        $opening_hours_counter  = $_POST['opening_hours_counter'];
        $search_keyword         = $_POST['search_keyword'];
        $dining_style           = $_POST['dining_style'];
        $photo_name             = $_POST['photo_name'];
        $sharing_post_title     = trim($_POST['sharing_post_title']);
        $notes                  = trim($_POST['notes']);
        $owner_name             = trim($_POST['owner_name']);
        $owner_contact_num      = trim($_POST['owner_contact_num']);
        $owner_email            = trim($_POST['owner_email']);
        $reset_profile          = $_POST['reset_profile'];
        $reset_cover            = $_POST['reset_cover'];
        $reset_sharing_post_img = $_POST['reset_sharing_post_img'];
        

        //change contact number format
        if ($owner_contact_num[3] != '-'){
            $owner_contact_num = substr($owner_contact_num, 0, 3) . '-' . substr($owner_contact_num, 3);
        }
        if (!empty($contact_num) && $contact_num[2] != '-'){
            $contact_num = substr($contact_num, 0, 2) . '-' . substr($contact_num, 2);
        }

        $current_restaurant = getRestaurant($connection, $_SESSION['login_rest_id']);
        $sharing_post_result = getSharingPostSettings($connection, $_SESSION['login_rest_id']);
        if (!empty($sharing_post_result))
            $sharing_post_settings = json_decode($sharing_post_result['value'], true);
        
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
                    $files = glob('../../uploads/profile_pic/' . $_SESSION['login_rest_id']. '.*');
                    foreach ($files as $file) {
                        unlink($file);
                    }
                    
                    //rename image name
                    $new_name = $_SESSION['login_rest_id']. '.' . $img_ext_lower;

                    //image upload path 
                    $upload_path = '../../uploads/profile_pic/' . $new_name;

                    //move uploaded img to folder
                    move_uploaded_file($tmp_name, $upload_path);
                }
            } else {
                $_SESSION['error'] = "Unknown error occured! Please reload and try again.";
                header("Location: ../pages/restaurant/restaurant.php");
                exit;
            }
        } elseif ($reset_profile == 'remove'){
            $new_name = 'default_pp_rest.png';
            $files = glob('../../uploads/profile_pic/' . $_SESSION['login_rest_id']. '.*');
            foreach ($files as $file) {
                unlink($file);
            }
        } else {
            $new_name = $current_restaurant['rest_profile'];
        }
        
        //cover photo
        //upload cover photo
        if (isset($_FILES['cover_photo']['size']) && $_FILES['cover_photo']['size'] > 0){
            $name      = $_FILES['cover_photo']['name'];
            $tmp_name  = $_FILES['cover_photo']['tmp_name'];
            $img_error = $_FILES['cover_photo']['error'];

            if ($img_error === 0){
                //get image extension
                $img_ext = pathinfo($name, PATHINFO_EXTENSION);
                
                //convert image extension to lower case
                $img_ext_lower = strtolower($img_ext);

                //allowed image extensions
                $allowed_ext = array("jpg", "jpeg", "png");

                if (in_array($img_ext_lower, $allowed_ext)) {
                    $files = glob('../../uploads/cover_photo/' . $_SESSION['login_rest_id']. '.*');
                    foreach ($files as $file) {
                        unlink($file);
                    }
                    //rename image name
                    $new_name_cover = $_SESSION['login_rest_id']. '.' . $img_ext_lower;

                    //image upload path 
                    $upload_path = '../../uploads/cover_photo/' . $new_name_cover;

                    //move uploaded img to folder
                    move_uploaded_file($tmp_name, $upload_path);
                }
            } else {
                $_SESSION['error'] = "Unknown error occured! Please reload and try again.";
                header("Location: ../pages/restaurant/restaurant.php");
                exit;
            }
        } elseif ($reset_cover == 'remove'){
            $new_name_cover = 'default_cover.png';
            $files = glob('../../uploads/cover_photo/' . $_SESSION['login_rest_id']. '.*');
            foreach ($files as $file) {
                unlink($file);
            }
        } else {
            $new_name_cover = $current_restaurant['cover_photo'];
        }

        //sharing post image
        //upload sharing post image
        if (isset($_FILES['sharing_post_img']['size']) && $_FILES['sharing_post_img']['size'] > 0){
            $name      = $_FILES['sharing_post_img']['name'];
            $tmp_name  = $_FILES['sharing_post_img']['tmp_name'];
            $img_error = $_FILES['sharing_post_img']['error'];
            
            if ($img_error === 0){
                //get image extension
                $img_ext = pathinfo($name, PATHINFO_EXTENSION);
                
                //convert image extension to lower case
                $img_ext_lower = strtolower($img_ext);

                //allowed image extensions
                $allowed_ext = array("jpg", "jpeg", "png");

                if (in_array($img_ext_lower, $allowed_ext)) {
                    $files = glob('../../uploads/sharing_post_img/' . $_SESSION['login_rest_id']. '.*');
                    foreach ($files as $file) {
                        unlink($file);
                    }
                    
                    //rename image name
                    $sharing_post_img_new_name = $_SESSION['login_rest_id']. '.' . $img_ext_lower;

                    //image upload path 
                    $upload_path = '../../uploads/sharing_post_img/' . $sharing_post_img_new_name;

                    //move uploaded img to folder
                    move_uploaded_file($tmp_name, $upload_path);
                }
            } else {
                $_SESSION['error'] = "Unknown error occured! Please reload and try again.";
                header("Location: ../pages/restaurant/restaurant.php");
                exit;
            }
        } elseif ($reset_sharing_post_img == 'remove'){
            $files = glob('../../uploads/sharing_post_img/' . $_SESSION['login_rest_id']. '.*');
            foreach ($files as $file) {
                unlink($file);
            }
        } else {
            if (isset($sharing_post_settings['image']))
                $sharing_post_img_new_name = $sharing_post_settings['image'];
        }

        //photos
        //upload restaurant photos
        $photos = json_decode($current_restaurant['photos'], true);
        if (count($photos) != 0 && $photo_name == ''){
            $files = glob('../../uploads/photos/' . $_SESSION['login_rest_id']. '*');
            foreach ($files as $file) {
                unlink($file);
            }
            $photos = array();
        }

        if (isset($_FILES['photos']['size'][0]) && $_FILES['photos']['size'][0] > 0){
            $photo_names      = $_FILES['photos']['name'];
            $photo_tmp_names  = $_FILES['photos']['tmp_name'];
            $photo_errors     = $_FILES['photos']['error'];

            if ((array_count_values($photo_errors))[0] == count($photo_errors)){
                //get photo extension
                foreach ($photo_names as $name)
                    $photo_ext[] = pathinfo($name, PATHINFO_EXTENSION);
                
                //allowed image extensions
                $allowed_ext = array("jpg", "jpeg", "png");

                foreach ($photo_ext as $key => $ext){
                    //convert image extension to lower case
                    $photo_ext_lower = strtolower($ext);

                    if (in_array($photo_ext_lower, $allowed_ext)) {
                        //rename photo name
                        $new_photo_name = $_SESSION['login_rest_id']. '.' . $photo_ext_lower;

                        //image upload path 
                        $upload_path = '../../uploads/photos/' . $new_photo_name;

                        //check if the photo name is already used
                        $counter = 0;
                        while (file_exists($upload_path)) {
                            $counter++;
                            $new_photo_name = $_SESSION['login_rest_id'] . '(' . $counter . ').' . $photo_ext_lower;
                            $upload_path = '../../uploads/photos/' . $new_photo_name;
                        }

                        //move uploaded photos to folder
                        move_uploaded_file($photo_tmp_names[$key], $upload_path);

                        $photos[] = $new_photo_name;
                    }
                }
            } else {
                $_SESSION['error'] = "Unknown error occured! Please reload and try again.";
                header("Location: ../pages/restaurant/restaurant.php");
                exit;
            }
        }

        $photos = json_encode($photos);

        //search keyword
        foreach ($search_keyword as $key => $keyword){
            if (empty($keyword))
                unset($search_keyword[$key]);
            else
                $search_keyword[$key] = trim($keyword);
        }

        $search_keyword = json_encode(array_values($search_keyword));

        //opening hours
        $opening_hours = array();
        $opening_hours_counter = explode(',', substr($opening_hours_counter, 0, strlen($opening_hours_counter)-1));
        foreach ($opening_hours_counter as $oh_counter){
            $an_opening_hour = array();
            $an_opening_hour['from'] = $_POST['opening-hour-'.$oh_counter][0];
            $an_opening_hour['to'] = $_POST['opening-hour-'.$oh_counter][1];
            $an_opening_hour['open'] = $_POST['opening-hour-'.$oh_counter][2];
            $an_opening_hour['close'] = $_POST['opening-hour-'.$oh_counter][3];
            $opening_hours[] = $an_opening_hour;
        }

        $daily_opening_hours = array();
        foreach ($opening_hours as $an_oh){
            if ($an_oh['to'] != '0'){
                $counter = (int)$an_oh['from'];
                while ($counter <= (int)$an_oh['to']){
                    $daily_opening_hours[(string)$counter]['open'] = $an_oh['open'];
                    $daily_opening_hours[(string)$counter]['close'] = $an_oh['close'];
                    $counter++;
                }
            } else {
                $daily_opening_hours[$an_oh['from']]['open'] = $an_oh['open'];
                $daily_opening_hours[$an_oh['from']]['close'] = $an_oh['close'];
            }
        }

        $num_to_day = array(
            '1' => 'Monday',
            '2' => 'Tuesday',
            '3' => 'Wednesday',
            '4' => 'Thursday',
            '5' => 'Friday',
            '6' => 'Saturday',
            '7' => 'Sunday'
        );

        foreach ($daily_opening_hours as $key => $daily_opening_hour){
            $daily_opening_hours[$num_to_day[$key]] = $daily_opening_hour;
            unset($daily_opening_hours[$key]);
        }

        $opening_hours = json_encode($opening_hours);
        $daily_opening_hours = json_encode($daily_opening_hours);

        //dining style
        if ($dining_style[0] == 'others'){
            $new_dining_style = trim($dining_style[1]);
            $sql = "INSERT INTO dining_style(dining_style) VALUES ('" . $new_dining_style . "')";
            $connection->query($sql);

            $sql2 = "SELECT dining_style_id FROM dining_style ORDER BY dining_style_id DESC LIMIT 1";
            $statement = $connection->query($sql2);
            $result = $statement->fetch_array(MYSQLI_ASSOC);
            $dining_style[0] = $result['dining_style_id'];
        }

        $dining_style = $dining_style[0];

        $sql3 = "UPDATE `restaurant` SET `rest_name` = '" . $rest_name . "', description = '" . $description . "', contact_num = '" . $contact_num . "', email = '" . $email . "', address = '" . $address . "', city = " . ($city == '*' ? 'null' : '\''. $city .'\'') . ", opening_hours = '" . $opening_hours . "', daily_opening_hours = '" . $daily_opening_hours . "', tags = '" . $search_keyword . "', dining_style_id = '" . (int)$dining_style . "', additional_notes = '" . $notes . "', owner_name = '" . $owner_name . "', owner_contact_num = '" . $owner_contact_num . "', owner_email = '" . $owner_email . "', photos = '" . $photos . "', rest_profile = '" . $new_name . "', cover_photo = '" . $new_name_cover . "' WHERE rest_id = '" . $_SESSION['login_rest_id'] . "'";

        $result = $connection->query($sql3);

        //update or add sharing post settings
        if ($sharing_post_title)
            $sharing_post_setting_data['title'] = $sharing_post_title;
        if (isset($sharing_post_img_new_name))
            $sharing_post_setting_data['image'] = $sharing_post_img_new_name;

        if (isset($sharing_post_settings)){
            if (isset($sharing_post_setting_data)){
                $sql4 = "UPDATE `settings` SET `value` = '" . json_encode($sharing_post_setting_data) . "' WHERE settings_id = '" . $sharing_post_result['settings_id'] . "'";
            } else {
                $sql4 = "DELETE FROM `settings` WHERE settings_id = '" . $sharing_post_result['settings_id'] . "'";
            }
        } else {
            if (isset($sharing_post_setting_data)){
                $sql4 = "INSERT INTO settings(rest_id, `key`, `value`) VALUES ('" . $_SESSION['login_rest_id'] . "', 'sharing_post', '" . json_encode($sharing_post_setting_data) . "')";
            }
        }

        $connection->query($sql4);

        $_SESSION['success'] = 'You have successfully edited restaurant and owner details.';

        header("Location: ../pages/restaurant/restaurant.php");

    }


?>