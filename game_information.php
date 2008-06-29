<?php

    include('./includes/functions.php');

    if(isset($_GET['game_id']) && 
       isset($_GET['game_phase']) && 
       isset($_GET['game_turn']) && 
       isset($_GET['user_id']) &&
       isset($_GET['user_hash'])) {
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-Type: text/xml');
        $game_id = safetify_input($_GET['game_id']);
        $game_turn = safetify_input($_GET['game_turn']);
        $game_phase = safetify_input($_GET['game_phase']);
        $user_id = safetify_input($_GET['user_id']);
        $user_hash = safetify_input($_GET['user_hash']);
        $query = "SELECT user_id FROM users WHERE user_id='$user_id' AND user_hash='$user_hash'";
        $result = mysqli_query($dbh, $query);
        if($result && mysqli_num_rows($result) == 1) {
            echo get_game_information($game_id, $game_turn, $game_phase, $user_id);
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
