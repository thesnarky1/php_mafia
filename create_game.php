<?php

    include('./includes/functions.php');

    $error = "";

    if(isset($_POST['game_name']) && isset($_POST['game_pass']) 
        && isset($_POST['game_pass2']) && is_logged_in()) {
        $user_id = $_SESSION['user_id'];
        $game_pass = $_POST['game_pass'];
        $game_pass2 = $_POST['game_pass2'];
        $game_name = $_POST['game_name'];
        $game_name = trim($game_name);
        if($game_name != "") {
            $game_name = safetify_input($game_name);
            if($game_pass == $game_pass2) {
                $query = "SELECT game_id FROM game_players WHERE user_id='$user_id' AND player_alive='Y'";
                $result = mysqli_query($dbh, $query);
                if($result) {
                    if(mysqli_num_rows($result) >=5) {
                        $error = "Sorry, you may only be in 5 games, alive, at any one time.";
                    } else {
                        if($game_pass != "") {
                            $game_pass = "MD5('$game_pass')";
                        }
                        if(strlen($game_name) > 32) {
                            $error = "Sorry, game name may not exceed 32 characters.";
                            $game_name = substr($game_name, 0, 32);
                        } else {
                            $query = "INSERT INTO games(game_name, game_creator, game_creation_date, game_phase, game_password, game_recent_date) ".
                                     "VALUES('$game_name', '$user_id', NOW(), 0, '$game_pass', NOW())";
                            $result = mysqli_query($dbh, $query);
                            if($result && mysqli_affected_rows($dbh) == 1) {
                                //Successful
                                $game_id = mysqli_insert_id($dbh);
                                $query = "INSERT INTO game_players(game_id, user_id, role_id) ".
                                         "VALUES('$game_id', '$user_id', 5)";
                                $result = mysqli_query($dbh, $query);
                                if($result && mysqli_affected_rows($dbh) == 1) {
                                    //Successful
                                    header("Location: games.php");
                                } else {
                                    $error = "Game created, but unable to add player. Contact an admin.";
                                }
                            } else {
                                $error = "Error occured during creation, please try again.";
                            }
                        }
                    }
                }
            } else {
                $error = "Passwords must match. To create a game with no password, leave both blank.";
            }
        } else {
            $error = "Must input a game name, not made up of only whitespace.";
        }
    }

    render_header("Thieves Tavern Create Game");
    
    if(is_logged_in()) {
        echo "<div id='create_game_form'>\n";
        if($error != "") {
            echo "<p class='error'>$error</p>\n";
        }
        echo "<form method='POST' action='./create_game.php'>\n";
        echo "<h3>Create Game</h3>\n";
        echo "<label>Game name: </label>\n";
        echo "<input type='text' name='game_name' size='60' ";
        if(isset($game_name) || isset($_POST['game_name'])) {
            if(isset($game_name)) {
                $game_name = stripslashes($game_name);
            } else {
                $game_name = stripslashes($_POST['game_name']);
            }
            echo "value='". htmlentities($game_name, ENT_QUOTES) ."' ";
        }
        echo "/>\n";
        echo "<br />\n";
        echo "<label>Game password: </label>\n";
        echo "<input type='password' name='game_pass' class='submit' />\n";
        echo "<br />\n";
        echo "<label>Game password: </label>\n";
        echo "<input type='password' name='game_pass2' class='submit' />\n";
        echo "<br />\n";
        echo "<input type='submit' value='Create Game' class='submit' />\n";
        echo "</form>\n";
        echo "</div>\n"; //Close create_game_form
    } else {
        echo "<div id='create_game_form'>\n";
        echo "<p class='error'>You must be <a href='./login.php'>logged in</a> to create a game.</p>\n";
        echo "</div>\n"; //Close create_game_form
    }
    render_footer();

?>
