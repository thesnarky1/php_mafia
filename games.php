<?php

    include('./includes/functions.php');

    render_header("Thieves Tavern Games");

    if(!isset($_GET['game_id'])) {
        if(is_logged_in()) {
            echo "<p class='banner'><a href='./create_game.php'>Create a game</a>?</p>\n";
        }
        echo "<div id='my_games'>\n";
        if(is_logged_in()) {
            $user_id = $_SESSION['user_id'];
            echo "<h3>Your Current Games</h3>\n";
            $query = "SELECT game_players.game_id, games.game_phase, games.game_turn, games.game_name, ".
                     "(SELECT COUNT(*) FROM game_players WHERE game_players.game_id=games.game_id AND game_players.player_alive='Y') as alive, ".
                     "(SELECT COUNT(*) FROM game_players WHERE game_players.game_id=games.game_id AND game_players.player_alive='N') as dead ".
                     "FROM game_players, games ".
                     "WHERE game_players.user_id='$user_id' AND games.game_id=game_players.game_id ".
                     "AND games.game_phase != 3";
            $result = mysqli_query($dbh, $query);
            if($result && mysqli_num_rows($result) > 0) {
                echo "<table align='center'>\n";
                echo "<tr class='header'>\n";
                echo "<td class='name'>Name</td>\n";
                echo "<td class='small'>Alive</td>\n";
                echo "<td class='small'>Dead</td>\n";
                echo "<td class='small'>Turn</td>\n";
                echo "<td class='small'>Phase</td>\n";
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
                    echo "<td>$alive</td>\n";
                    echo "<td>$dead</td>\n";
                    echo "<td>$game_turn</td>\n";
                    echo "<td>$game_phase</td>\n";
                    echo "</tr>\n";
                }
                echo "</table>\n";
            } else {
                echo "<p class='error'>You're not playing any games currently</p>\n";
            }
        }
        echo "</div>\n";
    } else {
        //Have a game to view
        if(is_logged_in()) {
            $user_id = $_SESSION['user_id'];
        } else {
            $user_id = false;
        }
        $game_id = $_GET['game_id'];
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

//Make person table

            $query = "SELECT users.user_name, users.user_avatar, ".
                     "game_players.player_alive, users.user_id, ".
                     "roles.role_name, roles.role_faction ".
                     "FROM users, game_players, roles ".
                     "WHERE game_players.game_id='$game_id' AND ".
                     "roles.role_id=game_players.role_id AND ".
                     "users.user_id=game_players.user_id ".
                     "ORDER BY player_alive DESC";
            $result = mysqli_query($dbh, $query);
            if($result && mysqli_num_rows($result)) {
                echo "<table align='center'>\n";
                $x = 1;
                while($row = mysqli_fetch_array($result)) {
                    $player_id = $row['user_id'];
                    $user_name = $row['user_name'];
                    $user_avatar = $row['user_avatar'];
                    $player_alive = $row['player_alive'];
                    $role_name = $row['role_name'];
                    $role_faction = $row['role_faction'];
                    if($x % 4 == 1) {
                        echo "<tr>\n";
                    }
                    echo "<td ";
                    if($player_id == $user_id || $player_alive == 'N') {
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
                    echo "</td>";
                    if($x % 4 == 0) {
                        echo "</tr>\n";
                    }
                    $x++;
                }
                echo "</table>\n";
            }
        } else {
            //Game doesn't exist
            echo "<p class='error'>Sorry, that game doesn't exist.</p>\n";
        }
    }

    render_footer();

?>
