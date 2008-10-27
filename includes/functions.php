<?php
    session_start();
    $phases = array('Setup', 'Night', 'Day', 'Finished');
    $channel_images = array('Doctor'=>'doctor.gif',
                            'Game'=>'game.png',
                            'Silent_killer'=>'silent_killer.gif',
                            'Cop'=>'cop.png',
                            'System'=>'system.png',
                            'Unassigned'=>'unassigned.png',
                            'Mason'=>'mason.png',
                            'Mafia'=>'mafia.gif');
    $game_name_limit = 40;
    include('./includes/mysql_config.php');
    include('./includes/game_functions.php');
    include('./includes/site_functions.php');
    include('./includes/chat_functions.php');
    include('./includes/profile_functions.php');
    include('./includes/xml_functions.php');
    include('./includes/mysqli_functions.php');
?>
