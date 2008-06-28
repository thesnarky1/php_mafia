<?php

    include('./includes/functions.php');

    render_header("Thieves Tavern Help");
    echo "<ol>\n";
    echo "<li>\n";
    echo "Roles";
    echo "<br />\n";
    echo "<ul>\n";
    $query = "SELECT role_name FROM roles";
    $result = mysqli_query($dbh, $query);
    if($result && mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_array($result)) {
            $role_name = $row['role_name'];
            echo "<li>$role_name</li>\n";
        }
    }
    echo "</ul>\n";
    echo "</li>\n";

    echo "</ol>\n";
    render_footer();

?>
