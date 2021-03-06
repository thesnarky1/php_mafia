<?php

    function revoke_channel($channel_id) {
        $query = "UPDATE channel_members SET channel_post_rights='0' WHERE channel_id='$channel_id'";
        mysqli_set_many($query);
    }

    function get_channel_by_name($channel_name, $game_id) {
        global $dbh;
        $query = "SELECT channel_id FROM channels ".
                 "WHERE channel_name='$channel_name' AND game_id='$game_id'";
        if($row = mysqli_get_one($query)) { //more or less and we don't want it!
            return $row['channel_id'];
        } else {
            return false;
        }
    }

    function get_system_channel($game_id) {
        return get_channel_by_name("system_$game_id", $game_id);
    }

    function get_system_id() {
        global $dbh;
        $system_name = "System";
        $query = "SELECT user_id FROM users WHERE user_name='$system_name'";
        if($row = mysqli_get_one($query)) {
            return $row['user_id'];
        } else {
            return false;
        }
    }


    function initialize_channels($game_id) {
        global $dbh;
        $channel_members = array(); //user_id=>channel
        $channels = array();
        $users = array();

        //Kill off ability to chat on unassigned channel
        $channel_id = get_channel_by_name("unassigned_" . $game_id, $game_id);
        $query = "DELETE FROM channel_members ".
                 "WHERE channel_id='$channel_id' ";
        $result = mysqli_query($dbh, $query);

        //Add in role_channel to roles
        $query = "SELECT roles.role_channel_rights, roles.role_channel, ".
                 "game_players.user_id ".
                 "FROM roles, game_players ".
                 "WHERE game_players.game_id='$game_id' AND ".
                 "roles.role_id=game_players.role_id";
        if($rows = mysqli_get_many($query)) {
            foreach($rows as $row) {
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
        }
        foreach($channel_members as $user_id=>$role_array) {
            $role_channel = $role_array[0];
            $role_rights = $role_array[1];
            if(!isset($channels[$role_channel])) {
                //Create channel
                $channel_name = $role_channel . "_" . $game_id;
                $query = "INSERT INTO channels(channel_name, game_id) ".
                         "VALUES('$channel_name', '$game_id')";
                if($channel_num = mysqli_insert($query)) {
                    $channels[$role_channel] = $channel_num;
                    $channel_id = $channel_num;
                    $query = "INSERT INTO channel_members(channel_id, user_id, channel_post_rights) ".
                             "VALUES('$channel_id', '$user_id', '$role_rights')";
                    mysqli_insert($query);
                }
            } else {
                //Channel is created
                $channel_id = $channels[$role_channel];
                $query = "INSERT INTO channel_members(channel_id, user_id, channel_post_rights) ".
                         "VALUES('$channel_id', '$user_id', '$role_rights')";
                mysqli_insert($query);
            }
        }

        //Add game channel
        $channel_name = "game_" . $game_id;
        $query = "INSERT INTO channels(channel_name, game_id, global) ".
                 "VALUES('$channel_name', '$game_id', 'Y')";
        if($channel_id = mysqli_insert($query)) {
            if($channel_id && $channel_id != 0) {
                foreach($users as $user_id) {
                    $query = "INSERT INTO channel_members(channel_id, user_id) ".
                             "VALUES('$channel_id', '$user_id')";
                    mysqli_insert($query);
                }
            }
        }

        //Add system channel
        $channel_name = "system_" . $game_id;
        $query = "INSERT INTO channels(channel_name, game_id, global) ".
                 "VALUES('$channel_name', '$game_id', 'Y')";
        if($channel_id = mysqli_insert($query)) {
            if($channel_id && $channel_id != 0) {
                foreach($users as $user_id) {
                    $query = "INSERT INTO channel_members(channel_id, user_id, channel_post_rights) ".
                             "VALUES('$channel_id', '$user_id', '0')";
                    mysqli_insert($query);
                }
            }
        }
    }

    function add_message($channel_id, $user_id, $message) {
        global $dbh;
        $message = mysqli_real_escape_string($dbh, $message);
        if($message != "") {
            $query = "INSERT INTO channel_messages(channel_id, user_id, message_text, message_date) ".
                     "VALUES('$channel_id', '$user_id', '$message', NOW())";
            if(mysqli_insert($query)) {
            } else {
                //echo "$query";
            }
        }
    }

    function show_chat_error($error) {
        $xml = "<?xml version='1.0' encoding='UTF-8'?>\n";
        $xml .= "<messages>\n";
        $xml .= "<message>\n";
        $xml .= "<id>-1</id>\n";
        $xml .= "<user>Error</user>\n";
        $xml .= "<date>Now</date>\n";
        $xml .= "<text>$error</text>\n";
        $xml .= "<channel>system.png</channel>\n";
        $xml .= "</message>\n";
        $xml .= "</messages>\n";
        return "$xml";
    }

   function retrieve_new_messages($user_id, $game_id, $id = 0, $wants_array=false) {
       global $dbh;
       global $channel_images;
       $return_array = array();
       if($user_id == 0 || $user_id == "") {
           $user_belongs = false;
       } else {
           if(user_belongs($game_id, $user_id)) {
               $user_belongs = true;
           } else {
               $user_belongs = false;
           }
       }
       $channels = array();
       $query = "SELECT game_phase FROM games WHERE game_id='$game_id'";
       if($row = mysqli_get_one($query)) {
           $game_phase = $row['game_phase'];
       }
/*       if($game_phase == 3) {
           //Game finished, pull all
            $query = "SELECT * FROM channels WHERE game_id='$game_id'";
            if($rows = mysqli_get_many($query)) {
                foreach($rows as $row) {
                    $channels[] = $row['channel_id'];
                }
            }
           if(count($channels) > 0) {
               $query = "SELECT users.user_name, channels.channel_name, channel_messages.message_id, ".
                        "channel_messages.message_text, ".
                        "DATE_FORMAT(channel_messages.message_date, '%T') as message_date ".
                        "FROM channels, channel_messages, users ".
                        "WHERE users.user_id=channel_messages.user_id AND channel_messages.message_id > '$id' AND ".
                        "channels.channel_id=channel_messages.channel_id ";
               $channel_query = " AND (";
               foreach($channels as $channel) {
                   if($channel_query != " AND (") {
                       $channel_query .= " OR ";
                   }
                   $channel_query .= " channel_messages.channel_id='$channel' ";
               }
               $channel_query .= ")";
               $xml = "<?xml version='1.0' encoding='UTF-8'?>\n";
               $xml .= "<messages>\n";
               $query = $query . $channel_query . " ORDER BY channel_messages.message_date";
               if($rows = mysqli_get_many($query)) {
                   foreach($rows as $row) {
                       $channel_name = $row['channel_name'];
                       $channel_name = strtoupper(substr($channel_name, 0, 1)) . 
                                       substr($channel_name, 1, strpos($channel_name, "_") - 1);
                       $channel_name = $channel_images[$channel_name];
                       $message_text = stripslashes(htmlentities($row['message_text']));
                       $message_date = $row['message_date'];
                       $message_id = $row['message_id'];
                       $user_name = capitalize($row['user_name']);
                       if($wants_array) {
                           $return_array[$message_id] = array('user'=>$user_name,
                                                              'date'=>$message_date,
                                                              'text'=>$message_text,
                                                              'channel'=>$channel_name);
                       } else {
                           $xml .= "<message>\n";
                           $xml .= "<id>$message_id</id>\n";
                           $xml .= "<user>$user_name</user>\n";
                           $xml .= "<date>$message_date</date>\n";
                           $xml .= "<text>$message_text</text>\n";
                           $xml .= "<channel>$channel_name</channel>\n";
                           $xml .= "</message>\n";
                       }
                   }
               } else {
                   echo $query;
               }
               $xml .= "</messages>\n";
               if($wants_array) {
                   return $return_array;
               } else {
                   return "$xml";
               }
           }
       } else { */
           if($user_belongs) {
               $query = "SELECT channel_members.channel_id ".
                        "FROM channel_members, channels ".
                        "WHERE channel_members.user_id='$user_id' AND channels.game_id='$game_id' AND ".
                        "channel_members.channel_id=channels.channel_id";
           } else {
               $query = "SELECT channels.channel_id ".
                        "FROM channels ".
                        "WHERE channels.game_id='$game_id' AND channels.global='Y'";
           }
           if($rows = mysqli_get_many($query)) {
               foreach($rows as $row) {
                   $channels[] = $row['channel_id'];
               }
           } else {
               echo "$query";
           }
           if(count($channels) > 0) {
               $query = "SELECT users.user_name, channel_messages.message_id, channels.channel_name, ".
                        "channel_messages.message_text, ".
                        "DATE_FORMAT(channel_messages.message_date, '%T') as message_date ".
                        "FROM channels, channel_messages, users ".
                        "WHERE users.user_id=channel_messages.user_id AND channel_messages.message_id > '$id' AND ".
                        "channels.channel_id=channel_messages.channel_id";
               $channel_query = " AND (";
               foreach($channels as $channel) {
                   if($channel_query != " AND (") {
                       $channel_query .= " OR ";
                   }
                   $channel_query .= "channel_messages.channel_id='$channel'";
               }
               $channel_query .= ")";
               $xml = "<?xml version='1.0' encoding='UTF-8'?>\n";
               $xml .= "<messages>\n";
               $query = $query . $channel_query . " ORDER BY channel_messages.message_date";
               if($rows = mysqli_get_many($query)) {
                   foreach($rows as $row) {
                       $channel_name = $row['channel_name'];
                       $channel_name = strtoupper(substr($channel_name, 0, 1)) . 
                                       substr($channel_name, 1, strpos($channel_name, "_") - 1);
                       $channel_name = $channel_images[$channel_name];
                       $message_text = stripslashes(htmlentities($row['message_text']));
                       $message_date = $row['message_date'];
                       $message_id = $row['message_id'];
                       $user_name = $row['user_name'];
                       if($wants_array) {
                           $return_array[$message_id] = array('user'=>$user_name,
                                                              'date'=>$message_date,
                                                              'text'=>$message_text,
                                                              'channel'=>$channel_name);
                       } else {
                           $xml .= "<message>\n";
                           $xml .= "<id>$message_id</id>\n";
                           $xml .= "<user>$user_name</user>\n";
                           $xml .= "<date>$message_date</date>\n";
                           $xml .= "<text>$message_text</text>\n";
                           $xml .= "<channel>$channel_name</channel>\n";
                           $xml .= "</message>\n";
                       }
                   }
               }
           } else {
               //Non-player, show them game only
           }
           $xml .= "</messages>\n";
           if($wants_array) {
               return $return_array;
           } else {
                return "$xml";
           }
       //}
   }

?>
