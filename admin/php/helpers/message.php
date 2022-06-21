<?php

    function getConversationsByRestID($connection, $rest_id){
        $sql = "SELECT cc.*, c.conversation_id, ccc.file, ccc.message, ccc.sent_at, ccc.chat_id, c.cus_id AS cus_guest_id FROM `conversation` c LEFT JOIN customer cc ON (c.cus_id = cc.cus_id) LEFT JOIN chat ccc ON (ccc.conversation_id = c.conversation_id) WHERE c.rest_id = '" . $rest_id . "' AND ccc.chat_id IN (SELECT max(chat_id) FROM chat GROUP BY conversation_id) ORDER BY ccc.sent_at DESC";
        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    function getConversationByUsers($connection, $rest_id, $cus_id){
        $sql = "SELECT * FROM `conversation` WHERE `rest_id` = '" . $rest_id . "' AND cus_id = '" . $cus_id . "'";
        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        return $results;
    }

    function getCustomerChats($connection, $cus_id, $rest_id){
        $sql = "SELECT * FROM `chat` WHERE (`sender_id` = '" . $cus_id . "' AND receiver_id = '" . $rest_id . "') OR (`sender_id` = '" . $rest_id . "' AND receiver_id = '" . $cus_id . "')";      
        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    function getChats($connection, $sender_id, $receiver_id, $filter_data = array()){
        $sql = "SELECT * FROM `chat` WHERE `sender_id` = '" . $sender_id . "' AND receiver_id = '" . $receiver_id . "'";

        if (isset($filter_data['seen'])){
            $sql .= " AND seen = " . $filter_data['seen'];
        }

        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

    function getConversationChatsTotal($connection, $conversation_id, $filter_data = array()){
        $sql = "SELECT COUNT(*) FROM `chat` WHERE `conversation_id` = '" . $conversation_id . "'";

        if (isset($filter_data['seen'])){
            $sql .= " AND seen = " . (int)$filter_data['seen'];
        }
        
        if (isset($filter_data['receiver_id'])){
            $sql .= " AND receiver_id = '" . $filter_data['receiver_id'] . "'";
        }

        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        return $results;
    }

    function getCustomerLoginActivity($connection, $cus_id){
        $sql = "SELECT * FROM `login_activity` la WHERE `user_id` = '" . $cus_id . "' ORDER BY login_time DESC LIMIT 1";
        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        return $results;
    }

    function getGuestOnlineStatus($connection, $guest_id, $rest_id){
        $sql = "SELECT `online` FROM `rest_guest` WHERE `guest_id` = '" . $guest_id . "' AND rest_id = '" . $rest_id . "'";      
        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        return $results;
    }

    if (isset($_POST['get_customer_list'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../helpers/customer.php';
        include_once '../../../session.php';

        updateLastActivity();

        $json = [];

        $customers = getRestaurantCustomers($connection, $_SESSION['login_rest_id']);

        foreach ($customers as $customer){
            $json[] = array(
                'cus_id' => $customer['cus_id'],
                'name'   => trim($customer['firstname'] . ' ' . $customer['lastname']),
                'email'  => '(' . $customer['email'] . ')'
            );
        }

        echo json_encode($json);
        exit;
    }

    if (isset($_POST['update_conversation_list']) && session_status() === PHP_SESSION_NONE){
        session_start();

        include_once '../../../db_connect.php';

        $json = [];

        $_POST['last_chat_ids'] = json_decode($_POST['last_chat_ids'], true);

        //check if has new conversation
        $conversations = getConversationsByRestID($connection, $_SESSION['login_rest_id']);
        if (count($conversations) > count($_POST['last_chat_ids'])){
            $json['new_conversation'] = true;
        } else {
            //check if the id of last msg of each conversation is matched
            foreach ($_POST['last_chat_ids'] as $conversation_id => $last_chat_id){
                $sql = "SELECT chat_id, `file`, `message`, sent_at FROM chat WHERE conversation_id = '" . $conversation_id . "' ORDER BY chat_id DESC LIMIT 1";
                $statement = $connection->query($sql);
                $last_chat_info = $statement->fetch_array(MYSQLI_ASSOC);

                if ($last_chat_info['chat_id'] != $last_chat_id){
                    $new_msg_total_result = getConversationChatsTotal($connection, $conversation_id, ['seen' => 0, 'receiver_id' => $_SESSION['login_rest_id']]);
                    $json['new_msgs'][$conversation_id] = array(
                        'last_chat_info' => $last_chat_info,
                        'total_new_msgs' => $new_msg_total_result['COUNT(*)']
                    );
                }
            }            
        }

        echo json_encode($json);
        exit;
    }

    if (isset($_POST['send_msg'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $json = [];       

        $sql = "INSERT INTO `chat`(conversation_id, sender_id, receiver_id, `message`) VALUES (0, '" . $_SESSION['login_rest_id'] . "','" . $_POST['cus_guest_id'] . "','" . $_POST['message'] . "')";

        if (!$connection->query($sql))
            $json['error'] = 'Error sending message, please try again.';
        else{
            //new chat id
            $new_inserted_chat_id = $connection->query('SELECT LAST_INSERT_ID() AS id')->fetch_array(MYSQLI_ASSOC)['id'];

            $json['chat_id'] = $new_inserted_chat_id;

            //conversation record
            $conversation = getConversationByUsers($connection, $_SESSION['login_rest_id'], $_POST['cus_guest_id']);

            if (empty($conversation)){
                $sql2 = "INSERT INTO `conversation` (cus_id, rest_id) VALUES ('" . $_POST['cus_guest_id'] . "', '" . $_SESSION['login_rest_id'] . "')";
                $connection->query($sql2);
                $conversation_id = $connection->insert_id;
            } else 
                $conversation_id = $conversation['conversation_id'];

            $sql3 = "UPDATE chat SET conversation_id = " . (int)$conversation_id . " WHERE chat_id = " . (int)$new_inserted_chat_id;
            $connection->query($sql3);
        }

        echo json_encode($json);
        exit;
    }

    if (isset($_POST['send_file'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();
        
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
                    $new_file_name = $_SESSION['login_rest_id'] . '-' . $file_name;

                    //file upload path 
                    $upload_path = '../../uploads/chat_files/' . $new_file_name;

                    //check if the file name is already used
                    $counter = 0;
                    while (file_exists($upload_path)) {
                        $counter++;
                        $new_file_name = $_SESSION['login_rest_id'] . '(' . $counter . ')-' . $file_name;
                        $upload_path = '../../uploads/chat_files/' . $new_file_name;
                    }

                    //move uploaded file to folder
                    move_uploaded_file($file_tmp_name, $upload_path);

                    //insert chat record
                    $sql = "INSERT INTO `chat`(conversation_id, sender_id, receiver_id, `file`) VALUES (0, '" . $_SESSION['login_rest_id'] . "','" . $_POST['cus_guest_id'] . "','" . $new_file_name . "')";

                    if (!$connection->query($sql))
                        $file_results[]['error'] = 'Error sending message, please try again.';
                    else{
                        //new chat id
                        $new_inserted_chat_id = $connection->query('SELECT LAST_INSERT_ID() AS id')->fetch_array(MYSQLI_ASSOC)['id'];

                        //conversation record
                        $conversation = getConversationByUsers($connection, $_SESSION['login_rest_id'], $_POST['cus_guest_id']);

                        if (empty($conversation)){
                            $sql5 = "INSERT INTO `conversation` (cus_id, rest_id) VALUES ('" . $_POST['cus_guest_id'] . "', '" . $_SESSION['login_rest_id'] . "')";
                            $connection->query($sql5);
                            $conversation_id = $connection->insert_id;
                        } else 
                            $conversation_id = $conversation['conversation_id'];

                        $sql6 = "UPDATE chat SET conversation_id = " . (int)$conversation_id . " WHERE chat_id = " . (int)$new_inserted_chat_id;
                        $connection->query($sql6);

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

    if (isset($_POST['update_online_status'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../store/php/helpers/message.php';
        
        if ($_POST['cus_guest_id'][0] == 'C'){
            $cus_login_activity = getCustomerLoginActivity($connection, $_POST['cus_guest_id']);
        } else {
            $guest_online_status = getGuestOnlineStatus($connection, $_POST['cus_guest_id'], $_SESSION['login_rest_id']);
        }

        if (isset($guest_online_status)) {
            echo '<i class="fas fa-circle align-self-center' . ($guest_online_status['online'] == 1 ? '' : ' d-none') . '" style="font-size: 10px; color: #31d459;"></i><small class="ms-2 text-white' . ($guest_online_status['online'] == 1 ? '' : ' d-none') . '" style="font-weight: 500 !important;">Active</small>';
        } else if (empty($cus_login_activity)){
            echo '<i class="fas fa-circle align-self-center" style="font-size: 10px; color: #dadada;"></i><small class="ms-2 text-white" style="font-weight: 500 !important;">Inactive</small>';
        } else if (empty($cus_login_activity['logout_time'])){
            echo '<i class="fas fa-circle align-self-center" style="font-size: 10px; color: #31d459"></i><small class="ms-2 text-white" style="font-weight: 500 !important;">Active</small>';
        } else {
            $online_status = calcTimeAgo(strtotime($cus_login_activity['logout_time']));
            if ($online_status == 'Active') {
                echo '<i class="fas fa-circle align-self-center" style="font-size: 10px; color: #31d459"></i><small class="ms-2 text-white" style="font-weight: 500 !important;">Active</small>';
            } else {
                echo '<i class="fas fa-circle align-self-center" style="font-size: 10px; color: #dadada;"></i><small class="ms-2 text-white" style="font-weight: 500 !important;">' . $online_status . '</small>';
            }
        }

        exit;
    }

    if (isset($_POST['update_read_status'])){
        session_start();

        include_once '../../../db_connect.php';
        
        $seen_chat_ids = [];

        $seen_chats_result = getChats($connection, $_SESSION['login_rest_id'], $_POST['cus_guest_id'], ['seen' => 1]);

        if ($seen_chats_result){
            $seen_chat_ids = array_column($seen_chats_result, 'chat_id');
        }

        echo json_encode($seen_chat_ids);
        exit;
    }

    if (isset($_POST['update_new_chats'])){
        session_start();

        include_once '../../../db_connect.php';

        $received_chats = [];

        $received_chats_result = getChats($connection, $_POST['cus_guest_id'], $_SESSION['login_rest_id'], ['seen' => 0]);

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

        echo json_encode($received_chats);
        exit;
    }

    if (isset($_POST['seen_message'])){
        session_start();

        include_once '../../../db_connect.php';
        include_once '../../../session.php';

        updateLastActivity();

        $sql = "UPDATE chat SET `seen` = 1 WHERE receiver_id = '" . $_SESSION['login_rest_id'] . "' AND sender_id = '" . $_POST['cus_guest_id'] . "'";
        $connection->query($sql);

        exit;
    }

    if (isset($_POST['update_nav_bar_msg'])){
        session_start();

        include_once '../../../db_connect.php';

        if (isset($_SESSION['login_rest_id'])){
            $sql = "SELECT * FROM `chat` WHERE receiver_id = '" . $_SESSION['login_rest_id'] . "' AND seen = 0";
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