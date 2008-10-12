<?php

    include('./includes/functions.php');

    if(isset($_POST['user_name']) && isset($_POST['user_pass'])) {
        //Login
        $user_name = safetify_input($_POST['user_name']);
        $user_pass = safetify_input($_POST['user_pass']);
        $query = "SELECT * FROM users ".
                 "WHERE user_name LIKE '$user_name' AND user_pass=MD5('$user_pass') ".
                 "LIMIT 1";
        $result = mysqli_query($dbh, $query);
        if(mysqli_num_rows($result) < 1) {
            $error = "Unknown user/password combination.";
        } else {
            $error = "";
            $row = mysqli_fetch_array($result);
            $user_name = $row['user_name'];
            $user_id = $row['user_id'];
            $user_hash = $row['user_hash'];
            $_SESSION['user_name'] = $user_name;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_hash'] = $user_hash;
        }

        render_header("Thieves Tavern Login");

        if($error != "") {
            render_big_login_form($error);
        } else {
            echo "<p class='center'>Welcome $user_name!</p>\n";
        }

        render_footer();

    } else {
        //Display login page
        render_header("Thieves Tavern Login");

        render_big_login_form();

        render_footer();
    }


?>
