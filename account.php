<?php

    include('./includes/functions.php');

    $allowed_exts = array("jpg", "jpeg", "png", "gif", "bmp");
    $max_size = "10000";

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
                        $error = "Currently passwords can only contain alphanumeric characters and not be all spaces.";
                    }
                } else {
                    $error = "Passwords don't match.";
                }
            } else {
                $error = "Please fill in both password fields.";
            }
        } else if($type == "avatar") {
            $user_id = $_SESSION['user_id'];
            if(isset($_FILES['user_pic'])) {
                if($_FILES['user_pic']['error'] == 0) {
                    if(isset($_FILES['user_pic']['tmp_name'])) {
                        print_r($_FILES);
                        $filename = $_FILES['user_pic']['name'];
                        $ext = get_ext($filename);
                        if(in_array(strtolower($ext), $allowed_exts)) {
                            $new_file = $user_id . "." . $ext;
                            $new_path = "./images/avatars/$new_file";
                            if(file_exists($new_path)) {
                                unlink($new_path);
                            }
                            copy($_FILES['user_pic']['tmp_name'], $new_path);
                            set_user_avatar($user_id, $new_file);
                        } else {
                            $error = "Illegal file type uploaded.";
                        }
                    } else {
                        $error = "File did not upload.";
                    }
                } else {
                    if($_FILES['user_pic']['error'] == 2 || $_FILES['user_pic']['error'] == 1) {
                        $error = "File too big, max size is 1MB.";
                    } else if($_FILES['user_pic']['error'] == 3) {
                        $error = "Image not completely uploaded, please try again.";
                    } else if($_FILES['user_pic']['error'] == 4) {
                        $error = "File not uploaded, please try again.";
                    } else if($_FILES['user_pic']['error'] == 6) {
                        $error = "Missing an upload folder.";
                    } else if($_FILES['user_pic']['error'] == 7) {
                        $error = "Failed to write file to disk.";
                    } else if($_FILES['user_pic']['error'] == 8) {
                        $error = "File upload stopped by extension.";
                    } else {
                        $error = "Error number " . $_FILES['user_pic']['error'] . " thrown.";
                    }
                }
            }
        }
    }

    render_header("Thieves Tavern Account");
    echo "<div class='center'>\n";
    if($error != "") {
        echo "<p class='error'>$error</p>\n";
    }
    echo "</div>\n";
    echo "<div id='account_settings_div'>\n";
    echo "<h3>Password</h3>\n";
    echo "<form id='account_password' action='./account.php' method='POST'>\n";
    echo "<label class='fixed_width'>Password: </label>";
    echo "<input type='password' name='password1' style='width: 22em;'/>\n";
    echo "<br />\n";
    echo "<label class='fixed_width'>Re-typed: </label>";
    echo "<input type='password' name='password2' style='width: 22em;' />\n";
    echo "<br />\n";
    echo "<input type='hidden' name='type' value='password' />\n";
    echo "<input type='submit' name='submit' value='Update Password' style='margin-top: .5em;'/>\n";
    echo "</form>\n";
    echo "</div>\n";

    echo "<div id='account_settings_div'>\n";
    echo "<h3>Avatar</h3>\n";
    echo "<img src='images/avatars/" . get_player_avatar($_SESSION['user_id']) . "' />\n";
    echo "<form id='account_avatar' action='./account.php' method='POST' ".
         "enctype='multipart/form-data' style='margin-top: .5em;'>\n";
    echo "<input type='hidden' name='type' value='avatar' />\n";
    echo "<input type='hidden' name='MAX_FILE_SIZE' value='$max_size'>\n";
    echo "<p>Choose a file to upload. Max size is 1MB, and the picture will be scaled down to 150px wide.</p>\n";
    echo "<input type='file' name='user_pic' />\n";
    echo "<br />\n";
    echo "<input type='submit' name='submit' value='Upload Avatar' style='margin-top: .25em;'/>\n";
    echo "</form>\n";
    echo "</div>\n";


    echo "</div>\n";
    render_footer();

?>
