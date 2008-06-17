<?php

    include('./includes/functions.php');

    render_header("Thieves Tavern Games");

    if(!isset($_GET['game_id'])) {
        echo "<p><a href='./create_game.php'>Create a game</a>?</p>\n";
        echo "<div id='my_games'>\n";
        if(is_logged_in()) {
            $user_id = $_SESSION['user_id'];
            echo "<h3>Your Current Games</h3>\n";
            echo "<table align='center'>\n";
            echo "<tr class='header'>\n";
            echo "<td class='name'>Name</td>\n";
            echo "<td class='small'>Alive</td>\n";
            echo "<td class='small'>Dead</td>\n";
            echo "<td class='small'>Turn</td>\n";
            echo "<td class='small'>Phase</td>\n";
            echo "</tr>\n";
            $query = "SELECT game_players.game_id, games.game_phase, games.game_turn, games.game_name, ".
                     "(SELECT COUNT(*) FROM game_players WHERE game_players.game_id=games.game_id AND game_players.player_alive='Y') as alive, ".
                     "(SELECT COUNT(*) FROM game_players WHERE game_players.game_id=games.game_id AND game_players.player_alive='N') as dead ".
                     "FROM game_players, games ".
                     "WHERE game_players.user_id='$user_id' AND games.game_id=game_players.game_id ".
                     "AND games.game_phase != 3";
            $result = mysqli_query($dbh, $query);
            if($result && mysqli_num_rows($result) > 0) {
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
            }
            echo "</table>\n";
        }
        echo "</div>\n";
    }

    render_footer();

?>
