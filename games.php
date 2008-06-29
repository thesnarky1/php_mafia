<?php

    include('./includes/functions.php');

    render_header("Thieves Tavern Games", "init_chat();init_info();");

    if(!isset($_GET['game_id'])) {
        if(is_logged_in()) {
            echo "<p class='banner'><a href='./create_game.php'>Create a game</a>?</p>\n";
        }
        echo "<div id='open_games'>\n";
        echo "<h3>Open Games</h3>\n";
        $query = "SELECT games.game_id, games.game_name, ".
                 "(SELECT COUNT(*) FROM game_players WHERE game_players.game_id=games.game_id AND game_players.player_alive='Y') as players ".
                 "FROM games ".
                 "WHERE games.game_phase=0 AND games.game_password=''".
                 "LIMIT 20";
        $result = mysqli_query($dbh, $query);
        if($result && mysqli_num_rows($result) > 0) {
            echo "<table class='game_table' align='center'>\n";
            echo "<tr class='header'>\n";
            echo "<td class='name'>Name</td>\n";
            echo "<td class='small'>Players</td>\n";
            echo "<td class='small'>Join</td>\n";
            echo "</tr>\n";
            while($row = mysqli_fetch_array($result)) {
                $game_name = $row['game_name'];
                $game_id = $row['game_id'];
                $game_players = $row['players'];
                echo "<tr>\n";
                echo "<td class='name'><a href='./games.php?game_id=$game_id'>$game_name</a></td>\n";
                echo "<td>$game_players</td>\n";
                echo "<td><a href='./games.php?game_id=$game_id&join=true'>Join</a></td>\n";
                echo "</tr>\n";
            }
            echo "</table>\n";
        } else {
            echo "<p class='error'>No open games.</p>\n";
        }
        echo "</div>\n"; //Close open games
        echo "<div id='my_games'>\n";
            echo "<h3>Your Current Games</h3>\n";
        if(is_logged_in()) {
            $user_id = $_SESSION['user_id'];
            //Current games
            $query = "SELECT game_players.game_id, games.game_phase, games.game_turn, games.game_name, ".
                     "(SELECT COUNT(*) FROM game_players WHERE game_players.game_id=games.game_id AND game_players.player_alive='Y') as alive, ".
                     "(SELECT COUNT(*) FROM game_players WHERE game_players.game_id=games.game_id AND game_players.player_alive='N') as dead ".
                     "FROM game_players, games ".
                     "WHERE game_players.user_id='$user_id' AND games.game_id=game_players.game_id ".
                     "AND games.game_phase != 3 ".
                     "ORDER BY games.game_turn DESC";
            $result = mysqli_query($dbh, $query);
            if($result && mysqli_num_rows($result) > 0) {
                echo "<table class='game_table' align='center'>\n";
                echo "<tr class='header'>\n";
                echo "<td class='name'>Name</td>\n";
                echo "<td class='small'>Turn</td>\n";
                echo "<td class='small'>Phase</td>\n";
                echo "<td class='small'>Alive</td>\n";
                echo "<td class='small'>Dead</td>\n";
                echo "</tr>\n";
                while($row = mysqli_fetch_array($result)) {
                    $game_name = $row['game_name'];
                    $game_id = $row['game_id'];
                    $game_phase = $row['game_phase'];
                    $game_phase = $phases[$game_phase];
                    $game_turn = $row['game_turn'];
                    $alive = $row['alive'];
                    $dead = $row['dead'];
                    echo "<tr>\n";
                    echo "<td class='name'><a href='./games.php?game_id=$game_id'>$game_name</a></td>\n";
                    echo "<td>$game_turn</td>\n";
                    echo "<td>$game_phase</td>\n";
                    echo "<td>$alive</td>\n";
                    echo "<td>$dead</td>\n";
                    echo "</tr>\n";
                }
                echo "</table>\n";
            } else {
                echo "<p class='error'>You're not playing any games currently</p>\n";
            }
        } else {
            echo "<p class='error'>Please login to check your current games.</p>\n";
        }
        echo "</div>\n";
    } else { //Have a game to view
        $error = "";
        if(is_logged_in()) {
            $user_id = $_SESSION['user_id'];
        } else {
            $user_id = false;
        }
        $game_id = $_GET['game_id'];
        if(isset($_GET['join']) || isset($_POST['join'])) {
            if($user_id) {
                $query = "SELECT game_phase, game_password FROM games WHERE game_id='$game_id'";
                $result = mysqli_query($dbh, $query);
                if($result && mysqli_num_rows($result) == 1) {
                    $row = mysqli_fetch_array($result);
                    $game_password = $row['game_password'];
                    if($row['game_phase'] == 0) {
                        $query = "SELECT user_id FROM game_players ".
                                 "WHERE user_id='$user_id' AND ".
                                 "player_alive='Y'";
                        $result = mysqli_query($dbh, $query);
                        if($result && mysqli_num_rows($result) <= 4) {
                            $query = "SELECT user_id FROM game_players ".
                                     "WHERE game_id='$game_id' AND user_id='$user_id'";
                            $result = mysqli_query($dbh, $query);
                            if($result && mysqli_num_rows($result) == 0) {
                                if($game_password != "") {
                                    if(isset($_POST['password'])) {
                                        //Check password
                                    } else {
                                        $error = "This game requires a password, please put it in and try again.";
                                    }
                                } else {
                                    $query = "INSERT INTO game_players(game_id, user_id, role_id) ".
                                             "VALUES('$game_id', '$user_id', 5)";
                                    $result = mysqli_query($dbh, $query);
                                    if($result && mysqli_affected_rows($dbh) == 1) {
                                        //Successful
                                    } else {
                                        $error = "Error joining game, please try again if its still open.";
                                    }
                                }
                            } else {
                                $error = "You're already playing this game!";
                            }
                        } else {
                            $error = "Sorry, you may only be in 5 games, alive, at once.";
                        }
                    } else {
                        $error = "Sorry, this game is already in progress.";
                    }
                }
            } else {
                echo "<p class='error'>Sorry, you must be <a href='./login.php'>logged in</a> to join a game.</p>";
            }
        }
        if($user_id) {
            $query = "SELECT * FROM game_players WHERE user_id='$user_id' AND game_id='$game_id'";
            $result = mysqli_query($dbh, $query);
            if($result && mysqli_num_rows($result) > 0) {
                $query = "SELECT * ".
                         "FROM games, game_players ".
                         "WHERE games.game_id='$game_id' AND game_players.game_id=games.game_id ".
                         "AND game_players.user_id='$user_id'";
            } else {
                $user_id = false;
                $query = "SELECT * FROM games WHERE game_id='$game_id'";
            }
        } else {
            $query = "SELECT * FROM games WHERE game_id='$game_id'";
        }
        $result = mysqli_query($dbh, $query);
        if($result && mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_array($result);
            $game_name = $row['game_name'];
            $game_phase = $row['game_phase'];
            $game_turn = $row['game_turn'];
            if($user_id) {
                $role_id = $row['role_id'];
            }
            //Make banner
            if($error != "") {
                echo "<p class='banner'><span class='error'>$error</span></p>\n";
            }
            if($game_phase == 0){
                echo "<p class='banner'>Game not yet started.</p>\n";
            } else if($game_phase == 3) {
                echo "<p class='banner'>Game has finished.</p>\n";
            } else {
                if($user_id) {
                    $query = "SELECT * FROM roles WHERE role_id='$role_id'";
                    $result = mysqli_query($dbh, $query);
                    if($result && mysqli_num_rows($result) > 0) {
                        $row = mysqli_fetch_array($result);
                        $role_name = $row['role_name'];
                        $role_day = $row['day_instructions'];
                        $role_night = $row['night_instructions'];
                        if($phase == 1) {
                            $do_what = $role_night;
                        } else {
                            $do_what = $role_day;
                        }
                        echo "<p class='banner'>You are a $role_name. $do_what</p>\n";
                    }
                } else {
                    echo "<p class='banner'>You aren't in this game.</p>\n";
                }
            }

            //Game information
            echo "<div id='game_information'>\n";
            echo "<h3 class='game_h3'>Game Information</h3>\n";
            echo "<p>\n";
            echo "Turn: <span id='game_turn'></span><br />\n";
            echo "Phase: <span id='game_phase'></span><br />\n";
            echo "</p>\n";
            echo "Alive: <span id='game_alive'></span>\n";
            echo "<ul class='game_player_list' id='game_alive_list'>\n";
            echo "</ul>\n";
            echo "Dead: <span id='game_dead'></span>\n";
            echo "<ul class='game_player_list' id='game_dead_list'>\n";
            echo "</ul>\n";
            echo "</div>\n";

            //Make person table
            $query = "SELECT users.user_name, users.user_avatar, ".
                     "game_players.player_alive, users.user_id, ".
                     "roles.role_name, roles.role_faction ".
                     "FROM users, game_players, roles ".
                     "WHERE game_players.game_id='$game_id' AND ".
                     "roles.role_id=game_players.role_id AND ".
                     "users.user_id=game_players.user_id ".
                     "ORDER BY game_players.player_alive DESC ";
            $result = mysqli_query($dbh, $query);
            if($result && mysqli_num_rows($result) > 0) {
                echo "<div id='player_box_table'>\n";
                echo "<table align='center'>\n";
                $x = 1;
                $box_per_row = 2;
                while($row = mysqli_fetch_array($result)) {
                    $player_id = $row['user_id'];
                    $user_name = $row['user_name'];
                    $user_avatar = $row['user_avatar'];
                    $player_alive = $row['player_alive'];
                    $role_name = $row['role_name'];
                    $role_faction = $row['role_faction'];
                    if($x % $box_per_row == 1) {
                        echo "<tr>\n";
                    }
                    echo "<td ";
                    if($player_alive == 'N' || ($player_id == $user_id && is_logged_in())) {
                        echo "class='game_player_" . $role_faction . "'";
                    } else {
                        echo "class='game_player_Unknown'";
                    }
                    echo ">";
                    echo "<div id='player_box'>\n";
                    if($player_alive == 'Y') {
                        echo "<a href='#'>";
                        echo "<img src='./images/$user_avatar'>";
                        echo "<p class='player_name'>$user_name</p>\n";
                        echo "</a>\n";
                    } else {
                        echo "<img src='./images/dead.png'>";
                        echo "<p class='player_name'><span class='strikeout'>$user_name</span></p>\n";
                    }
                    echo "</div>\n";
                    echo "</td>\n";
                    if($x % $box_per_row == 0) {
                        echo "</tr>\n";
                    }
                    $x++;
                }
                if(($x - 1) % $box_per_row != 0) {
                    echo "</tr>\n";
                }
                echo "</table>\n";
                echo "</div>\n"; //Close player_box_table
            } else {
                echo "$query";
            }

            //Game chat
            echo "<div id='game_chat'>\n";
            echo "<h3 class='game_h3'>Chat</h3>\n";
            echo "<div name='chat_text' id='chat_text' >";
            echo "</div>\n";
            if(is_logged_in()) {
                echo "<input type='text' onkeydown='handleKey(event)' name='text_box' id='text_box' style='width: 100%' />\n";
            }
            echo "<input type='hidden' id='user_id' value='$_SESSION[user_id]' />\n";
            echo "<input type='hidden' id='user_hash' value='$_SESSION[user_hash]' />\n";
            echo "<input type='hidden' id='game_id' value='$game_id' />\n";
            echo "</div>\n"; //Close game_chat
        } else {
            //Game doesn't exist
            echo "<p class='error'>Sorry, that game doesn't exist.</p>\n";
        }
    }

    render_footer();

?>
