<?php

    include('./includes/functions.php');

    $mode = $_GET['mode'];
    if($mode == 'RetrieveNew') {
        if(isset($_GET['id']) && isset($_GET['user_id']) && 
            isset($_GET['user_hash']) && isset($_GET['game_id'])) {
            $id = safetify_input($_GET['id']);
            $user_id = safetify_input($_GET['user_id']);
            $user_hash = safetify_input($_GET['user_hash']);
            $game_id = safetify_input($_GET['game_id']);
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            header('Content-Type: text/xml');
            if(valid_user($user_id, $user_hash)) {
                echo retrieve_new_messages($user_id, $game_id, $id);
            } else {
                echo retrieve_new_messages('', $game_id, $id);
            }
        }
    }
    if($mode == 'SendAndRetrieveNew') {
        if(isset($_GET['id']) && isset($_GET['user_id']) && 
            isset($_GET['user_hash']) && isset($_GET['game_id']) &&
            isset($_GET['message'])) {
            $id = safetify_input($_GET['id']);
            $user_id = safetify_input($_GET['user_id']);
            $user_hash = safetify_input($_GET['user_hash']);
            $game_id = safetify_input($_GET['game_id']);
            $message = safetify_input($_GET['message']);
            $query = "SELECT users.user_id, game_players.player_alive ".
                     "FROM users, game_players ".
                     "WHERE users.user_id='$user_id' AND users.user_hash='$user_hash' AND ".
                     "game_players.user_id=users.user_id AND game_players.game_id='$game_id'";
            $result = mysqli_query($dbh, $query);
            if($result && mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_array($result);
                $player_alive = $row['player_alive'];
                if($player_alive == 'Y') {
                    //Get phase
                    $query = "SELECT game_phase FROM games WHERE game_id='$game_id'";
                    $result = mysqli_query($dbh, $query);
                    $phase = false;
                    $channel = false;
                    if($result && mysqli_num_rows($result) == 1) {
                        $row = mysqli_fetch_array($result);
                        $phase = $row['game_phase'];
                        //Check user and game
                        $query = "SELECT channels.channel_id, channels.channel_name, ".
                                 "channel_members.channel_post_rights ".
                                 "FROM channels, channel_members, users ".
                                 "WHERE users.user_hash='$user_hash' AND ".
                                 "users.user_id=channel_members.user_id AND ".
                                 "channel_members.user_id='$user_id' AND ".
                                 "channel_members.channel_id=channels.channel_id AND ".
                                 "channels.game_id='$game_id' AND ".
                                 "channel_members.channel_post_rights='1'";
                        $result = mysqli_query($dbh, $query);
                        if($result && mysqli_num_rows($result) >= 1) {
                            while($row = mysqli_fetch_array($result)) {
                                $channel_name = $row['channel_name'];
                                $channel_id = $row['channel_id'];
                                $channel_post_rights = $row['channel_post_rights'];
                                if($phase == 2) { //Day time
                                    if(substr($channel_name, 0, 4) == "game") {
                                        $channel = $channel_id;
                                    }
                                } else {
                                    if(substr($channel_name, 0, 4) != "game") {
                                        $channel = $channel_id;
                                    }
                                }
                            }
                            //Post message
                            if($channel) {
                                $query = "INSERT INTO channel_messages(channel_id, user_id, message_text, message_date) ".
                                         "VALUES('$channel', '$user_id', '$message', NOW())";
                                $result = mysqli_query($dbh, $query);
                                if($result && mysqli_affected_rows($dbh) > 0) {
                                } else {
                                    $error = "Unknown issue adding message into system.";
                                }
                            } else {
                                $error = "You currently can't talk on any channels. Wait for daylight and try again.";
                            }
                        } else {
                            $error = "You currently can't talk on any channels. $query";
                        }
                    }
                } else {
                    $error = "Dead men tell no tales!";
                }
            } else {
                $error = "You're not a part of this game.";
            }
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            header('Content-Type: text/xml');
            if($error == "") {
                echo retrieve_new_messages($user_id, $game_id, $id);
            } else {
                echo show_chat_error($error);
            }
        } else {
            echo show_chat_error(print_r($_GET));
        }
    }
?>
