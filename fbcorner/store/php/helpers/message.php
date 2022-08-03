<?php
    date_default_timezone_set("Asia/Kuala_Lumpur");
    
    function calcTimeAgo($timestamp){
        $time_unit = ['second', 'minute', 'hour', 'day', 'month', 'year'];
        $length = ['60', '60', '24', '30', '12', '10'];

        $difference = time() - $timestamp;

        for ($i=0; $difference >= $length[$i] && $i < count($length)-1; $i++){
            $difference /= $length[$i];
        }

        $difference = round($difference);
        if ($difference < 59 && $time_unit[$i] == 'second'){
            return 'Active';
        } else {
            return 'Active ' . $difference . ' ' . $time_unit[$i] . "(s) ago";
        }
    }

    function getConversationsByCusID($connection, $cus_id){
        $sql = "SELECT r.*, c.conversation_id, ccc.file, ccc.message, ccc.sent_at, ccc.chat_id FROM `conversation` c LEFT JOIN restaurant r ON (c.rest_id = r.rest_id) LEFT JOIN chat ccc ON (ccc.conversation_id = c.conversation_id) WHERE c.cus_id = '" . $cus_id . "' AND ccc.chat_id IN (SELECT max(chat_id) FROM chat GROUP BY conversation_id) ORDER BY ccc.sent_at DESC";
        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    if (isset($_POST['update_conversation_list'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../admin/php/helpers/message.php';

        $json = [];

        $_POST['last_chat_ids'] = json_decode($_POST['last_chat_ids'], true);
        $_POST['has_unseen_conversation_ids'] = json_decode($_POST['has_unseen_conversation_ids'], true);

        //check if has new conversation
        $conversations = getConversationsByCusID($connection, $_SESSION['login_cus_id']);
        if (count($conversations) > count($_POST['last_chat_ids'])){
            $json['new_conversation'] = true;
        } else {
            //check if the id of last msg of each conversation is matched
            foreach ($_POST['last_chat_ids'] as $conversation_id => $last_chat_id){
                $sql = "SELECT chat_id, `file`, `message`, sent_at FROM chat WHERE conversation_id = '" . $conversation_id . "' ORDER BY chat_id DESC LIMIT 1";
                $statement = $connection->query($sql);
                $last_chat_info = $statement->fetch_array(MYSQLI_ASSOC);

                if ($last_chat_info['chat_id'] != $last_chat_id){
                    $new_msg_total_result = getConversationChatsTotal($connection, $conversation_id, ['seen' => 0, 'receiver_id' => $_SESSION['login_cus_id']]);
                    $json['new_msgs'][$conversation_id] = array(
                        'last_chat_info' => $last_chat_info,
                        'total_new_msgs' => $new_msg_total_result['COUNT(*)']
                    );
                } else {
                    $new_msg_total_result = getConversationChatsTotal($connection, $conversation_id, ['seen' => 0, 'receiver_id' => $_SESSION['login_cus_id']]);
                    if ($new_msg_total_result['COUNT(*)'] == 0 && in_array($conversation_id, $_POST['has_unseen_conversation_ids']))
                        $json['refresh'] = true;
                }
            }            
        }

        echo json_encode($json);
        exit;
    }

    if (isset($_POST['send_msg_store'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';
        include_once '../../../admin/php/helpers/message.php';

        updateLastActivity();

        $json = [];

        //sender id
        if (isset($_SESSION['login_cus_id'])){
            $sender_id = $_SESSION['login_cus_id'];
        } elseif (isset($_SESSION['guest_id']) && substr($_SESSION['guest_id'], 0, strpos($_SESSION['guest_id'], 'G')) == $_POST['rest_id']) {
            $sender_id = $_SESSION['guest_id'];
        } else{
            //generate guest id
            $sql = "SELECT *, CAST(SUBSTRING(guest_id, LOCATE('G', guest_id) + 1) AS UNSIGNED) AS guest_id_num FROM rest_guest WHERE rest_id = '" . $_POST['rest_id'] . "' ORDER BY guest_id_num DESC LIMIT 1";
            $statement = $connection->query($sql);
            if ($statement->num_rows > 0){
                $last_id = ($statement->fetch_assoc())['guest_id'];
                $sender_id = $_POST['rest_id'] . 'G' . (string)((int)substr($last_id, strlen($_POST['rest_id']) + 1) + 1);
            } else {
                $sender_id = $_POST['rest_id'] . 'G1';
            }

            $sql2 = "INSERT INTO rest_guest (rest_id, guest_id) VALUES ('" . $_POST['rest_id'] . "','" . $sender_id . "')";
            $connection->query($sql2);

            $_SESSION['guest_id'] = $sender_id;
        }        

        $sql3 = "INSERT INTO `chat`(conversation_id, sender_id, receiver_id, `message`) VALUES (0, '" . $sender_id . "','" . $_POST['rest_id'] . "','" . $_POST['message'] . "')";

        if (!$connection->query($sql3))
            $json['error'] = 'Error sending message, please try again.';
        else{
            //new chat id
            $new_inserted_chat_id = $connection->query('SELECT LAST_INSERT_ID() AS id')->fetch_array(MYSQLI_ASSOC)['id'];

            $json['chat_id'] = $new_inserted_chat_id;

            //add conversation record
            $conversation = getConversationByUsers($connection, $_POST['rest_id'], $sender_id);

            if (empty($conversation)){
                $sql4 = "INSERT INTO `conversation`(cus_id, rest_id) VALUES ('" . $sender_id . "','" . $_POST['rest_id'] . "')";
                $connection->query($sql4);

                $new_conversation_id = $connection->query('SELECT LAST_INSERT_ID() AS id')->fetch_array(MYSQLI_ASSOC)['id'];

                $sql5 = "UPDATE chat SET conversation_id = " . (int)$new_conversation_id . " WHERE chat_id = " . (int)$new_inserted_chat_id;
                $connection->query($sql5);
            } else {
                $sql5 = "UPDATE chat SET conversation_id = " . (int)$conversation['conversation_id'] . " WHERE chat_id = " . (int)$new_inserted_chat_id;
                $connection->query($sql5);
            }

        }

        echo json_encode($json);
        exit;
    }

    if (isset($_POST['send_file_store'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';
        include_once '../../../admin/php/helpers/message.php';

        updateLastActivity();

        //sender id
        if (isset($_SESSION['login_cus_id'])){
            $sender_id = $_SESSION['login_cus_id'];
        } elseif (isset($_SESSION['guest_id']) && substr($_SESSION['guest_id'], 0, strpos($_SESSION['guest_id'], 'G')) == $_POST['rest_id']) {
            $sender_id = $_SESSION['guest_id'];
        } else{
            //generate guest id
            $sql = "SELECT *, CAST(SUBSTRING(guest_id, LOCATE('G', guest_id) + 1) AS UNSIGNED) AS guest_id_num FROM rest_guest WHERE rest_id = '" . $_POST['rest_id'] . "' ORDER BY guest_id_num DESC LIMIT 1";
            $statement = $connection->query($sql);
            if ($statement->num_rows > 0){
                $last_id = ($statement->fetch_assoc())['guest_id'];
                $sender_id = $_POST['rest_id'] . 'G' . (string)((int)substr($last_id, strlen($_POST['rest_id']) + 1) + 1);
            } else {
                $sender_id = $_POST['rest_id'] . 'G1';
            }

            $sql2 = "INSERT INTO rest_guest (rest_id, guest_id) VALUES ('" . $_POST['rest_id'] . "','" . $sender_id . "')";
            $connection->query($sql2);

            $_SESSION['guest_id'] = $sender_id;
        }
        
        //upload files
        $file_results = array();

        foreach ($_FILES as $file_data){
            if (isset($file_data['size']) && $file_data['size'] > 0){
                $file_name      = $file_data['name'];
                $file_tmp_name  = $file_data['tmp_name'];
                $file_error     = $file_data['error'];
            }

            if ($file_error === 0){
                //get file extension
                $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
                
                //convert file extension to lower case
                $file_ext_lower = strtolower($file_ext);

                //allowed file extensions
                $allowed_ext = array("jpg", "jpeg", "png", "pdf", "doc", "docx", "xls", "xlsx", "txt");

                if (in_array($file_ext_lower, $allowed_ext)) {
                    //rename file name
                    $new_file_name = $sender_id . '-' . $file_name;

                    //file upload path 
                    $upload_path = '../../uploads/chat_files/' . $new_file_name;

                    //check if the file name is already used
                    $counter = 0;
                    while (file_exists($upload_path)) {
                        $counter++;
                        $new_file_name = $sender_id . '(' . $counter . ')-' . $file_name;
                        $upload_path = '../../uploads/chat_files/' . $new_file_name;
                    }

                    //move uploaded file to folder
                    move_uploaded_file($file_tmp_name, $upload_path);

                    //insert chat record
                    $sql3 = "INSERT INTO `chat`(conversation_id, sender_id, receiver_id, `file`) VALUES (0, '" . $sender_id . "','" . $_POST['rest_id'] . "','" . $new_file_name . "')";

                    if (!$connection->query($sql3))
                        $file_results[]['error'] = 'Error sending message, please try again.';
                    else{
                        //new chat id
                        $new_inserted_chat_id = $connection->query('SELECT LAST_INSERT_ID() AS id')->fetch_array(MYSQLI_ASSOC)['id'];

                        //add conversation record
                        $conversation = getConversationByUsers($connection, $_POST['rest_id'], $sender_id);

                        if (empty($conversation)){
                            $sql4 = "INSERT INTO `conversation`(cus_id, rest_id) VALUES ('" . $sender_id . "','" . $_POST['rest_id'] . "')";
                            $connection->query($sql4);

                            $new_conversation_id = $connection->query('SELECT LAST_INSERT_ID() AS id')->fetch_array(MYSQLI_ASSOC)['id'];

                            $sql5 = "UPDATE chat SET conversation_id = " . (int)$new_conversation_id . " WHERE chat_id = " . (int)$new_inserted_chat_id;
                            $connection->query($sql5);
                        } else {
                            $sql5 = "UPDATE chat SET conversation_id = " . (int)$conversation['conversation_id'] . " WHERE chat_id = " . (int)$new_inserted_chat_id;
                            $connection->query($sql5);
                        }

                        //add successfully uploaded file's name
                        $file_results[] = array(
                            'name' => $new_file_name,
                            'chat_id' => $new_inserted_chat_id
                        );
                    }
                }
            } else {
                $file_results[]['error'] = 'Unknown error occured! Please try again.';
            }
        }

        echo json_encode($file_results);
        exit;
    }

    if (isset($_POST['remove_guest_id'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();
        
        if (isset($_SESSION['guest_id'])){
            $sql = "UPDATE rest_guest SET `online` = 0 WHERE rest_id = '" . $_POST['rest_id'] . "' AND guest_id = '" . $_SESSION['guest_id'] . "'";
            $connection->query($sql);

            unset($_SESSION['guest_id']);
        }
        exit;
    }

    if (isset($_POST['update_online_status_store'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../helpers/restaurant.php';

        $online_login_activities = getRestaurantLoginActivity($connection, $_POST['rest_id'], ['online' => true]);

        $rest_login_activity = getRestaurantLoginActivity($connection, $_POST['rest_id'], ['last_login_activity' => true]);
        if ($rest_login_activity){
            $rest_login_activity = $rest_login_activity[0];
        }

        if ($online_login_activities){
            echo '<i class="fas fa-circle align-self-center" style="font-size: 10px; color: #31d459"></i><small class="ms-2 text-white" style="font-weight: 500 !important;">Active</small>';
        } elseif (empty($rest_login_activity)) {
            echo '<i class="fas fa-circle align-self-center" style="font-size: 10px; color: #dadada;"></i><small class="ms-2 text-white" style="font-weight: 500 !important;">Inactive</small>';
        } else {
            $online_status = calcTimeAgo(strtotime($rest_login_activity['logout_time']));
            if ($online_status == 'Active') {
                echo '<i class="fas fa-circle align-self-center" style="font-size: 10px; color: #31d459"></i><small class="ms-2 text-white" style="font-weight: 500 !important;">Active</small>';
            } else {
                echo '<i class="fas fa-circle align-self-center" style="font-size: 10px; color: #dadada;"></i><small class="ms-2 text-white" style="font-weight: 500 !important;">' . $online_status . '</small>';
            }
        }

        exit;
    }

    if (isset($_POST['update_read_status_store'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../admin/php/helpers/message.php';

        //sender id
        if (isset($_SESSION['login_cus_id'])){
            $sender_id = $_SESSION['login_cus_id'];
        } elseif (isset($_SESSION['guest_id']) && substr($_SESSION['guest_id'], 0, strpos($_SESSION['guest_id'], 'G')) == $_POST['rest_id']) {
            $sender_id = $_SESSION['guest_id'];
        }
        
        $seen_chat_ids = [];

        if (isset($sender_id)){
            $seen_chats_result = getChats($connection, $sender_id, $_POST['rest_id'], ['seen' => 1]);

            if ($seen_chats_result){
                $seen_chat_ids = array_column($seen_chats_result, 'chat_id');
            }
        }

        echo json_encode($seen_chat_ids);
        exit;
    }

    if (isset($_POST['update_new_chats_store'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../admin/php/helpers/message.php';

        //sender id
        if (isset($_SESSION['login_cus_id'])){
            $sender_id = $_SESSION['login_cus_id'];
        } elseif (isset($_SESSION['guest_id']) && substr($_SESSION['guest_id'], 0, strpos($_SESSION['guest_id'], 'G')) == $_POST['rest_id']) {
            $sender_id = $_SESSION['guest_id'];
        }

        $received_chats = [];

        if (isset($sender_id)){
            $received_chats_result = getChats($connection, $_POST['rest_id'], $sender_id, ['seen' => 0]);

            if ($received_chats_result){
                foreach ($received_chats_result as $chat){
                    if (!empty($chat['message'])){
                        $received_chats[] = array(
                            'chat_id' => $chat['chat_id'],
                            'message' => $chat['message'],
                            'sent_at' => date('d/m/Y h:i a', strtotime($chat['sent_at']))
                        );
                    } else {
                        $received_chats[] = array(
                            'chat_id' => $chat['chat_id'],
                            'file'    => $chat['file'],
                            'sent_at' => date('d/m/Y h:i a', strtotime($chat['sent_at']))
                        );
                    }
                }
            }
        }

        echo json_encode($received_chats);
        exit;
    }

    if (isset($_POST['seen_message'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        //sender id
        if (isset($_SESSION['login_cus_id'])){
            $receiver_id = $_SESSION['login_cus_id'];
        } elseif (isset($_SESSION['guest_id']) && substr($_SESSION['guest_id'], 0, strpos($_SESSION['guest_id'], 'G')) == $_POST['rest_id']) {
            $receiver_id = $_SESSION['guest_id'];
        }

        if (isset($receiver_id)){
            $sql = "UPDATE chat SET `seen` = 1 WHERE receiver_id = '" . $receiver_id . "' AND sender_id = '" . $_POST['rest_id'] . "'";
            $connection->query($sql);
        }

        exit;
    }

    if (isset($_POST['check_has_new_msg'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';
        include_once '../../../admin/php/helpers/message.php';

        updateLastActivity();

        if (isset($_SESSION['login_cus_id'])){
            $unseen_chats_result = getChats($connection, $_POST['rest_id'], $_SESSION['login_cus_id'], ['seen' => 0]);

            if ($unseen_chats_result){
                echo 'true';
            } else {
                echo 'false';
            }
        } else {
            echo 'false';
        }

        exit;
    }

    if (isset($_POST['update_nav_bar_msg'])){
        session_start();

        include_once '../../../db_connect.php';

        if (isset($_SESSION['login_cus_id'])){
            $sql = "SELECT * FROM `chat` WHERE receiver_id = '" . $_SESSION['login_cus_id'] . "' AND seen = 0";
            $statement = $connection->query($sql);
            $results = $statement->fetch_all(MYSQLI_ASSOC);

            if ($results){
                echo 'show';
            } else {
                echo 'hide';
            }
        }
        exit;
    }
?>