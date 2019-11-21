<?php

    include('./includes/functions.php');

    render_header("Thieves Tavern Help");
    echo "<ol>\n";
    echo "<li>For basic information, try <a href='http://wiki.mafiascum.net/index.php?title=Newbie_Guide'>this page</a>.</li>";
    echo "<li>\n";
    echo "Roles";
    echo "<br />\n";
    echo "<ul>\n";
    $query = "SELECT role_name, role_help FROM roles";
    if($rows = mysqli_get_many($query)) {
        foreach($rows as $row) {
            $role_name = $row['role_name'];
            $role_help = $row['role_help'];
            echo "<li>";
            echo "<b>$role_name</b>";
            echo "<p>$role_help</p>\n";
            echo "</li>\n";
        }
    }
    echo "</ul>\n";
    echo "</li>\n";

    echo "</ol>\n";
    render_footer();

?>
