<?php

    include('./includes/functions.php');

    $mode = $_GET['mode'];
    if($mode == 'RetrieveNew') {
        if(isset($_GET['id']) && isset($_GET['user_id']) && 
            isset($_GET['user_hash']) && isset($_GET['game_id'])) {
            $id = $_GET['id'];
            $user_id = $_GET['user_id'];
            $user_hash = $_GET['user_hash'];
            $game_id = $_GET['game_id'];
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            header('Content-Type: text/xml');
            $query = "SELECT user_id FROM users WHERE user_id='$user_id' AND user_hash='$user_hash'";
            $result = mysqli_query($dbh, $query);
            if($result && mysqli_num_rows($result) == 1) {
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
            $id = $_GET['id'];
            $user_id = $_GET['user_id'];
            $user_hash = $_GET['user_hash'];
            $game_id = $_GET['game_id'];
            $message = safetify_input($_GET['message']);
            $query = "SELECT user_id FROM users WHERE user_id='$user_id' AND user_hash='$user_hash'";
            $result = mysqli_query($dbh, $query);
            if($result && mysqli_num_rows($result) == 1) {
                //Get phase
                $query = "SELECT game_phase FROM games WHERE game_id='$game_id'";
                $result = mysqli_query($dbh, $query);
                $phase = false;
                $channel = false;
                if($result && mysqli_num_rows($result) == 1) {
                    $row = mysqli_fetch_array($result);
                    $phase = $row['game_phase'];
                }
                //Get channel
                if($phase) {
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
                            }
                        } else {
                            $error = "You currently can't talk on any channels. Wait for daylight and try again.";
                        }
                    } else {
                        $error = "You currently can't talk on any channels.";
                    }
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
            }
        }
    }
?>
