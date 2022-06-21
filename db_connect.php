<?php

    $server = 'www.000webhost.com';
    $username = 'id19145827_root';
    $password = 'I-h77^>EZ#epiM-{';

    $db = 'id19145827_fbcorner';

    // Create connection
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $connection = new mysqli($server, $username, $password, $db);

    // Check connection
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

?>
