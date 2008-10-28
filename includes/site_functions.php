<?php

    function capitalize($str) {
        $str = strtoupper(substr($str, 0, 1)) . 
               substr($str, 1);
        return $str;
    }

    function get_timestamp($file) {
        return filectime($file);
    }

    function is_user_admin($user_id) {
        $query = "SELECT user_role FROM users WHERE user_id='$user_id'";
        if($row = mysqli_get_one($query)) {
            if($row['user_role'] == 'A') {
                return true;
            }
        }
        return false;
    }

    function valid_user($user_id, $user_hash) {
        global $dbh;
        $query = "SELECT user_id FROM users WHERE user_id='$user_id' AND user_hash='$user_hash'";
        return mysqli_get_one($query);
    }

    function login_user($user_name, $user_id, $user_hash) {
        $_SESSION['user_name'] = $user_name;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_hash'] = $user_hash;
    }

    function send_invite_email($email, $reg_code, $inviter) {
        $body = "Hey there!\n".
                "Someone over at Thieves Tavern ($inviter) thought you'd have a blast if you joined. If you would like to, please use the invitation link below to create a user on our site.\n\n".
                "http://thievestavern.com/register.php?email=$email&code=$reg_code\n\n".
                "If the above address doesn't show up as a link, try cutting and pasting the URL into a browser.\n".
                "Have a great day, and survive the night!\n".
                "--Thieves Tavern Management\n";
        $name = "Thieves Tavern";
        $from_email = "no-reply@thievestavern.com";
        $subject = "Thieves Tavern (Mafia) Invitation";
        $headers = "From: " . $name . " <" . $from_email . ">\r\n";
        return mail($email, $subject, $body, $headers);
    }

    function create_user_hash() {
        $hash = md5(uniqid('', TRUE));
        return $hash;
    }

    function is_logged_in() {
        if(isset($_SESSION['user_id']) && isset($_SESSION['user_name']) &&
            $_SESSION['user_name'] != "" && $_SESSION['user_id'] != "") {
            return true;
        } else {
            return false;
        }
    }

    function resize_and_copy($img, $new_img) {
        $max_size = 100;
        $ext = get_ext($new_img);
        list($width, $height) = getimagesize($img);
        if($width > $max_size || $height > $max_size) { //If both are 150 or under we do nothing
            if($ext == "jpg" || $ext == "jpeg") {
                $src_img = imagecreatefromjpeg($img);
            } else if($ext == "png") {
                $src_img = imagecreatefrompng($img);
            } else if($ext == "gif") {
                $src_img = imagecreatefromgif($img);
            }
            if(!$src_img) {
                die("No source img - $ext");
            }
            if($width > $height) { //resize width to 150, keep aspect ratio
                $new_width = $max_size;
                $new_height = ($height/$width) * $max_size;
            } else if($height > $width) { //resize height, keep aspect ratio
                $new_height = $max_size;
                $new_width = ($width/$height) * $max_size;
            } else { //resize both to 150
                $new_height = $max_size;
                $new_width = $max_size;
            }
            $tmp_img = imagecreatetruecolor($new_width, $new_height);
            imagecopyresampled($tmp_img, $src_img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            if($ext == "jpg" || $ext == "jpeg") {
                imagejpeg($tmp_img, $new_img);
            } else if($ext == "png") {
                imagepng($tmp_img, $new_img);
            } else if($ext == "gif") {
                imagegif($tmp_img, $new_img);
            } else {
                echo "Uh-oh...";
                die();
            }
        } else {
            copy($img, $new_img);
        }
    }

    function set_user_avatar($user_id, $filename) {
        global $dbh;
        if(file_exists("./images/avatars/$filename")) {
            //Make sure there's something there
        } else {
            $filename = "face.png";
        }
        $query = "UPDATE users SET user_avatar='$filename' WHERE user_id='$user_id'";
        $row = mysqli_set_one($query);
    }

    function get_ext($file) {
        if(strpos($file, '.') >= 0) {
            $period = strpos($file, '.');
            $ext_len = strlen($file) - $period;
            $ext = substr($file, $period + 1, $ext_len);
            return $ext;
        } else {
            return "";
        }
    }

    function get_player_avatar($user_id) {
        global $dbh;
        $query = "SELECT user_avatar FROM users WHERE user_id='$user_id'";
        if($row = mysqli_get_one($query)) {
            return $row['user_avatar'];
        } else {
            return 'face.png';
        }
    }

    function harsh_replace($str) {
        $str = preg_replace("/[^\d\w]/", "", $str);
        return $str;
    }

    function int_replace($str) {
        $str = preg_replace("/\D/", "", $str);
        return $str;
    }

    function pass_check($str) {
        if(trim($str) == "") {
            return false;
        } else {
            if(strlen($str) < 6) {
                return false;
            } else {
                return true;
            }
        }
    }

    function username_check($str) {
        preg_match("/[^\d\w]/", $str, $matches);
        if(count($matches) > 0) {
            return false;
        } else {
            if(trim($str) == "") {
                return false;
            } else {
                if(strlen($str) < 5) {
                    return false;
                } else {
                    return true;
                }
            }
        }
    }

    function safetify_input($str) {
        global $dbh;

        $str = html_entity_decode($str);
        //$str = htmlspecialchars($str);
        $str = mysqli_real_escape_string($dbh, $str);
        return $str;
    }

    function convert_to_html($str) {
        $str = str_replace("\\r", "", $str);
        $str = str_replace("\\n", "<br/>", $str);
        //$str = str_replace(" ", "&nbsp;", $str);
        return $str;
    }

    function render_big_login_form($error = "") {
        echo "<div id='big_login_form_div'>";
        echo "<h3>Login</h3>\n";
        echo "<form action='./login.php' method='POST' class='form'>\n";
        if($error != "") {
            echo "<span class='error'>$error</span><br />\n";
        }
        echo "<label class='fixed_width'>Username: </label>\n";
        echo "<input name='user_name' type='text' />\n";
        echo "<br />\n";
        echo "<label class='fixed_width'>Password: </label>\n";
        echo "<input name='user_pass' type='password' style='margin-top: .25em;' />\n";
        echo "<br />\n";
        echo "<input type='submit' value='Login' style='margin-top: .25em;'/>\n";
        echo "</form>\n";
        echo "</div>\n";
    }

    function get_required_javascript_specific_game() {
            $to_return = "";
            $to_return .= "<script language='Javascript' type='text/javascript'>\n";
            $to_return .= "google.load('jquery', '1.2');\n";
            $to_return .= "</script>\n";
            $to_return .= "<script type='text/javascript' src='./scripts/chat.js'></script>\n";
            $to_return .= "<script type='text/javascript' src='./scripts/game_info.js'></script>\n";
            $to_return .= "<script type='text/javascript' src='./scripts/perform_action.js'></script>\n";
            return $to_return;
    }

    function get_nav_bar() {
        $to_return = "<div id='nav_buttons'>\n";
        $to_return .= "<span class='nav_button'><a href='./index.php'>Home</a></span>\n";
        $to_return .= "<span class='nav_button'><a href='./games.php'>Games</a></span>\n";
        $to_return .= "<span class='nav_button'><a href='./news.php'>News</a></span>\n";
        $to_return .= "<span class='nav_button'><a href='./help.php'>Help</a></span>\n";
        $to_return .= "</div>\n"; //Close nav_buttons
        return $to_return;
    }

    function get_player_info_div($logged_in) {
        echo "<div id='player_info'>\n";
        if($logged_in) {
            $user_name = $_SESSION['user_name'];
            $user_id = $_SESSION['user_id'];
            echo "<p class='player_name'>$user_name <span class='small_text'><a href='./logout.php'>(logout)</a></span></p>";
            echo "<span class='player_info_button'><a href='./account.php'>Account</a></span>\n";
            echo "<span class='player_info_button'><a href='./profile.php?id=$user_id'>Profile</a></span>\n";
            echo "<span class='player_info_button'><a href='./games.php'>Games</a></span>\n";
        } else {
            echo "<form name='small_login_form' method='POST' action='./login.php' ".
                 "style='padding: .25em; text-align: center;'>\n";
            echo "<label class='fixed_label'>Username: </label>\n";
            echo "<input type='text' name='user_name'  style='margin-top: .25em;'/>\n";
            echo "<br />\n";
            echo "<label class='fixed_label'>Password: </label>\n";
            echo "<input type='password' name='user_pass' style='margin-top: .25em;' />\n";
            echo "<br />\n";
            echo "<input type='submit' value='Login' style='margin-top: .25em'/>\n";
            echo "</form>\n";
        }
        echo "</div>\n"; //Close player_info
    }

    function render_header($title="Thieves Tavern", $on_load='') {
        $file_name = $_SERVER['REQUEST_URI'];
        $file_name_arr = explode("/", $file_name);
        $file_name = $file_name_arr[count($file_name_arr) - 1];
        $file_name_arr = explode("?", $file_name);
        $file_name = $file_name_arr[0];

        //Render <head> junk
        echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' \n";
        echo "'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>";
        echo "<html xmlns='http://www.w3.org/1999/xhtml' lang='en' xml:lang='en'>\n";

        echo "<head>\n";
        echo "<meta http-equiv='Content-Type' content='text/html;charset=utf-8' />";
        echo "<link rel='stylesheet' href='./includes/style.css' type='text/css' media='screen' />\n";
        echo "<title>$title</title>\n";
        echo "<script src='http://www.google.com/jsapi?key=ABQIAAAAUsFEjhe8hOp3ncAxs_I-ZxTuReQOfkQuMttBdN_0aRFZ3els6xTBpqQ46vNpQyeS1piAI3qyWSxRaw' type='text/javascript'></script>";
        if(strtolower($file_name) == "games.php" && isset($_REQUEST['game_id'])) {
            echo get_required_javascript_specific_game();
        }
        echo "</head>\n";

        //Render the top of the body
        echo "<body onload='$on_load'>\n";
        echo "<div id='content'>\n";

        echo "<div id='header'>\n";

        echo "<div id='logo'>\n";
        echo "<a href='./index.php'><img src='./images/mafia_logo.png' /></a>\n";
        echo "</div>\n"; //Close logo

        echo get_player_info_div(is_logged_in());

        echo get_nav_bar();

        echo "</div>\n"; //Close header
        echo "<div id='content'>\n";
    }

    function render_footer() {
       echo "</div>\n"; //Close content
       echo "</body>\n";
       echo "</html>\n";
    }

?>
