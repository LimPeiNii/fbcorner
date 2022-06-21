<?php

    if (isset($_POST['send-contact-form'])){
        include_once '../../../email/send_email.php';
        include_once '../../../session.php';

        updateLastActivity();

        $message = '
        <html>
        <head>
        </head>
        <body>';

        $message .= 'Name: ' . $_POST['footer-name'];

        if (!empty($_POST['footer-phone']))
            $message .= '<br>Phone: ' . $_POST['footer-phone'];

        $message .= "<br>" . $_POST['footer-message'];

        $message .= 
        '</body>
        </html>';

        sendmail("thefbcorner@gmail.com", "Contact Form", $message, '');
    }

?>