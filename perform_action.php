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
        $game_id = safetify_input($_REQUEST['game_id']);
        $user_id = safetify_input($_REQUEST['user_id']);
        $user_hash = safetify_input($_REQUEST['user_hash']);
        $action_id = safetify_input($_REQUEST['action_id']);
        $target_id = safetify_input($_REQUEST['target_id']);
        if(valid_user($user_id, $user_hash)) {
            $valid_actions = get_user_actions($user_id, $game_id);
            if(in_array($action_id, $valid_actions)) {
                $action_enum = get_action_by_id($action_id);
                if($action_enum) {
                    //Big ol' switch, I'm thinking
                    switch($action_enum) {
                        case "KILL":
                            echo "You mark " . get_user_name($target_id) . " for death.";
                            add_player_action($game_id, $user_id, $action_id, $target_id);
                            set_player_ready($game_id, $user_id, true);
                            update_player_needs_update($game_id, $user_id, true);
                            break;
                        case "NO_ACTION":
                            echo "You cannot do anything at this juncture.";
                            break;
                        case "READY":
                            set_player_ready($game_id, $user_id, true);
                            update_player_needs_update($game_id, $user_id, true);
                            echo "You declare that you're ready.";
                            break;
                        case "START":
                            if(can_start_game($game_id)) {
                                start_game($game_id);
                                echo "You start the game.";
                            } else {
                            }
                            break;
                        case "UN_READY":
                            clear_player_action($game_id, $user_id);
                            set_player_ready($game_id, $user_id, false);
                            update_player_needs_update($game_id, $user_id, true);
                            echo "You remove all choices and sit, unprepared.";
                            break;
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


