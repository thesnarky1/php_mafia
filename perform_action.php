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
                echo "Valid action";
            } else {
                echo "Faker!";
                print_r($valid_actions);
                print_r($_REQUEST);
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


