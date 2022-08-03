<?php

    include_once '../../../db_connect.php';
    
    if (isset($_POST['submit'])){
        $rest_name    = trim($_POST['rest_name']);
        $owner_name   = trim($_POST['owner_name']);
        $contact_num  = trim($_POST['contact_num']);
        $email        = trim($_POST['email']);
        $address      = trim($_POST['address']);
        $p_p          = $_POST['p_p'];

        if (isset($_POST['docs']))
            $docs     = $_POST['docs'];

        $error = [];

        // error checking
        // restaurant name
        if (empty($rest_name))
            $error['rest_name'] = "Please enter the restaurant name!";
        elseif (strlen($rest_name) > 100)
            $error['rest_name'] = "Restaurant name must be between 1 and 100 characters!";
        
        // owner name
        if (empty($owner_name))
            $error['owner_name'] = "Please enter the owner name!";
        elseif (strlen($owner_name) > 64)
            $error['owner_name'] = "Owner name must be between 1 and 64 characters!";

        // contact number
        $tel_pattern = "/^(01[0-9])-([0-9]{7}|[0-9]{8})$/";
        $tel_pattern_2 = "/^(01[0-9]{8}|01[0-9]{9})$/";
        if (empty($contact_num))
            $error['contact_num'] = "Please enter the contact number!";
        elseif (!preg_match($tel_pattern, $contact_num) && !preg_match($tel_pattern_2, $contact_num))
            $error['contact_num'] = "Contact number does not appear to be valid!";

        // email
        if (empty($email))
            $error['email'] = "Please enter an email!";
        elseif (strlen($email) > 96 || !filter_var($email, FILTER_VALIDATE_EMAIL))
            $error['email'] = "Email does not appear to be valid!";
        else {
            $sql       = "SELECT email FROM restaurant WHERE email='" . $email . "'";
            $statement = $connection->query($sql);

            if ($statement->num_rows > 0)
                $error['email'] = "The email ($email) is already registered!";
        }

        // address
        if (empty($address))
            $error['address'] = "Please enter an address!";
        elseif (strlen($address) > 400)
            $error['address'] = "Address must be between 1 and 400 characters!";
        else {
            $sql       = "SELECT `address` FROM restaurant WHERE `address`='" . $address . "'";
            $statement = $connection->query($sql);

            if ($statement->num_rows > 0)
                $error['address'] = "This address is already registered!";
        }

        // profile picture (extension type)
        if (!empty($p_p)){
            $allowed_ext = array("jpg", "jpeg", "png");
            if (!in_array(strtolower(pathinfo($p_p, PATHINFO_EXTENSION)), $allowed_ext))
                $error['p_p'] = 'You can only upload files with type "jpg", "jpeg", "png"!';
        }

        // documents
        $allowed_ext = array("pdf", "doc", "docx", "xls", "xlsx", "txt");

        if (!isset($docs))
            $error['docs'] = "Please upload relevant restaurant documents!";
        else{
            foreach ($docs as $doc){
                if (!in_array(strtolower(pathinfo($doc, PATHINFO_EXTENSION)), $allowed_ext)){
                    $error['docs'] = 'You can only upload files with type "pdf", "doc", "docx", "xls", "xlsx", "txt"!';
                    break;
                }
            }
        }

        // after error checking
        if (!empty($error))
            echo '<i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;There are errors on the form. Please fix them before continuing.';
        else{
            echo '';
        }

    }
    else{
        echo '<i class="fas fa-exclamation-circle"></i>&nbsp;&nbsp;There was an error! Please try again.';
    }
?>

<script>
    var error = <?php echo json_encode($error)?>;

    if (error.length != 0){
        $('.form-message').addClass('alert-danger');
        $('form').addClass('hasError');
    }

    if (error['rest_name']){
        $('#rest-name').text(error['rest_name']);
        $("input[name='rest-name']").addClass('red-box-shadow is-invalid');
    }
    if (error['owner_name']){
        $('#owner-name').text(error['owner_name']);
        $("input[name='owner-name']").addClass('red-box-shadow is-invalid');
    }
    if (error['contact_num']){
        $('#contact-num').text(error['contact_num']);
        $("input[name='tel']").addClass('red-box-shadow is-invalid');
    }
    if (error['email']){
        $('#email').text(error['email']);
        $("input[name='email']").addClass('red-box-shadow is-invalid');
    }
    if (error['address']){
        $('#address').text(error['address']);
        $("textarea[name='address']").addClass('red-box-shadow is-invalid');
    }
    if (error['p_p']){
        $('#p_p').text(error['p_p']);
        $("input[name='profile_pic'").addClass('red-box-shadow is-invalid');
    }
    if (error['docs']){
        $('#docs').text(error['docs']);
        $("input[name='documents[]'").addClass('red-box-shadow is-invalid');
    }

    if (!($("#signup-form").hasClass("hasError"))){
        $('#spinner').removeClass('d-none');
        $('#spinner').parent().prop('disabled', true);
        $("#signup-form").unbind().submit();
    } else {
        $('html, body').animate({ scrollTop: 0 }, 0);
    }

</script>