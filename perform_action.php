<?php

    include('./includes/functions.php');

    if(isset($_REQUEST['game_id']) && isset($_REQUEST['user_id']) &&
       isset($_REQUEST['action_id']) && isset($_REQUEST['target_id']) &&
       isset($_REQUEST['user_hash'])) {
        
        echo "Works";
    } else {
        print_r($_REQUEST);
    }


?>


