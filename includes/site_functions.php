<?php

    function is_logged_in() {
        if(isset($_SESSION['user_id']) && isset($_SESSION['user_name']) &&
            $_SESSION['user_name'] != "" && $_SESSION['user_id'] != "") {
            return true;
        } else {
            return false;
        }
    }

    function get_player_avatar($user_id) {
        global $dbh;
        $query = "SELECT user_avatar FROM users WHERE user_id='user_id'";
        $result = mysqli_query($dbh, $query);
        if($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_array($result);
            return $row['user_avatar'];
        } else {
            return 'face.png';
        }
    }

    function harsh_replace($str) {
        $str = preg_replace("/[^\d\w]/", "", $str);
        return $str;
    }

    function pass_check($str) {
        preg_match("/[^\d\w]/", $str, $matches);
        if(count($matches) > 0) {
            return false;
        } else {
            return true;
        }
    }

    function pass_replace($str) {
        $str = preg_replace("/[^\d\w]/", "", $str);
        return $str;
    }

    function safetify_input($str) {
        global $dbh;
        $str = stripslashes($str);
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
        echo "<form action='./login.php' method='POST' class='form'>\n";
        echo "<p>Login Form</p>\n";
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

    function render_header($title="Thieves Tavern", $on_load='') {
        //Render <head> junk
        echo "<html>\n";

        echo "<head>\n";
        echo "<link rel='stylesheet' href='./includes/style.css' type='text/css' media='screen' />\n";
        echo "<title>$title</title>\n";
        echo "<script type='text/javascript' src='./scripts/chat.js'></script>\n";
        echo "<script type='text/javascript' src='./scripts/game_info.js'></script>\n";
        echo "<script type='text/javascript' src='./scripts/perform_action.js'></script>\n";
        echo "</head>\n";

        //Render the top of the body
        echo "<body onload='$on_load'>\n";
        echo "<div id='content'>\n";

        echo "<div id='header'>\n";

        echo "<div id='logo'>\n";
        echo "<a href='./index.php'><img src='./images/logo.gif' /></a>\n";
        echo "</div>\n"; //Close logo

        echo "<div id='player_info'>\n";

        if(is_logged_in()) {
            $user_name = $_SESSION['user_name'];
            echo "<p class='player_name'>$user_name <span class='small_text'><a href='./logout.php'>(logout)</a></span></p>";
            echo "<span class='player_info_button'><a href='./account.php'>Account</a></span>\n";
            echo "<span class='player_info_button'><a href='./profile.php'>Profile</a></span>\n";
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

        echo "<div id='nav_buttons'>\n";
        echo "<span class='nav_button'><a href='./index.php'>Home</a></span>\n";
        echo "<span class='nav_button'><a href='./games.php'>Games</a></span>\n";
        echo "<span class='nav_button'><a href='./news.php'>News</a></span>\n";
        echo "<span class='nav_button'><a href='./help.php'>Help</a></span>\n";
        echo "</div>\n"; //Close nav_buttons

        echo "</div>\n"; //Close header
        echo "<div id='content'>\n";
    }

    function render_footer() {
       echo "</div>\n"; //Close content
       echo "</body>\n";
       echo "</html>\n";
    }

?>
