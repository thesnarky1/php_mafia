<?php

    include('./includes/functions.php');

    if(isset($_POST['user_name']) && isset($_POST['user_pass'])) {
        //Login
        $user_name = safetify_input($_POST['user_name']);
        $user_pass = safetify_input($_POST['user_pass']);
        $query = "SELECT * FROM users ".
                 "WHERE user_name LIKE '$user_name' AND user_pass=MD5('$user_pass') ".
                 "LIMIT 1";
        if($row = mysqli_get_one($query)) {
            $error = "";
            $user_name = $row['user_name'];
            $user_id = $row['user_id'];
            $user_hash = $row['user_hash'];
            if(isset($_POST['remember']) && $_POST['remember'] == 'true') {
                $remember = true;
            } else {
                $remember = false;
            }
            login_user($user_name, $user_id, $user_hash, $remember);
            if(isset($_POST['next_page'])) {
                $next = safetify_input($_POST['next_page']);
                header("Location: $next");
            }
        } else {
            $error = "Unknown user/password combination.";
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
