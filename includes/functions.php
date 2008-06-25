<?php
    session_start();
    $phases = array('Setup', 'Night', 'Day', 'Finished');
    $channel_images = array('Doctor'=>'doctor.gif',
                            'Game'=>'doctor.gif',
                            'Silent_killer'=>'silent_killer.gif',
                            'Cop'=>'cop.png',
                            'Mafia'=>'mafia.gif');
    include('./includes/mysql_config.php');
    include('./includes/game_functions.php');
    include('./includes/site_functions.php');
    include('./includes/chat_functions.php');
?>
