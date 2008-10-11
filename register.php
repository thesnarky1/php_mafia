<?php

    include('./includes/functions.php');

    render_header("Thieves Tavern Register");

    if(isset($_GET['email']) && isset($_GET['code'])) {
        if(isset($_GET['register'])) {
            //We're registering
        } else {

            $email = safetify_input($_GET['email']);
            $code = safetify_input($_GET['code']);

            //Check if email is already registered
            $query = "SELECT user_id FROM users WHERE user_email='$email'";
            $result = mysqli_query($dbh, $query);
            if($result) {
                if(mysqli_num_rows($result) == 1) {
                    $error = "Sorry, this email address is already registered.";
                }
            } else {
                //Check if registration code is correct
                $query = "SELECT reg_code FROM registration_codes WHERE user_email='$email'";
                $result = mysqli_query($dbh, $query);
                if($result && mysqli_num_rows($result) == 1) {
                    $row = mysqli_fetch_array($result);
                    $reg_code = $row['reg_code'];
                    if($reg_code != $code) {
                        $error = "Sorry, this registration code is incorrect.";
                    } else {
                    }
                } else {
                    $error = "Sorry, this email does not have a registration code.";
                }
            }

            if($error == "") {
                //Show register form
                echo "<div id='registration_div'>\n";
                echo "<h3>Account Registration</h3>\n";
                echo "<form id='registration_form'>\n";
                echo "<label class='fixed_width'>Username: </label>";
                echo "<input type='text' name='username'>\n";
                echo "<br />\n";
                echo "<label class='fixed_width'>Password: </label>";
                echo "<input type='password' name='pass'>\n";
                echo "<br />\n";
                echo "<label class='fixed_width'>Password: </label>";
                echo "<input type='password' name='pass2'>\n";
                echo "<br />\n";
                echo "<input type='submit' value='Register' style='margin-top: .25em;'>\n";
                echo "</form>\n";
                echo "</div>\n";
            } else {
                echo "<p class='error'>$error</p>\n";
            }
        }
    } else {
        echo "<p class='error'>Sorry, registration is currently limited to invite only. ".
             "If you received an invitation, please use the link provided by email.</p>\n";
    }

    render_footer();

?>
