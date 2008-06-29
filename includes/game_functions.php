<?php

    function capitalize($str) {
        $str = strtoupper(substr($str, 0, 1)) . 
               substr($str, 1);
        return $str;
    }

    function get_game_information($game_id, $old_game_turn, $old_game_phase, $user_id) {
        global $dbh, $phases;
        $to_return = "<?xml version='1.0' encoding='UTF-8'?>\n";
        $to_return .= "<game_data>\n";
        $query = "SELECT * FROM games WHERE game_id='$game_id'";
        $result = mysqli_query($dbh, $query);
        if($result && mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_array($result);
            $game_phase = $row['game_phase'];
            $game_turn = $row['game_turn'];
            if($game_turn != $old_game_turn || $phases[$game_phase] != $old_game_phase) {
                $to_return .= "<turn>$game_turn</turn>\n";
                $to_return .= "<phase>$phases[$game_phase]</phase>\n";
                $to_return .= "<player_list>\n";
                $query = "SELECT users.user_name, users.user_avatar, ".
                         "game_players.player_alive, users.user_id, ".
                         "roles.role_name, roles.role_faction ".
                         "FROM users, game_players, roles ".
                         "WHERE game_players.game_id='$game_id' AND ".
                         "roles.role_id=game_players.role_id AND ".
                         "users.user_id=game_players.user_id ".
                         "ORDER BY game_players.player_alive DESC ";
                $result = mysqli_query($dbh, $query);
                if($result && mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_array($result)) {
                        $player_id = $row['user_id'];
                        $user_name = $row['user_name'];
                        $user_avatar = $row['user_avatar'];
                        $player_alive = $row['player_alive'];
                        $role_name = $row['role_name'];
                        $role_faction = $row['role_faction'];
                        $to_return .= "<player>\n";
                        $to_return .= "<id>$player_id</id>\n";
                        $to_return .= "<name>$user_name</name>\n";
                        $to_return .= "<avatar>$user_avatar</avatar>\n";
                        $to_return .= "<alive>$player_alive</alive>\n";
                        if($player_alive == 'N' || $player_id == $user_id) {
                            $to_return .= "<role_name>$role_name</role_name>\n";
                            $to_return .= "<role_faction>$role_faction</role_faction>\n";
                        }
                        $to_return .= "</player>\n";
                    }
                }
                $to_return .= "</player_list>\n";
            }
        }
        $to_return .= "</game_data>\n";
        return $to_return;
    }

    function kill_player($user_id, $game_id) {
        global $dbh;
        $query = "SELECT user_name FROM users WHERE user_id='$user_id'";
        $result = mysqli_query($dbh, $query);
        if($result && mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_array($result);
            $user_name = $row['user_name'];
            $query = "UPDATE channel_members, channels ".
                     "SET channel_members.channel_post_rights='0' ".
                     "WHERE channel_members.user_id='$user_id' AND ".
                     "channels.channel_id=channel_members.channel_id AND ".
                     "channels.game_id='$game_id'";
            $result = mysqli_query($dbh, $query);
            if($result && mysqli_affected_rows($dbh) > 0) {
                //Success
                $query = "UPDATE game_players ".
                         "SET player_alive='N' ".
                         "WHERE user_id='$user_id' AND game_id='$game_id'";
                $result = mysqli_query($dbh, $query);
                if($result && mysqli_affected_rows($dbh) > 0) {
                    //Success
                    $query = "SELECT channel_id FROM channels ".
                             "WHERE game_id='$game_id' AND channel_name LIKE 'system_%'";
                    $result = mysqli_query($dbh, $query);
                    if($result && mysqli_num_rows($result) == 1) {
                        $row = mysqli_fetch_array($result);
                        $channel_id = $row['channel_id'];
                        $query = "SELECT user_id FROM users WHERE user_name='System'";
                        $result = mysqli_query($dbh, $query);
                        if($result && mysqli_num_rows($result) == 1) {
                            $row = mysqli_fetch_array($result);
                            $system_id = $row['user_id'];
                            $message_text = "$user_name was killed.";
                            $query = "INSERT INTO channel_messages(channel_id, user_id, message_text, message_date) ".
                                     "VALUES('$channel_id', '$system_id', '$message_text', NOW())";
                            $result = mysqli_query($dbh, $query);
                            if($result && mysqli_affected_rows($dbh) == 1) {
                                //Success in killing
                            }
                        }
                    } else {
                        echo "Error getting system channel id. " . $query;
                    }
                } else {
                    echo "Error setting player to dead. " . $query;
                }
            } else {
                echo "Error setting post rights to null. " . $query;
            }
        }
    }

    function initialize_channels($game_id) {
        global $dbh;
        $channel_members = array(); //user_id=>channel
        $channels = array();
        $users = array();
        //Add in role_channel to roles
        $query = "SELECT roles.role_channel_rights, roles.role_channel, ".
                 "game_players.user_id ".
                 "FROM roles, game_players ".
                 "WHERE game_players.game_id='$game_id' AND ".
                 "roles.role_id=game_players.role_id";
        $result = mysqli_query($dbh, $query);
        if($result && mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_array($result)) {
                $role_channel = $row['role_channel'];
                $role_channel_rights = $row['role_channel_rights'];
                $user_id = $row['user_id'];
                if($role_channel != '') {
                    if(substr($role_channel, strlen($role_channel) - 1) == "_") {
                        $role_channel = $role_channel . $user_id;
                    }
                    $channel_members[$user_id] = array($role_channel, $role_channel_rights);
                }
                $users[] = $user_id;
            }
        } else {
        }
        foreach($channel_members as $user_id=>$role_array) {
            $role_channel = $role_array[0];
            $role_rights = $role_array[1];
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
                    $query = "INSERT INTO channel_members(channel_id, user_id, channel_post_rights) ".
                             "VALUES('$channel_id', '$user_id', '$role_rights')";
                    $result = mysqli_query($dbh, $query);
                }
            } else {
                //Channel is created
                $channel_id = $channels[$role_channel];
                $query = "INSERT INTO channel_members(channel_id, user_id, channel_post_rights) ".
                         "VALUES('$channel_id', '$user_id', '$role_rights')";
                $result = mysqli_query($dbh, $query);
            }
        }
        $channel_name = "game_" . $game_id;
        $query = "INSERT INTO channels(channel_name, game_id, global) ".
                 "VALUES('$channel_name', '$game_id', 'Y')";
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
        $channel_name = "system_" . $game_id;
        $query = "INSERT INTO channels(channel_name, game_id, global) ".
                 "VALUES('$channel_name', '$game_id', 'Y')";
        $result = mysqli_query($dbh, $query);
        if($result && mysqli_affected_rows($dbh) == 1) {
            $channel_id = mysqli_insert_id($dbh);
            if($channel_id && $channel_id != 0) {
                foreach($users as $user_id) {
                    $query = "INSERT INTO channel_members(channel_id, user_id, channel_post_rights) ".
                             "VALUES('$channel_id', '$user_id', '0')";
                    $result = mysqli_query($dbh, $query);
                }
            }
        }
    }

?>
