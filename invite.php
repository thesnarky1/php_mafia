<?php

    include('./includes/functions.php');

    render_header("Thieves Tavern Invite");

    //Check if we have someone that should be viewing the page
    $belongs = false;
    if(!is_logged_in()) {
    } else {
        $user_id = $_SESSION['user_id'];
        $query = "SELECT user_role FROM users WHERE user_id='$user_id'";
        $result = mysqli_query($dbh, $query);
        if($result && mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_array($result);
            $user_role = $row['user_role'];
            if($user_role == "A") { //Admin - good to go
                $belongs = true;
            } else { //Everyone else - posers...
            }
        }
    }

    if(!$belongs) {
        echo "<p class='error'>Sorry, invitations are currently restricted to admins only.</p>\n";
        render_footer();
        die();
    }

    if(isset($_POST['email'])) {
        $email = safetify_input($_POST['email']);
        $query = "SELECT user_email ".
                 "FROM users ".
                 "WHERE user_email='$email'";
        $result = mysqli_query($dbh, $query);
        if($result) {
            if(mysqli_num_rows($result) == 0) {
                $query = "SELECT user_email FROM registration_codes WHERE user_email='$email'";
                $result = mysqli_query($dbh, $query);
                if($result) {
                    if(mysqli_num_rows($result) == 0) {
                        $reg_code = create_user_hash();
                        $query = "INSERT INTO registration_codes(user_email, reg_code) ".
                                 "VALUES('$email', '$reg_code')";
                        $result = mysqli_query($dbh, $query);
                        if($result && mysqli_affected_rows($dbh) == 1) {
                            $user = $_SESSION['user_name'];
                            if(send_invite_email($email, $reg_code, $user)) {
                                $error = "Invitation sent successfully to $email";
                            } else {
                                $error = "Registration code created, but email failed to send, ";
                                $query = "DELETE FROM registration_codes WHERE reg_code='$reg_code' AND user_email='$email'";
                                $result = mysqli_query($dbh, $query);
                                if(mysqli_affected_rows($dbh) == 1) {
                                    $error .= "registration code deleted.";
                                } else {
                                    $error .= "registration code failed to delete - $query";
                                }
                            }
                        } else {
                            $error = "DB error - $query";
                        }
                    } else {
                        $error = "Email address already has an invitation sent.";
                    }
                } else {
                    $error = "DB error - $query";
                }
            } else {
                $error = "Email address already registered.";
            }
        } else {
            $error = "DB error - $query";
        }
    }


    if($error != "") {
        echo "<p class='error'>$error</p>\n";
    }

    echo "<div id='invite_div'>\n";
    echo "<h3>Invite a Player</h3>\n";
    echo "<form id='invite_form' method='POST' action='invite.php'>\n";
    echo "<label class='fixed_width'>Email address: </label>";
    echo "<input type='text' name='email'>\n";
    echo "<br />\n";
    echo "<input type='submit' value='Send Invite' style='margin-top: .25em;'>\n";
    echo "</form>\n";
    echo "</div>\n";

    render_footer();

?>
