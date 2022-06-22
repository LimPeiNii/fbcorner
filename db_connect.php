<?php

    $server = 'cxmgkzhk95kfgbq4.cbetxkdyhwsb.us-east-1.rds.amazonaws.com:3306';
    $username = 'vpa9ltfk8f2klijc';
    $password = 'i5szwdaz2f1wps43';

    $db = 'idhuce2uhpwhzlsk';

    // Create connection
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $connection = new mysqli($server, $username, $password, $db);

    // Check connection
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

?>
