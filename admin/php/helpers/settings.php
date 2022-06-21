<?php

    function getSettings($connection, $rest_id){
        $sql = "SELECT * FROM settings WHERE `rest_id` = '" . $rest_id . "'";
        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);
        $simplified_results = [];

        foreach ($results as $result){
            $simplified_results[$result['key']] = $result['value'];
        }

        return $simplified_results;
    }

    function getSetting($connection, $rest_id, $title){
        $sql = "SELECT * FROM settings WHERE `rest_id` = '" . $rest_id . "' AND `key` = '" . $title . "'";
        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        if ($results){
            return json_decode($results['value'], true);
        } else {
            return $results;
        }
    }

?>