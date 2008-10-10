<?php

    include('./includes/functions.php');

    render_header("Thieves Tavern Register");

    if(isset($_GET['email']) && isset($_GET['code'])) {
        if(isset($_GET['register'])) {
            //We're registering
        } else {
            //Show register form
            echo "<form id='registration_form'>\n";
            echo "<input type='submit' value='Register'>\n";
            echo "</form>\n";
        }
    } else {
        echo "<p class='error'>Sorry, registration is currently limited to invite only. ".
             "If you received an invitation, please use the link provided by email.</p>\n";
    }

    render_footer();

?>
