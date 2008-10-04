<?php

    include('./includes/functions.php');

    render_header("Thieves Tavern Profile");

    if(isset($_GET['id'])) {
        $user_id = int_replace($_GET['id']);
        $query = "SELECT user_name FROM users WHERE user_id='$user_id'";
        $result = mysqli_query($dbh, $query);
        if($result && mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_array($result);
            $user_name = $row['user_name'];
            echo "<div id='profile_header'>\n";
            echo "<div id='profile_header_img'>\n";
            echo "<img src='images/avatars/" . get_player_avatar($user_id) . "' />\n";
            echo "</div>\n";
            echo "<div id='profile_header_name'>\n";
            echo "<h1>$user_name</h1>\n";
            echo "</div>\n";
            echo "</div>\n";
        } else {
            echo "<p class='error'>Profile not found!</p>\n";
        }
        
    }


    render_footer();

?>
