<?php

    include('./includes/functions.php');
    if(is_logged_in()) {
        logout_user();
    } else {
        $error = "Not logged in.";
    }

    render_header("Thieves Tavern Logout");
    if($error != "") {
        echo "<p class='error'>$error</p>\n";
    } else {
        echo "<p class='center'>Successfully logged out.</p>\n";
    }

    render_footer();

?>
