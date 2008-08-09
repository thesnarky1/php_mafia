<?php

    include('./includes/functions.php');

    if(!is_logged_in()) {
        render_header("Thieves Tavern Account");
        echo "<p class='error'>Please <a href='./login.php'>login</a> or <a href='./register.php'>register</a> to mess with your account.</p>\n";
        render_footer();
        die();
    }

    $error = "";
    
    if(isset($_POST['type'])) {
        $type = pass_replace($_POST['type']);
        if($type == "password") {
            if(isset($_POST['password1']) && isset($_POST['password2'])) {
                if($_POST['password1'] == $_POST['password2']) {
                    $pass = pass_replace($_POST['password1']);
                    if(pass_check($pass)) {
                        $user_id = $_SESSION['user_id'];
                        //Update password
                        $query = "UPDATE users ".
                                 "SET user_pass=MD5('$pass') ".
                                 "WHERE user_id='$user_id' ";
                        $result = mysqli_query($dbh, $query);
                        if(mysqli_affected_rows($dbh) == 1) {
                            //Success
                        } else {
                            $error = "Unknown error, please try again.";
                        }
                    } else {
                        $error = "Currently passwords can only contain alphanumeric characters.";
                    }
                } else {
                    $error = "Passwords don't match.";
                }
            } else {
                $error = "Please fill in both password fields.";
            }
        }
    }

    render_header("Thieves Tavern Account");
    echo "<div class='center'>\n";
    if($error != "") {
        echo "<p class='error'>$error</p>\n";
    }
    echo "<form id='account_password' action='./account.php' method='POST'>\n";
    echo "Password: ";
    echo "<input type='text' name='password1' />\n";
    echo "<br />\n";
    echo "Re-typed: ";
    echo "<input type='text' name='password2' />\n";
    echo "<br />\n";
    echo "<input type='hidden' name='type' value='password' />\n";
    echo "<input type='submit' name='submit' value='Update Password' />\n";
    echo "</form>\n";
    echo "<form id='account_details' action='./account.php' method='POST'>\n";
    echo "<input type='hidden' name='type' value='details' />\n";
    echo "<input type='submit' name='submit' value='Update Details' />\n";
    echo "</form>\n";
    echo "</div>\n";
    render_footer();

?>
