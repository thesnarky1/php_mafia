<?php

    include('./includes/functions.php');

    if(isset($_GET['game_id']) && 
       isset($_GET['game_phase']) && 
       isset($_GET['game_turn'])) {
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-Type: text/xml');
        $game_id = safetify_input($_GET['game_id']);
        $game_turn = safetify_input($_GET['game_turn']);
        $game_phase = safetify_input($_GET['game_phase']);
        echo get_game_information($game_id, $game_turn, $game_phase);
    } else {
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-Type: text/xml');
        echo "<?xml version='1.0' encoding='UTF-8'?>\n";
        echo "<game_date></game_data>\n";
    }

?>
