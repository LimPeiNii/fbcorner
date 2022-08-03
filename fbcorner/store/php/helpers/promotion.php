<?php

    function getPromotionalAds($connection, $filter_data = array()){
        $sql = "SELECT pa.*, r.rest_id, r.rest_profile, r.rest_name FROM promotional_ads pa LEFT JOIN restaurant r ON (pa.rest_id = r.rest_id) WHERE 1";

        if (isset($filter_data['status'])){
            $sql .= " AND `status` = " . $filter_data['status'];
        }

        if (isset($filter_data['order_by']) && isset($filter_data['asc_desc'])){
            $sql .= " ORDER BY " . $filter_data['order_by'] . " " . $filter_data['asc_desc'];
        }

        $statement = $connection->query($sql);
        $results = $statement->fetch_all(MYSQLI_ASSOC);
        
        return $results;
    }

?>