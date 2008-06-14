<?php

    include('./includes/functions.php');
    if(isset($_SESSION['user_name']) && isset($_SESSION['user_id'])) {
        $_SESSION['user_name'] = "";
        $_SESSION['user_id'] = "";
        session_destroy();
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
