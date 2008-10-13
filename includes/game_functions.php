<?php

    function get_user_actions($user_id, $game_id) {
        global $dbh;
        $to_return = array();
        $query = "SELECT game_players.player_ready, game_players.role_id, ".
                 "game_players.player_alive, games.game_phase ".
                 "FROM game_players, games ".
                 "WHERE games.game_id='$game_id' AND game_players.user_id='$user_id' ".
                 "AND game_players.game_id=games.game_id";
        $result = mysqli_query($dbh, $query);
        if($result && mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_array($result);
            $player_ready = $row['player_ready'];
            $player_alive = $row['player_alive'];
            $game_phase = $row['game_phase'];
            $role_id = $row['role_id'];
            if($player_alive == 'Y') {
                if($game_phase == 2 || $game_phase == 0) { //day
                    $query = "SELECT day_action_id, day_alt_action_id FROM roles WHERE role_id='$role_id'";
                    $result = mysqli_query($dbh, $query);
                    if($result && mysqli_num_rows($result) == 1) {
                        $row = mysqli_fetch_array($result);
                        $to_return[] = $row['day_action_id'];
                        $to_return[] = $row['day_alt_action_id'];
                    }
                } else {
                    $query = "SELECT day_action_id, day_alt_action_id FROM roles WHERE role_id='$role_id'";
                    $result = mysqli_query($dbh, $query);
                    if($result && mysqli_num_rows($result) == 1) {
                        $row = mysqli_fetch_array($result);
                        $to_return[] = $row['night_action_id'];
                        $to_return[] = $row['night_alt_action_id'];
                    }
                }
                if($player_ready == 'Y') {
                    $query = "SELECT action_id FROM actions WHERE action_enum='UN_READY'";
                    $result = mysqli_query($dbh, $query);
                    if($result && mysqli_num_rows($result) == 1) {
                        $row = mysqli_fetch_array($result);
                        $to_return[] = $row['action_id'];
                    }
                }
            }
        }
        return $to_return;
    }

    function is_game_over($game_id) {
        global $dbh;
        $roles = array();
        $query = "SELECT roles.role_faction, game_players.player_alive ".
                 "FROM game_players, roles ".
                 "WHERE game_players.game_id='$game_id' AND roles.role_id=game_players.role_id";
        $result = mysqli_query($dbh, $query);
        if($result && mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_array($result)) {
                $role_faction = $row['role_faction'];
                $player_alive = $row['player_alive'];
                if($player_alive == 'Y') {
                    if(!isset($roles[$role_faction])) {
                        $roles[$role_faction] = 0;
                    }
                    $roles[$role_faction]++;
                }
            }
            if(isset($roles['Unknown'])) {
                $over = false;
            } else if(count($roles) == 1) {
                if(isset($roles['Town'])) {
                    $over = true;
                } else if(isset($roles['Antitown'])) {
                    $over = true;
                } else {
                    $over = false;
                }
            }

            if($over) {
                echo "Game over!<br />";
            } else {
                echo "Not over!<br />";
            }
            return $over;
        }
    }

    function capitalize($str) {
        $str = strtoupper(substr($str, 0, 1)) . 
               substr($str, 1);
        return $str;
    }

    function get_system_channel($game_id) {
        global $dbh;
        $system_name = "system_$game_id";
        $query = "SELECT channel_id FROM channels WHERE channel_name='$system_name'";
        $result = mysqli_query($dbh, $query);
        if($result && mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_array($result);
            return $row['channel_id'];
        } else {
            return false;
        }
    }

    function get_system_id() {
        global $dbh;
        $system_name = "System";
        $query = "SELECT user_id FROM users WHERE user_name='$system_name'";
        $result = mysqli_query($dbh, $query);
        if($result && mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_array($result);
            return $row['user_id'];
        } else {
            return false;
        }
    }

    function next_phase($game_id) {
        global $dbh;
        $system_id = get_system_id();
        $chan_id = get_system_channel($game_id);
        $query = "SELECT game_turn, game_phase FROM games WHERE game_id='$game_id'";
        $result = mysqli_query($dbh, $query);
        if($result && mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_array($result);
            $game_turn = $row['game_turn'];
            $game_phase = $row['game_phase'];
            if($game_phase == 1) {
                //Just update game_phase
                $game_phase++;
                $query = "UPDATE games ".
                         "SET game_phase='$game_phase', game_recent_date=NOW() ".
                         "WHERE game_id='$game_id'";
                $result = mysqli_query($dbh, $query);
                if($result && mysqli_affected_rows($dbh) == 1) {
                    if($chan_id) {
                        add_message($chan_id, $system_id, "Another day breaks over the town.");
                    }
                }
            } else {
                //Increment turn as well.
                $game_phase--;
                $game_turn++;
                $query = "UPDATE games ".
                         "SET game_phase='$game_phase', game_turn='$game_turn', game_recent_date=NOW() ".
                         "WHERE game_id='$game_id'";
                $result = mysqli_query($dbh, $query);
                if($result && mysqli_affected_rows($dbh) == 1) {
                    if($chan_id) {
                        add_message($chan_id, $system_id, "Night creeps silently over the town as turn $game_turn begins.");
                    } else {
                        echo "Can't find system channel";
                    }
                }
            }
            update_game_tracker($game_id);
        } else {
        }
    }

    function update_game_tracker($game_id) {
        global $dbh;
        $query = "SELECT game_tracker FROM games WHERE game_id='$game_id'";
        $result = mysqli_query($dbh, $query);
        if($result && mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_array($result);
            $tracking_num = $row['game_tracker'];
            $tracking_num += rand(1, 23);
            $query = "UPDATE games ".
                     "SET game_tracker='$tracking_num' ".
                     "WHERE game_id='$game_id'";
            $result = mysqli_query($dbh, $query);
        }
        $result = mysqli_query($dbh, $query);
    }

    function get_game_information($game_id, $old_game_tracker, $user_id=0) {
        global $dbh, $phases;
        $to_return = "<?xml version='1.0' encoding='UTF-8'?>\n";
        $to_return .= "<game_data>\n";
        $query = "SELECT * FROM games WHERE game_id='$game_id'";
        $result = mysqli_query($dbh, $query);
        if($result && mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_array($result);
            $game_phase = $row['game_phase'];
            $game_creator = $row['game_creator'];
            $game_turn = $row['game_turn'];
            $game_tracker = $row['game_tracker'];
            if($game_tracker != $old_game_tracker) {
                $banner = false;
                $alt_banner = false;
                $to_return .= "<turn>$game_turn</turn>\n";
                $to_return .= "<phase>$phases[$game_phase]</phase>\n";
                $to_return .= "<tracker>$game_tracker</tracker>\n";
                $to_return .= "<player_list>\n";
                $query = "SELECT users.user_name, users.user_avatar, ".
                         "game_players.player_alive, game_players.player_ready, ".
                         "users.user_id, ".
                         "roles.role_name, roles.role_channel, ".
                         "roles.role_faction, roles.role_id ".
                         "FROM users, game_players, roles ".
                         "WHERE game_players.game_id='$game_id' AND ".
                         "roles.role_id=game_players.role_id AND ".
                         "users.user_id=game_players.user_id ".
                         "ORDER BY game_players.player_alive DESC ";
                $result = mysqli_query($dbh, $query);
                if($result && mysqli_num_rows($result) > 0) {
                    $real_channel = false;
                    while($row = mysqli_fetch_array($result)) {
                        $channel = $row['role_channel'];
                        $player_id = $row['user_id'];
                        $user_name = $row['user_name'];
                        $user_avatar = $row['user_avatar'];
                        $player_alive = $row['player_alive'];
                        $player_ready = $row['player_ready'];
                        $role_name = $row['role_name'];
                        $role_faction = $row['role_faction'];
                        $role_id = $row['role_id'];
                        $to_return .= "<player>\n";
                        $to_return .= "<id>$player_id</id>\n";
                        $to_return .= "<name>$user_name</name>\n";
                        $to_return .= "<avatar>$user_avatar</avatar>\n";
                        $to_return .= "<alive>$player_alive</alive>\n";
                        if($player_alive == 'N' || $player_id == $user_id) {
                            $to_return .= "<role_name>$role_name</role_name>\n";
                            $to_return .= "<role_faction>$role_faction</role_faction>\n";
                        }

                        //Anything specific to the player viewing needs to go after this.
                        if($player_id == $user_id) {

                            //Get channel name
                            if($game_phase == 1) { //Daytime
                                if($channel && $channel != "") {
                                    if(false !== strpos($channel, "_")) {
                                        $channel = substr($channel, 0, strpos($channel, "_"));
                                    }
                                    $real_channel = capitalize($channel);
                                } else {
                                    $real_channel = "No";
                                }
                            } else {
                                $real_channel = "Town";
                            }
                            
                            //If alive, we want to put up the banner for actions not targetting people
                            if($player_alive == "Y") {

                                if($game_phase == 2 || $game_phase == 0) { //If daytime
                                    $query = "SELECT * FROM ".
                                             "(SELECT actions.action_id as day_action_id, ".
                                             "actions.action_banner as day_action_banner ".
                                             "FROM actions, roles ".
                                             "WHERE actions.action_id=roles.day_action_id AND ".
                                             "roles.role_id='$role_id') as day_actions, ".
                                             "(SELECT actions.action_id as day_alt_action_id, ".
                                             "actions.action_banner as day_alt_action_banner ".
                                             "FROM actions, roles ".
                                             "WHERE actions.action_id=roles.day_alt_action_id AND ".
                                             "roles.role_id='$role_id') as day_alt_actions";
                                    $result2 = mysqli_query($dbh, $query);
                                    if($result2 && mysqli_num_rows($result2) == 1) {
                                        $row2 = mysqli_fetch_array($result2);
                                        $action_id = $row2['day_action_id'];
                                        $banner_action = $row2['day_alt_action_id'];
                                        $banner = $row2['day_alt_action_banner'];
                                    }
                                } else { //If nighttime
                                    $query = "SELECT * FROM ".
                                             "(SELECT actions.action_id as night_action_id, ".
                                             "actions.action_banner as night_action_banner ".
                                             "FROM actions, roles ".
                                             "WHERE actions.action_id=roles.night_action_id AND ".
                                             "roles.role_id='$role_id') as night_actions, ".
                                             "(SELECT actions.action_id as night_alt_action_id, ".
                                             "actions.action_banner as night_alt_action_banner ".
                                             "FROM actions, roles ".
                                             "WHERE actions.action_id=roles.night_alt_action_id AND ".
                                             "roles.role_id='$role_id') as night_alt_actions";
                                    $result2 = mysqli_query($dbh, $query);
                                    if($result2 && mysqli_num_rows($result2) == 1) {
                                        $row2 = mysqli_fetch_array($result2);
                                        $action_id = $row2['night_action_id'];
                                        $banner_action = $row2['night_alt_action_id'];
                                        $banner = $row2['night_alt_action_banner'];
                                    }
                                }

                                if($player_ready == "Y") {
                                    //player ready, we need banner, banner_action, and un_ready

                                    //In case we want to start the game
                                    if($game_phase == 0 && $game_creator == $user_id) {
                                        $query = "SELECT * FROM actions WHERE action_enum='START'";
                                        $result2 = mysqli_query($dbh, $query);
                                        if($result2 && mysqli_num_rows($result2) == 1) {
                                            $row2 = mysqli_fetch_array($result2);
                                            $banner = $row2['action_banner'];
                                            $banner_action = $row2['action_id'];
                                            $action_id = 0;
                                        } else {
                                            $banner = "ERROR"; //Should never hit here. If we do... let me know
                                        }
                                    }
                                    $query = "SELECT * FROM actions WHERE action_enum='UN_READY'";
                                    $result2 = mysqli_query($dbh, $query);
                                    if($result2 && mysqli_num_rows($result2) == 1) {
                                        $row2 = mysqli_fetch_array($result2);
                                        $alt_banner = $row2['action_banner'];
                                        $alt_banner_action = $row2['action_id'];
                                    }
                                }
                            }
                        }
                        //And before this

                        $to_return .= "</player>\n";
                    }
                    if(!$real_channel) {
                        $real_channel = "No";
                    }
                    $to_return .= "<channel>$real_channel</channel>\n";
                }
                $to_return .= "</player_list>\n";
            }
            if($banner) {
                $to_return .= "<banner>$banner</banner>\n";
                $to_return .= "<banner_action>$banner_action</banner_action>\n";
                $to_return .= "<action>$action_id</action>\n";
            }
            if($alt_banner) {
                $to_return .= "<alt_banner>$alt_banner</alt_banner>\n";
                $to_return .= "<alt_banner_action>$alt_banner_action</alt_banner_action>\n";
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
                                update_game_tracker($game_id);
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
