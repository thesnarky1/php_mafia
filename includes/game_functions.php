<?php

    function initialize_channels($game_id) {
        global $dbh;
        $channel_members = array(); //user_id=>channel
        $channels = array();
        $users = array();
        //Add in role_channel to roles
        $query = "SELECT roles.role_channel, game_players.user_id ".
                 "FROM roles, game_players ".
                 "WHERE game_players.game_id='$game_id' AND ".
                 "roles.role_id=game_players.role_id";
        $result = mysqli_query($dbh, $query);
        if($result && mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_array($result)) {
                $role_channel = $row['role_channel'];
                $user_id = $row['user_id'];
                if($role_channel != '') {
                    if(substr($role_channel, strlen($role_channel) - 1) == "_") {
                        $role_channel = $role_channel . $user_id;
                    }
                    $channel_members[$user_id] = $role_channel;
                }
                $users[] = $user_id;
            }
        } else {
        }
        foreach($channel_members as $user_id=>$role_channel) {
            if(!isset($channels[$role_channel])) {
                //Create channel
                $channel_name = $role_channel . "_" . $game_id;
                $query = "INSERT INTO channels(channel_name, game_id) ".
                         "VALUES('$channel_name', '$game_id')";
                $result = mysqli_query($dbh, $query);
                if($result && mysqli_affected_rows($dbh) == 1) {
                    $channel_num = mysqli_insert_id($dbh);
                    $channels[$role_channel] = $channel_num;
                    $channel_id = $channel_num;
                    $query = "INSERT INTO channel_members(channel_id, user_id) ".
                             "VALUES('$channel_id', '$user_id')";
                    $result = mysqli_query($dbh, $query);
                }
            } else {
                //Channel is created
                $channel_id = $channels[$role_channel];
                $query = "INSERT INTO channel_members(channel_id, user_id) ".
                         "VALUES('$channel_id', '$user_id')";
                $result = mysqli_query($dbh, $query);
            }
        }
        $channel_name = "game_" . $game_id;
        $query = "INSERT INTO channels(channel_name, game_id) ".
                 "VALUES('$channel_name', '$game_id')";
        $result = mysqli_query($dbh, $query);
        if($result && mysqli_affected_rows($dbh) == 1) {
            $channel_id = mysqli_insert_id($dbh);
            if($channel_id && $channel_id != 0) {
                foreach($users as $user_id) {
                    $query = "INSERT INTO channel_members(channel_id, user_id) ".
                             "VALUES('$channel_id', '$user_id')";
                    $result = mysqli_query($dbh, $query);
                }
            }
        }
    }

?>
