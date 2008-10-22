<?php

    include('./includes/functions.php');

    render_header("Thieves Tavern Help");
    echo "<ol>\n";
    echo "<li>\n";
    echo "Roles";
    echo "<br />\n";
    echo "<ul>\n";
    $query = "SELECT role_name FROM roles";
    if($rows = mysqli_get_many($query)) {
        foreach($rows as $row) {
            $role_name = $row['role_name'];
            echo "<li>$role_name</li>\n";
        }
    }
    echo "</ul>\n";
    echo "</li>\n";

    echo "</ol>\n";
    render_footer();

?>
