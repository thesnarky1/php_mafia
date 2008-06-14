<?php

    include('./includes/functions.php');
    session_start();
    session_destroy();

    render_header("Thieves Tavern Logout");

    echo "<p style='center'>Successfully logged out</p>\n";

    render_footer();

?>
