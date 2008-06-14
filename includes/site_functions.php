<?php


    function harsh_replace($str) {
        $str = preg_replace("/[^\d\w]/", "", $str);
        return $str;
    }

    function render_big_login_form($error = "") {
        echo "<form action='./login.php' method='POST' class='center'>\n";
        echo "<p>Login Form</p>\n";
        if($error != "") {
            echo "<span class='error'>$error</span><br />\n";
        }
        echo "<label>Username: </label>\n";
        echo "<input name='user_name' type='text' />\n";
        echo "<br />\n";
        echo "<label>Password: </label>\n";
        echo "<input name='user_pass' type='password' />\n";
        echo "<br />\n";
        echo "<input type='submit' value='Login' style='margin-top: .25em;'/>\n";
        echo "</form>\n";
    }

    function render_header($title="Thieves Tavern") {
        //Render <head> junk
        echo "<html>\n";

        echo "<head>\n";
        echo "<link rel='stylesheet' href='./includes/style.css' type='text/css' media='screen' />\n";
        echo "<title>$title</title>\n";
        echo "</head>\n";

        //Render the top of the body
        echo "<body>\n";
        echo "<div id='content'>\n";

        echo "<div id='header'>\n";

        echo "<div id='logo'>\n";
        echo "<a href='./index.php'><img src='./images/logo.gif' /></a>\n";
        echo "</div>\n"; //Close logo

        echo "<div id='player_info'>\n";
        echo "<p class='player_name'>Thesnarky1</p>";
        echo "<span class='player_info_button'><a href='./account.php'>Account</a></span>\n";
        echo "<span class='player_info_button'><a href='./profile.php'>Profile</a></span>\n";
        echo "<span class='player_info_button'><a href='./games.php'>Games</a></span>\n";
        echo "</div>\n"; //Close player_info

        echo "<div id='nav_buttons'>\n";
        echo "<span class='nav_button'><a href='./index.php'>Home</a></span>\n";
        echo "<span class='nav_button'><a href='./games.php'>Games</a></span>\n";
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
