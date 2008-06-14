<?php
    
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

        echo "<div id='upper_header'>\n";

        echo "<div id='logo'>\n";
        echo "<img src='./images/logo.gif' />\n";
        echo "</div>\n"; //Close logo

        echo "<div id='player_info'>\n";
        echo "<p class='player_name'>Thesnarky1</p>";
        echo "</div>\n"; //Close player_info

        echo "</div>\n"; //Close upper_header

        echo "<div id='lower_header'>\n";
        echo "</div>\n"; //Close lower_header

        echo "</div>\n"; //Close header
    }

    function render_footer() {
       echo "</div>\n"; //Close content
       echo "</body>\n";
       echo "</html>\n";
    }

?>
