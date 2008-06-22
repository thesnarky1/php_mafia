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
            echo retrieve_new_messages($user_id, $game_id, $id);
        } else {
            print_r($_GET);
        }
    }
?>
