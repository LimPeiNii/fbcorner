<?php

    function getCategory($connection, $category_id){
        $sql = "SELECT * FROM `category` WHERE `category_id` = '" . $category_id . "'";
        $statement = $connection->query($sql);
        $results = $statement->fetch_array(MYSQLI_ASSOC);

        return $results;
    }

    function getCategories($connection, $section){
        $sql = "SELECT * FROM `category` WHERE `section` = '" . $section . "'";
        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

?>