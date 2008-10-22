<?php

    include('./includes/functions.php');

    $error = "";

    if(isset($_POST['game_name']) && isset($_POST['game_pass']) 
        && isset($_POST['game_pass2']) && is_logged_in()) {
        $user_id = $_SESSION['user_id'];
        $game_pass = safetify_input($_REQUEST['game_pass']);
        $game_pass2 = safetify_input($_REQUEST['game_pass2']);
        $game_name = safetify_input($_REQUEST['game_name']);
        $game_name = trim($game_name);
        if($game_name != "") {
            if($game_pass == $game_pass2) {
                $query = "SELECT game_id FROM game_players WHERE user_id='$user_id' AND player_alive='Y'";
                if($rows = mysqli_get_many($query)) {
                    if(count($rows) >= 5) {
                        $error = "Sorry, you may only be in 5 games, alive, at any one time.";
                    } else {
                        if($game_pass != "") {
                            $game_pass = "MD5('$game_pass')";
                        }
                        if(strlen($game_name) > $game_name_limit) {
                            $error = "Sorry, game name may not exceed $game_name_limit characters.";
                            $game_name = substr($game_name, 0, $game_name_limit);
                        } else {
                            $query = "INSERT INTO games(game_name, game_creator, game_creation_date, game_phase, game_password, game_recent_date) ".
                                     "VALUES('$game_name', '$user_id', NOW(), 0, '$game_pass', NOW())";
                            if($game_id = mysqli_insert($query)) {
                                //Successful
                                $query = "INSERT INTO game_players(game_id, user_id, role_id) ".
                                         "VALUES('$game_id', '$user_id', 5)";
                                if(mysqli_insert($query)) {
                                    //Successful
                                    //Add in unassigned channel for pre-game chit chat
                                    $channel_name = "unassigned_" . $game_id;
                                    $query = "INSERT INTO channels(channel_name, game_id, global) ".
                                             "VALUES('$channel_name', '$game_id', 'Y')";
                                    if($channel_id = mysqli_insert($query)) {
                                        $query = "INSERT INTO channel_members(channel_id, user_id, channel_post_rights) ".
                                                 "VALUES('$channel_id', '$user_id', '1')";
                                        if(mysqli_insert($query)) {
                                            header("Location: games.php?game_id=$game_id");
                                        } else {
                                            $error = "Unable to add channel_member - $query";
                                        }
                                    } else {
                                        $error = "Unable to create pre-chat channel - $query";
                                    }
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
        echo "<label><span class='error'>*</span>Game name: </label>\n";
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
//        echo "<label>Minimum Players: </label>\n";
//        echo "<input type='text' size='2' name='minimum' class='submit' />\n";
//        echo "<br />\n";
//        echo "<label>Maximum Players: </label>\n";
//        echo "<input type='text' size='2' name='maximum' class='submit' />\n";
//        echo "<br />\n";
        echo "<label>Game password: </label>\n";
        echo "<input type='password' name='game_pass' class='submit' />\n";
        echo "<br />\n";
        echo "<label>Game password: </label>\n";
        echo "<input type='password' name='game_pass2' class='submit' />\n";
        echo "<br />\n";
        echo "<input type='submit' value='Create Game' class='submit' />\n";
        echo "</form>\n";
        echo "</div>\n"; //Close create_game_form
        echo "<div id='create_game_form'>\n";
        echo "<p>\n";
        echo "<span class='error'>*</span> - Required field, all others are optional.<br />\n";
        echo "If a password is entered once, it must be entered both times.<br />\n";
        echo "If no maximum and/or minimum is given, the game will be open to as many or as few players as we have rolesets for.<br />";
        echo "</p>\n";
        echo "</div>\n";
    } else {
        echo "<div id='create_game_form'>\n";
        echo "<p class='error'>You must be <a href='./login.php'>logged in</a> to create a game.</p>\n";
        echo "</div>\n"; //Close create_game_form
    }
    render_footer();

?>
