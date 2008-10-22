<?php

    function mysqli_get_one($query) {
        global $dbh;
        $result = mysqli_query($dbh, $query);
        if($result && mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_array($result);
            return $row;
        } else {
            return false;
        }
    }

?>
