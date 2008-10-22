<?php

    include('./includes/functions.php');

    if(isset($_REQUEST['game_id']) && isset($_REQUEST['user_id']) &&
       isset($_REQUEST['action_id']) && isset($_REQUEST['target_id']) &&
       isset($_REQUEST['user_hash'])) {
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-Type: text/xml');
        echo "<?xml version='1.0' encoding='UTF-8'?>\n";
        $game_id = safetify_input($_REQUEST['game_id']);
        $user_id = safetify_input($_REQUEST['user_id']);
        $user_hash = safetify_input($_REQUEST['user_hash']);
        $action_id = safetify_input($_REQUEST['action_id']);
        $target_id = safetify_input($_REQUEST['target_id']);
        if(valid_user($user_id, $user_hash)) {
            if(is_game_locked($game_id)) {
                echo "<action_data>";
                echo "<error>Game is currently locked. Either changing turns or has ended.</error>\n";
                echo "</action_data>\n";
                die();
            }
            $query = "SELECT game_phase FROM games WHERE game_id='$game_id'";
            $result = mysqli_query($dbh, $query);
            if($result && mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_array($result);
                $game_phase = $row['game_phase'];
            }
            $valid_actions = get_user_actions($game_id, $user_id);
            if(in_array($action_id, $valid_actions)) {
                $priority = get_action_priority($game_id, $user_id);
                $action_enum = get_action_by_id($action_id);
                if($action_enum) {
                    //Big ol' switch, I'm thinking
                    switch($action_enum) {
                        case "INVESTIGATE":
                            echo "You ask around about " . get_user_name($target_id) . ".";
                            add_player_action($game_id, $user_id, $action_id, $target_id);
                            set_player_ready($game_id, $user_id, true);
                            update_player_needs_update($game_id, $user_id, true);
                            break;
                        case "KILL":
                            echo "You mark " . get_user_name($target_id) . " for death.";
                            add_player_action($game_id, $user_id, $action_id, $target_id, $priority);
                            set_player_ready($game_id, $user_id, true);
                            update_player_needs_update($game_id, $user_id, true);
                            break;
                        case "LYNCH":
                            echo "You publically declare that " . get_user_name($target_id) . " should be brought to trial.";
                            add_player_action($game_id, $user_id, $action_id, $target_id);
                            set_player_ready($game_id, $user_id, true);
                            add_message(get_system_channel($game_id),
                                        get_system_id(),
                                        get_user_name($user_id) . " wants to lynch " . get_user_name($target_id) . ".");
                            update_game_players($game_id); //We always want a lynch vote to refresh ALL pages.
                            update_game_tracker($game_id);
                            break;
                        case "NO_ACTION":
                            echo "You cannot do anything at this juncture.";
                            break;
                        case "NO_INVESTIGATE":
                            echo "Going off a hunch, you decide not to look into anyone's life.";
                            add_player_action($game_id, $user_id, $action_id, $target_id);
                            set_player_ready($game_id, $user_id, true);
                            update_player_needs_update($game_id, $user_id, true);
                            break;
                        case "NO_LYNCH":
                            echo "You have a change of heart and decide no one should be lynched.";
                            add_player_action($game_id, $user_id, $action_id, $target_id);
                            set_player_ready($game_id, $user_id, true);
                            add_message(get_system_channel($game_id),
                                        get_system_id(),
                                        get_user_name($user_id) . " wants to lynch no one.");
                            update_game_players($game_id); //We always want a lynch vote to refresh ALL pages.
                            update_game_tracker($game_id);
                            break;
                        case "NO_KILL":
                            echo "At the last second you decide that killing is wrong, and elect to let everyone live.";
                            add_player_action($game_id, $user_id, $action_id, $target_id);
                            set_player_ready($game_id, $user_id, true);
                            update_player_needs_update($game_id, $user_id, true);
                            break;
                        case "NO_SAVE":
                            echo "Remembering that this town skips out on the bill, you elect to help no one.";
                            add_player_action($game_id, $user_id, $action_id, $target_id);
                            set_player_ready($game_id, $user_id, true);
                            update_player_needs_update($game_id, $user_id, true);
                            break;
                        case "READY":
                            set_player_ready($game_id, $user_id, true);
                            update_player_needs_update($game_id, $user_id, true);
                            echo "You declare that you're ready.";
                            if($game_phase == 0) {
                                add_message(get_channel_by_name("unassigned_" . $game_id, $game_id),
                                            get_system_id(),
                                            get_user_name($user_id) . " is ready to go.");
                            }
                            break;
                        case "SAVE":
                            if($target_id != $user_id) {
                                echo "You run off to help " . get_user_name($target_id) . " in their illness.";
                                add_player_action($game_id, $user_id, $action_id, $target_id);
                                set_player_ready($game_id, $user_id, true);
                                update_player_needs_update($game_id, $user_id, true);
                            } else {
                                echo "Now you're just being selfish! We don't allow that!";
                            }
                            break;
                        case "START":
                            if(can_start_game($game_id)) {
                                add_message(get_channel_by_name("unassigned_" . $game_id, $game_id),
                                            get_system_id(),
                                            get_user_name($user_id) . " starts the game. Good luck!");
                                start_game($game_id);
                                echo "You start the game.";
                                die();
                            } else {
                            }
                            break;
                        case "UN_READY":
                            clear_player_action($game_id, $user_id);
                            set_player_ready($game_id, $user_id, false);
                            update_player_needs_update($game_id, $user_id, true);
                            echo "You remove all choices and sit, unprepared.";
                            if($game_phase == 0) {
                                add_message(get_channel_by_name("unassigned_" . $game_id, $game_id),
                                            get_system_id(),
                                            get_user_name($user_id) . " wants to hold everyone up, and unreadies.");
                            }
                            break;
                    }
                    if(can_phase_change($game_id)) {
                        lock_game($game_id, true);
                        carry_out_actions($game_id);
                        next_phase($game_id);
                        lock_game($game_id, false);
                    } else {
                    }
                } else {
                    echo "Faker!!!";
                }
            } else {
                echo "Faker!";
            }
        } else {
        }
    } else {
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-Type: text/xml');
        echo "<?xml version='1.0' encoding='UTF-8'?>\n";
        echo "<action_data></action_data>\n";
    }


?>


