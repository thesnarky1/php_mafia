<?php

    include('./includes/functions.php');

    if(isset($_REQUEST['game_id']) && 
       isset($_REQUEST['user_id']) &&
       isset($_REQUEST['game_tracker']) &&
       isset($_REQUEST['user_hash'])) {
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-Type: text/xml');
        $game_id = safetify_input($_REQUEST['game_id']);
        $game_tracker = safetify_input($_REQUEST['game_tracker']);
        $user_id = safetify_input($_REQUEST['user_id']);
        $user_hash = safetify_input($_REQUEST['user_hash']);
        $query = "SELECT user_id FROM users WHERE user_id='$user_id' AND user_hash='$user_hash'";
        $result = mysqli_query($dbh, $query);
        if($result && mysqli_num_rows($result) == 1) {
            echo get_game_information($game_id, $game_tracker, $user_id);
        } else {
            echo get_game_information($game_id, $game_tracker);
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
