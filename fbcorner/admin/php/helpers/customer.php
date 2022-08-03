<?php

    function getRestaurantCustomers($connection, $rest_id){
        $sql = "SELECT c.cus_id, c.firstname, c.lastname, c.email, c.contact_num, c.profile_pic FROM `cus_rest_history` crh LEFT JOIN customer c ON (crh.cus_id = c.cus_id) WHERE `rest_id` = '" . $rest_id . "' ORDER BY c.firstname ASC";
        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return $results;
    }

?>