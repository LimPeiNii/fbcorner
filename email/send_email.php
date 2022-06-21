<?php

    use PHPMailer\PHPMailer\PHPMailer;
    function sendmail($to, $subject, $body, $success_msg){
        $name = "F&B Corner";
        $from = "thefbcorner@gmail.com";
        $password = "kvkupyfmdypoyuix";

        require_once "PHPMailer/PHPMailer.php";
        require_once "PHPMailer/SMTP.php";
        require_once "PHPMailer/Exception.php";
        $mail = new PHPMailer();

        //SMTP Settings
        $mail->isSMTP();
        // $mail->SMTPDebug = 3; // Debug
        $mail->Host = "smtp.gmail.com"; // smtp address of the sender email
        $mail->SMTPAuth = true;
        $mail->Username = $from;
        $mail->Password = $password;
        $mail->Port = 587;
        $mail->SMTPSecure = "tls"; // tls or ssl
        $mail->smtpConnect([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
            ]
        ]);

        //Email Settings
        $mail->isHTML(true);
        $mail->setFrom($from, $name);
        $mail->addAddress($to);
        $mail->Subject = ("$subject");
        $mail->Body = $body;
        if ($mail->send()) {
            return $success_msg . " Confirmation email has been succesfully sent.";
        } else {
            return $success_msg . " Unable to send an email. Please contact the system administrator.";
        }
    }

?>