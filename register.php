<?php

    include('./includes/functions.php');


    if(isset($_REQUEST['email']) && isset($_REQUEST['code'])) {
        $error = "";
        $blocking_error = false;
        $email = safetify_input($_REQUEST['email']);
        $code = safetify_input($_REQUEST['code']);

        if(isset($_REQUEST['register'])) {
            //Check email/registration code again... TRUST NO ONE!
            $query = "SELECT reg_id FROM registration_codes ".
                     "WHERE user_email='$email' AND reg_code='$code'";
            if($row = mysqli_get_one($query)) {
                $reg_id = $row['reg_id'];
                if(isset($_REQUEST['username'])) {
                    $username = safetify_input($_REQUEST['username']);
                    if(username_check($username)) {
                        $query = "SELECT user_id FROM users WHERE user_name='$username'";
                        $rows = mysqli_get_many($query);
                        if(count($rows) == 0) {
                            if(isset($_REQUEST['pass']) && isset($_REQUEST['pass2'])) {
                                $pass = safetify_input($_REQUEST['pass']);
                                $pass2 = safetify_input($_REQUEST['pass2']);
                                if($pass == $pass2) {
                                    if(pass_check($pass)) {
                                        $query = "SELECT user_id FROM users WHERE user_email='$email'";
                                        $rows = mysqli_get_many($query);
                                        if(count($rows) == 0) {
                                            //Good username / pass, lets register
                                            $hash = create_user_hash();
                                            $query = "INSERT INTO users(user_name, ".
                                                     "user_email, user_pass, user_hash, user_joined)".
                                                     "VALUES('$username', '$email', MD5('$pass'), '$hash', NOW())";
                                            if($user_id = mysqli_insert($query)) {
                                                $user_id = mysqli_insert_id($dbh);
                                                login_user($username, $user_id, $hash);
                                                //Remove registration code
                                                $query = "DELETE FROM registration_codes WHERE reg_id='$reg_id'";
                                                if(mysqli_delte($query)) {
                                                    $error = "Registration complete, you are now logged in.";
                                                    $blocking_error = true;
                                                } else {
                                                    $error = "Error deleting registration code, but registration complete.";
                                                    $blocking_error = true;
                                                }
                                            } else {
                                                $error = "Error inserting new user.";
                                            }
                                        } else {
                                            $error = "That username is already taken, sorry.";
                                        }
                                    } else {
                                        $error = "Sorry, password must be at least 6 characters with no funny business.";
                                    }
                                } else {
                                    $error = "Passwords must match.";
                                }
                            } else {
                                $error = "Password must be typed in twice.";
                            }
                        } else {
                            $error = "Username already in use.";
                        }
                    } else {
                        $error = "Username needs to be at least 5 alphanumeric characters.";
                    }
                } else {
                    $error = "Username must be specified";
                }
            } else {
                $error = "Bad email/registration code combo.";
                $blocking_error = true;
            }
        } else {
            //Check if email is already registered
            $query = "SELECT user_id FROM users WHERE user_email='$email'";
            if(mysqli_get_one($query)) {
                $error = "Sorry, this email address is already registered.";
                $blocking_error = true;
            } else {
                //Check if registration code is correct
                $query = "SELECT reg_code FROM registration_codes WHERE user_email='$email'";
                if($row = mysqli_get_one($query)) {
                    $reg_code = $row['reg_code'];
                    if($reg_code != $code) {
                        $error = "Sorry, this registration code is incorrect.";
                    } else {
                    }
                } else {
                    $error = "Sorry, this email does not have a registration code.";
                }
            }
        }

        render_header("Thieves Tavern Register");

        if($error != "") {
            echo "<p class='error'>$error</p>\n";
        }

        if(!$blocking_error) {
            //Show register form
            echo "<div id='registration_div'>\n";
            echo "<h3>Account Registration</h3>\n";
            echo "<form id='registration_form' method='POST' action='register.php'>\n";
            echo "<label class='fixed_width'>Username: </label>";
            echo "<input type='text' name='username' ";
            if(isset($_REQUEST['username'])) {
                $username = safetify_input($_REQUEST['username']);
                echo "value='$username' ";
            }
            echo ">\n";
            echo "<br />\n";
            echo "<label class='fixed_width'>Password: </label>";
            echo "<input type='password' name='pass'>\n";
            echo "<br />\n";
            echo "<label class='fixed_width'>Password: </label>";
            echo "<input type='password' name='pass2'>\n";
            echo "<br />\n";
            echo "<input type='hidden' name='register' value='true'>\n";
            echo "<input type='hidden' name='email' value='$email'>\n";
            echo "<input type='hidden' name='code' value='$code'>\n";
            echo "<input type='submit' value='Register' style='margin-top: .25em;'>\n";
            echo "</form>\n";
            echo "</div>\n";
        }
    } else {
        render_header("Thieves Tavern Register");
        echo "<p class='error'>Sorry, registration is currently limited to invite only. ".
             "If you received an invitation, please use the link provided by email.</p>\n";
    }

    render_footer();

?>
