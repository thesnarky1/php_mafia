<?php
    session_start();
    $phases = array('Setup', 'Night', 'Day', 'Finished');
    include('./includes/mysql_config.php');
    include('./includes/game_functions.php');
    include('./includes/site_functions.php');
?>
