<?php

    include('./includes/functions.php');

    if(isset($_REQUEST['game_id']) && 
       isset($_REQUEST['user_id']) &&
       isset($_REQUEST['game_tracker']) &&
       isset($_REQUEST['user_hash'])) {
        $force = false;
        if(isset($_REQUEST['force'])) {
            $force = true;
        }
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-Type: text/xml');
        $game_id = safetify_input($_REQUEST['game_id']);
        $game_tracker = safetify_input($_REQUEST['game_tracker']);
        $user_id = safetify_input($_REQUEST['user_id']);
        $user_hash = safetify_input($_REQUEST['user_hash']);
        if(valid_user($user_id, $user_hash)) {
            echo get_game_information($game_id, $game_tracker, $force, $user_id);
        } else {
            echo get_game_information($game_id, $game_tracker, $force);
        }
    } else {
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-Type: text/xml');
        echo "<?xml version='1.0' encoding='UTF-8'?>\n";
        echo "<game_data></game_data>\n";
    }

?>
