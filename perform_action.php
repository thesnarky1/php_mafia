<?php

    include('./includes/functions.php');

    if(isset($_REQUEST['game_id']) && isset($_REQUEST['user_id']) &&
       isset($_REQUEST['action_id']) && isset($_REQUEST['target_id']) &&
       isset($_REQUEST['user_hash'])) {
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
            if($row = mysqli_get_one($query)) {
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
                            add_player_action($game_id, $user_id, $action_id, $target_id);
                            set_player_ready($game_id, $user_id, true);
                            update_player_needs_update($game_id, $user_id, true);
                            build_action_xml("You ask around about " . get_user_name($target_id) . ".");
                            break;
                        case "KILL":
                            add_player_action($game_id, $user_id, $action_id, $target_id, $priority);
                            set_player_ready($game_id, $user_id, true);
                            update_player_needs_update($game_id, $user_id, true);
                            build_action_xml("You mark " . get_user_name($target_id) . " for death.");
                            break;
                        case "LYNCH":
                            add_player_action($game_id, $user_id, $action_id, $target_id);
                            set_player_ready($game_id, $user_id, true);
                            add_message(get_system_channel($game_id),
                                        get_system_id(),
                                        get_user_name($user_id) . " wants to lynch " . get_user_name($target_id) . ".");
                            update_game_players($game_id); //We always want a lynch vote to refresh ALL pages.
                            update_game_tracker($game_id);
                            build_action_xml("You publically declare that " . get_user_name($target_id) . " should be brought to trial.");
                            break;
                        case "NO_ACTION":
                            build_action_xml("You cannot do anything at this juncture.");
                            break;
                        case "NO_INVESTIGATE":
                            add_player_action($game_id, $user_id, $action_id, $target_id);
                            set_player_ready($game_id, $user_id, true);
                            update_player_needs_update($game_id, $user_id, true);
                            build_action_xml("Going off a hunch, you decide not to look into anyone's life.");
                            break;
                        case "NO_LYNCH":
                            add_player_action($game_id, $user_id, $action_id, $target_id);
                            set_player_ready($game_id, $user_id, true);
                            add_message(get_system_channel($game_id),
                                        get_system_id(),
                                        get_user_name($user_id) . " wants to lynch no one.");
                            update_game_players($game_id); //We always want a lynch vote to refresh ALL pages.
                            update_game_tracker($game_id);
                            build_action_xml("You have a change of heart and decide no one should be lynched.");
                            break;
                        case "NO_KILL":
                            add_player_action($game_id, $user_id, $action_id, $target_id);
                            set_player_ready($game_id, $user_id, true);
                            update_player_needs_update($game_id, $user_id, true);
                            build_action_xml("At the last second you decide that killing is wrong, and elect to let everyone live.");
                            break;
                        case "NO_SAVE":
                            add_player_action($game_id, $user_id, $action_id, $target_id);
                            set_player_ready($game_id, $user_id, true);
                            update_player_needs_update($game_id, $user_id, true);
                            build_action_xml("Remembering that this town skips out on the bill, you elect to help no one.");
                            break;
                        case "READY":
                            set_player_ready($game_id, $user_id, true);
                            build_action_xml("You declare that you're ready.");
                            if($game_phase == 0) {
                                add_message(get_channel_by_name("unassigned_" . $game_id, $game_id),
                                            get_system_id(),
                                            get_user_name($user_id) . " is ready to go.");
                            }
                            update_player_needs_update($game_id, $user_id, true);
                            update_game_tracker($game_id);
                            break;
                        case "SAVE":
                            if($target_id != $user_id) {
                                add_player_action($game_id, $user_id, $action_id, $target_id);
                                set_player_ready($game_id, $user_id, true);
                                update_player_needs_update($game_id, $user_id, true);
                                build_action_xml("You run off to help " . get_user_name($target_id) . " in their illness.");
                            } else {
                                build_action_xml("Now you're just being selfish! We don't allow that!");
                            }
                            break;
                        case "START":
                            if(can_start_game($game_id)) {
                                add_message(get_channel_by_name("unassigned_" . $game_id, $game_id),
                                            get_system_id(),
                                            get_user_name($user_id) . " starts the game. Good luck!");
                                start_game($game_id);
                                build_action_xml("You start the game.");
                                die();
                            } else {
                            }
                            break;
                        case "UN_READY":
                            clear_player_action($game_id, $user_id);
                            set_player_ready($game_id, $user_id, false);
                            build_action_xml("You remove all choices and sit, unprepared.");
                            if($game_phase == 0) {
                                add_message(get_channel_by_name("unassigned_" . $game_id, $game_id),
                                            get_system_id(),
                                            get_user_name($user_id) . " wants to hold everyone up, and unreadies.");
                                update_game_tracker($game_id);
                                update_game_players($game_id);
                            } else {
                                update_player_needs_update($game_id, $user_id, true);
                            }
                            break;
                    }
                    if(can_phase_change($game_id)) {
                        lock_game($game_id, true);
                        carry_out_actions($game_id);
                        next_phase($game_id);
                        lock_game($game_id, false);
                    }
                } else {
                   build_action_aml("Faker!!!");
                }
            } else {
                build_action_xml("Faker!");
            }
        } else {
        }
    } else {
        build_action_xml('');
    }


?>


