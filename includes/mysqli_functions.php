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

    function mysqli_insert($query) {
        global $dbh;
        $result = mysqli_query($dbh, $query);
        if($result && mysqli_affected_rows($dbh) == 1) {
            return mysqli_insert_id($dbh);
        } else {
            return false;
        }
    }

    function mysqli_set($query) {
        global $dbh;
        $result = mysqli_query($dbh, $query);
        if($result && mysqli_affected_rows($dbh) == 1) {
            return true;
        } else {
            return false;
        }
    }
    
    function mysqli_get_many($query) {
        global $dbh;
        $result = mysqli_query($dbh, $query);
        if($result && mysqli_num_rows($result) > 0) {
            $to_return = array();
            while($row = mysqli_fetch_array($result)) {
                $to_return[] = $row;
            }
            return $to_return;
        }
        return false;
    }
?>
